<?php
require '00_db.php';
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_id'])) {
    $student_id = intval($_POST['student_id']);

    // Sanitize and validate inputs
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
    $existing_photo = trim($_POST['existing_photo'] ?? ''); // Path to the existing photo

    // Qualification is now by ID
    $qualification_id = intval($_POST['qualification_id'] ?? 0);
    $percentage = floatval($_POST['percentage'] ?? 0);
    $passing_year = intval($_POST['passing_year'] ?? 0);
    $university = trim($_POST['university'] ?? '');

    // Hobbies will be an array of IDs
    $hobbies = $_POST['hobbies'] ?? []; // This will be an array of hobby IDs

    $uploadDir = 'uploads/';
    $photoPath = $existing_photo; // Default to existing photo

    // Handle new profile photo upload
    if (isset($_FILES['profile']) && $_FILES['profile']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['profile']['tmp_name'];
        $fileName = $_FILES['profile']['name'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
        $destPath = $uploadDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $destPath)) {
            // Delete old photo if it exists and is different from the new one
            if (!empty($existing_photo) && file_exists($existing_photo) && $existing_photo !== $destPath) {
                unlink($existing_photo);
            }
            $photoPath = $destPath;
        } else {
            $_SESSION['msg'] = "Error uploading new file.";
            $_SESSION['msg_type'] = "warning"; // Use warning if other updates proceed
            // Continue with existing photo path if upload fails
        }
    }

    // Start Transaction
    $conn->begin_transaction();

    try {
        // 1. Update stud_basic_info
        $stmt = $conn->prepare("UPDATE stud_basic_info SET fname = ?, lname = ?, email = ?, phone = ? WHERE id = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed for basic info: (" . $conn->errno . ") " . $conn->error);
        }
        $stmt->bind_param("ssssi", $fname, $lname, $email, $phone, $student_id);
        $stmt->execute();
        $stmt->close();

        // 2. Update stud_gen_info
        $stmt = $conn->prepare("UPDATE stud_gen_info SET gender = ?, address1 = ?, address2 = ?, city = ?, state = ?, country = ?, zip = ?, photo = ? WHERE student_id = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed for general info: (" . $conn->errno . ") " . $conn->error);
        }
        $stmt->bind_param("ssssssssi", $gender, $address1, $address2, $city, $state, $country, $zip, $photoPath, $student_id);
        $stmt->execute();
        $stmt->close();

        // 3. Update stud_academic_info
        // Make sure qualification_id is a valid ID (not 'OTHERS')
        if ($qualification_id === 0) { // If 'OTHERS' was selected and not saved via AJAX
            $_SESSION['msg'] = "Invalid Qualification ID provided during update. Please ensure new qualifications are saved first.";
            $_SESSION['msg_type'] = "danger";
            $conn->rollback();
            header("Location: 01_form.php?student_id=" . $student_id);
            exit();
        }

        $stmt = $conn->prepare("UPDATE stud_academic_info SET qualification_id = ?, percentage = ?, passing_year = ?, university = ? WHERE student_id = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed for academic info: (" . $conn->errno . ") " . $conn->error);
        }
        $stmt->bind_param("iidss", $qualification_id, $percentage, $passing_year, $university, $student_id);
        $stmt->execute();
        $stmt->close();

        // 4. Update stud_hobbies (Delete all existing, then re-insert new ones)
        // Delete all current hobbies for this student
        $stmt_delete_hobbies = $conn->prepare("DELETE FROM stud_hobbies WHERE student_id = ?");
        if (!$stmt_delete_hobbies) {
            throw new Exception("Prepare failed for deleting hobbies: (" . $conn->errno . ") " . $conn->error);
        }
        $stmt_delete_hobbies->bind_param("i", $student_id);
        $stmt_delete_hobbies->execute();
        $stmt_delete_hobbies->close();

        // Insert the newly selected hobbies
        if (!empty($hobbies) && is_array($hobbies)) {
            $stmt_insert_hobbies = $conn->prepare("INSERT INTO stud_hobbies (student_id, hobby_id) VALUES (?, ?)");
            if (!$stmt_insert_hobbies) {
                throw new Exception("Prepare failed for inserting hobbies: (" . $conn->errno . ") " . $conn->error);
            }
            foreach ($hobbies as $hobby_id) {
                $hobby_id_int = intval($hobby_id);
                if ($hobby_id_int > 0) { // Filter out 'OTHERS' value or invalid IDs
                    $stmt_insert_hobbies->bind_param("ii", $student_id, $hobby_id_int);
                    $stmt_insert_hobbies->execute();
                    if ($stmt_insert_hobbies->error) {
                        error_log("Error inserting hobby ID {$hobby_id_int} for student {$student_id} on update: " . $stmt_insert_hobbies->error);
                    }
                }
            }
            $stmt_insert_hobbies->close();
        }

        // If all successful, commit the transaction
        $conn->commit();
        $_SESSION['msg'] = "Student updated successfully!";
        $_SESSION['msg_type'] = "success";
        header("Location: 05_crud.php"); // Redirect to the list page
        exit();
    } catch (Exception $e) {
        // Rollback on any error
        $conn->rollback();
        $_SESSION['msg'] = "Error updating student: " . $e->getMessage();
        $_SESSION['msg_type'] = "danger";
        header("Location: 01_form.php?student_id=" . $student_id); // Redirect back to the form
        exit();
    } finally {
        $conn->close();
    }
} else {
    $_SESSION['msg'] = "Invalid request or missing student ID for update.";
    $_SESSION['msg_type'] = "danger";
    header("Location: 05_crud.php");
    exit();
}
?>
<?php ?>