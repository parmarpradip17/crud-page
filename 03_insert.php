<?php
require '00_db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: 01_form.php");
    exit;
}

// Helper function to get or insert qualification/hobby
function getOrInsert($conn, $table, $column, $value)
{
    $stmt = $conn->prepare("SELECT id FROM $table WHERE $column = ?");
    $stmt->bind_param("s", $value);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        return $row['id'];
    }

    $stmt = $conn->prepare("INSERT INTO $table ($column) VALUES (?)");
    $stmt->bind_param("s", $value);
    $stmt->execute();
    return $stmt->insert_id;
}

try {
    $conn->begin_transaction();

    // Basic info
    $fname = trim($_POST['fname']);
    $lname = trim($_POST['lname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM stud_basic_info WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception("Email already exists!");
    }
    $stmt->close();

    // Insert basic info
    $stmt = $conn->prepare("INSERT INTO stud_basic_info (fname, lname, email, phone) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $fname, $lname, $email, $phone);
    $stmt->execute();
    $student_id = $stmt->insert_id;
    $stmt->close();

    // Handle file upload
    $filename = '';
    if (isset($_FILES['profile']) && $_FILES['profile']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $ext = strtolower(pathinfo($_FILES['profile']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            throw new Exception("Only JPG, PNG, and GIF files are allowed.");
        }

        $uploadDir = "uploads/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = $uploadDir . uniqid() . '.' . $ext;
        if (!move_uploaded_file($_FILES['profile']['tmp_name'], $filename)) {
            throw new Exception("Failed to upload file.");
        }
    }

    // General info
    $gender = $_POST['gender'];
    $add1 = trim($_POST['add1']);
    $add2 = trim($_POST['add2'] ?? '');
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $country = trim($_POST['country']);
    $zip = trim($_POST['zip']);

    $stmt = $conn->prepare("INSERT INTO stud_gen_info 
        (student_id, gender, address1, address2, city, state, country, zip, photo) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssssss", $student_id, $gender, $add1, $add2, $city, $state, $country, $zip, $filename);
    $stmt->execute();
    $stmt->close();

    // Academic info
    $quali = trim($_POST['quali']);
    $percentage = floatval($_POST['percentage']);
    $passing_year = intval($_POST['passing_year']);
    $university = trim($_POST['university']);

    $qualification_id = getOrInsert($conn, 'qualifications', 'qualification_name', $quali);

    $stmt = $conn->prepare("INSERT INTO stud_academic_info 
        (student_id, qualification_id, percentage, passing_year, university) 
        VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iidss", $student_id, $qualification_id, $percentage, $passing_year, $university);
    $stmt->execute();
    $stmt->close();

    // // Hobbies
    // $hobbies = isset($_POST['hobbies_final']) ? array_filter(array_map('trim', explode(',', $_POST['hobbies_final']))) : [];

    // foreach ($hobbies as $hobby) {
    //     $hobby_id = getOrInsert($conn, 'stud_hobbies', 'hobby_name', $hobby);

    //     $stmt = $conn->prepare("INSERT INTO stud_hobbies (student_id, hobby_id) VALUES (?, ?)");
    //     $stmt->bind_param("ii", $student_id, $hobby_id);
    //     $stmt->execute();
    //     $stmt->close();
    // }
    $hobbies = isset($_POST['hobbies_final']) ? array_filter(array_map('trim', explode(',', $_POST['hobbies_final']))) : [];

    foreach ($hobbies as $hobby) {
        // Query hobby_id from the hobbies table
        $stmt = $conn->prepare("SELECT id FROM hobbies WHERE hobby_name = ?");
        $stmt->bind_param("s", $hobby);
        $stmt->execute();
        $stmt->bind_result($hobby_id);

        if ($stmt->fetch()) {
            $stmt->close();

            // Insert into stud_hobbies
            $insert = $conn->prepare("INSERT INTO stud_hobbies (student_id, hobby_id) VALUES (?, ?)");
            $insert->bind_param("ii", $student_id, $hobby_id);
            $insert->execute();
            $insert->close();
        } else {
            // Hobby does not exist in `hobbies` table — skip
            $stmt->close();
        }
    }

    $conn->commit();
    $_SESSION['msg'] = "Student added successfully!";
    $_SESSION['msg_type'] = "success";
    header("Location: 05_crud.php");
    exit;
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['msg'] = "Error: " . $e->getMessage();
    $_SESSION['msg_type'] = "danger";
    header("Location: 01_form.php");
    exit;
}
?>
<?php ?>