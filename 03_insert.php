<?php
require '00_db.php';
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs (basic validation for demonstration)
    $fname = trim($_POST['fname'] ?? '');
    $lname = trim($_POST['lname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    $gender = trim($_POST['gender'] ?? '');
    $address1 = trim($_POST['add1'] ?? '');
    $address2 = trim($_POST['add2'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $zip = trim($_POST['zip'] ?? '');

    // Qualification is now by ID
    $qualification_id = intval($_POST['qualification_id'] ?? 0);
    $percentage = floatval($_POST['percentage'] ?? 0);
    $passing_year = intval($_POST['passing_year'] ?? 0);
    $university = trim($_POST['university'] ?? '');

    // Hobbies will be an array of IDs
    $hobbies = $_POST['hobbies'] ?? []; // This will be an array of hobby IDs

    $uploadDir = 'uploads/';
    $photoPath = '';

    // Handle profile photo upload
    if (isset($_FILES['profile']) && $_FILES['profile']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['profile']['tmp_name'];
        $fileName = $_FILES['profile']['name'];
        $fileSize = $_FILES['profile']['size'];
        $fileType = $_FILES['profile']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
        $destPath = $uploadDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $destPath)) {
            $photoPath = $destPath;
        } else {
            $_SESSION['msg'] = "Error uploading file.";
            $_SESSION['msg_type'] = "danger";
            header("Location: 01_form.php");
            exit();
        }
    }

    // Start Transaction
    $conn->begin_transaction();

    try {
        // 1. Insert into stud_basic_info
        $stmt = $conn->prepare("INSERT INTO stud_basic_info (fname, lname, email, phone) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        }
        $stmt->bind_param("ssss", $fname, $lname, $email, $phone);
        $stmt->execute();
        $student_id = $conn->insert_id; // Get the ID of the newly inserted student
        $stmt->close();

        if (!$student_id) {
            throw new Exception("Failed to get new student ID.");
        }

        // 2. Insert into stud_gen_info
        $stmt = $conn->prepare("INSERT INTO stud_gen_info (student_id, gender, address1, address2, city, state, country, zip, photo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        }
        $stmt->bind_param("issssssss", $student_id, $gender, $address1, $address2, $city, $state, $country, $zip, $photoPath);
        $stmt->execute();
        $stmt->close();

        // 3. Insert into stud_academic_info
        // Make sure qualification_id is a valid ID (not 'OTHERS')
        if ($qualification_id === 0) { // If 'OTHERS' was selected and not saved via AJAX
            // You might want to handle this case: either throw an error, or try to get the ID
            // of a qualification that was just added via AJAX, or prevent form submission.
            // For now, assuming if 'OTHERS' was chosen, it's already handled by JS and saved.
            // If not, you'd need to fetch the ID based on the text entered by the user.
            $_SESSION['msg'] = "Invalid Qualification ID provided. Please ensure new qualifications are saved first.";
            $_SESSION['msg_type'] = "danger";
            $conn->rollback();
            header("Location: 01_form.php");
            exit();
        }

        $stmt = $conn->prepare("INSERT INTO stud_academic_info (student_id, qualification_id, percentage, passing_year, university) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        }
        $stmt->bind_param("iidss", $student_id, $qualification_id, $percentage, $passing_year, $university);
        $stmt->execute();
        $stmt->close();

        // 4. Insert into stud_hobbies (handle multiple selected hobbies)
        if (!empty($hobbies) && is_array($hobbies)) {
            $stmt_hobbies = $conn->prepare("INSERT INTO stud_hobbies (student_id, hobby_id) VALUES (?, ?)");
            if (!$stmt_hobbies) {
                throw new Exception("Prepare failed for hobbies: (" . $conn->errno . ") " . $conn->error);
            }
            foreach ($hobbies as $hobby_id) {
                // Ensure hobby_id is an integer (from hidden value or select option)
                $hobby_id_int = intval($hobby_id);
                if ($hobby_id_int > 0) { // Filter out 'OTHERS' value or invalid IDs
                    $stmt_hobbies->bind_param("ii", $student_id, $hobby_id_int);
                    $stmt_hobbies->execute();
                    if ($stmt_hobbies->error) {
                        // Log specific hobby insertion error but don't stop transaction
                        error_log("Error inserting hobby ID {$hobby_id_int} for student {$student_id}: " . $stmt_hobbies->error);
                    }
                }
            }
            $stmt_hobbies->close();
        }


        // If all successful, commit the transaction
        $conn->commit();
        $_SESSION['msg'] = "Student added successfully!";
        $_SESSION['msg_type'] = "success";
        header("Location: 05_crud.php"); // Redirect to the list page
        exit();
    } catch (Exception $e) {
        // Rollback on any error
        $conn->rollback();
        $_SESSION['msg'] = "Error adding student: " . $e->getMessage();
        $_SESSION['msg_type'] = "danger";
        header("Location: 01_form.php"); // Redirect back to the form
        exit();
    } finally {
        $conn->close();
    }
} else {
    $_SESSION['msg'] = "Invalid request method.";
    $_SESSION['msg_type'] = "danger";
    header("Location: 01_form.php");
    exit();
}
?>
<?php ?>