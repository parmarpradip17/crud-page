<?php
require '00_db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: 05_crud.php");
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

    $student_id = intval($_POST['student_id']);
    if ($student_id <= 0) {
        throw new Exception("Invalid student ID");
    }

    // Basic info
    $fname = trim($_POST['fname']);
    $lname = trim($_POST['lname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);

    // Check if email already exists for another student
    $stmt = $conn->prepare("SELECT id FROM stud_basic_info WHERE email = ? AND id != ?");
    $stmt->bind_param("si", $email, $student_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception("Email already exists for another student!");
    }
    $stmt->close();

    // Update basic info
    $stmt = $conn->prepare("UPDATE stud_basic_info SET fname=?, lname=?, email=?, phone=? WHERE id=?");
    $stmt->bind_param("ssssi", $fname, $lname, $email, $phone, $student_id);
    $stmt->execute();
    $stmt->close();

    // Handle file upload
    $filename = $_POST['existing_photo'] ?? '';
    if (isset($_FILES['profile'])) {
        // Delete old file if exists
        if ($filename && file_exists($filename)) {
            unlink($filename);
        }

        if ($_FILES['profile']['error'] === UPLOAD_ERR_OK) {
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
    }

    // General info
    $gender = $_POST['gender'];
    $add1 = trim($_POST['add1']);
    $add2 = trim($_POST['add2'] ?? '');
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $country = trim($_POST['country']);
    $zip = trim($_POST['zip']);

    if ($filename) {
        $stmt = $conn->prepare("UPDATE stud_gen_info SET 
            gender=?, address1=?, address2=?, city=?, state=?, country=?, zip=?, photo=? 
            WHERE student_id=?");
        $stmt->bind_param("ssssssssi", $gender, $add1, $add2, $city, $state, $country, $zip, $filename, $student_id);
    } else {
        $stmt = $conn->prepare("UPDATE stud_gen_info SET 
            gender=?, address1=?, address2=?, city=?, state=?, country=?, zip=? 
            WHERE student_id=?");
        $stmt->bind_param("sssssssi", $gender, $add1, $add2, $city, $state, $country, $zip, $student_id);
    }
    $stmt->execute();
    $stmt->close();

    // Academic info
    $quali = trim($_POST['quali']);
    $percentage = floatval($_POST['percentage']);
    $passing_year = intval($_POST['passing_year']);
    $university = trim($_POST['university']);

    $qualification_id = getOrInsert($conn, 'qualifications', 'qualification_name', $quali);

    $stmt = $conn->prepare("UPDATE stud_academic_info SET 
        qualification_id=?, percentage=?, passing_year=?, university=? 
        WHERE student_id=?");
    $stmt->bind_param("idssi", $qualification_id, $percentage, $passing_year, $university, $student_id);
    $stmt->execute();
    $stmt->close();

    // Hobbies - first delete existing ones
    $stmt = $conn->prepare("DELETE FROM stud_hobbies WHERE student_id = ?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $stmt->close();

    // Then add new ones
    $hobbies = isset($_POST['hobbies_final']) ? array_filter(array_map('trim', explode(',', $_POST['hobbies_final']))) : [];

    foreach ($hobbies as $hobby) {
        $hobby_id = getOrInsert($conn, 'stud_hobbies', 'hobby_id', $hobby);

        $stmt = $conn->prepare("INSERT INTO stud_hobbies (student_id, hobby_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $student_id, $hobby_id);
        $stmt->execute();
        $stmt->close();
    }

    $conn->commit();
    $_SESSION['msg'] = "Student updated successfully!";
    $_SESSION['msg_type'] = "success";
    header("Location: 05_crud.php");
    exit;
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['msg'] = "Error: " . $e->getMessage();
    $_SESSION['msg_type'] = "danger";
    header("Location: 01_form.php?student_id=" . ($student_id ?? ''));
    exit;
}
?>
<?php ?>