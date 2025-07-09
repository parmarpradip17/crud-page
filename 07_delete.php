<?php
require '00_db.php';
session_start();

if (!isset($_GET['id'])) {
    $_SESSION['msg'] = "No student ID provided for deletion.";
    $_SESSION['msg_type'] = "danger";
    header("Location: 05_crud.php");
    exit;
}

$student_id = intval($_GET['id']);

if ($student_id <= 0) {
    $_SESSION['msg'] = "Invalid student ID.";
    $_SESSION['msg_type'] = "danger";
    header("Location: 05_crud.php");
    exit;
}

try {
    $conn->begin_transaction();

    // First get the photo path to delete the file later
    $stmt = $conn->prepare("SELECT photo FROM stud_gen_info WHERE student_id = ?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $photo = $result->fetch_assoc()['photo'] ?? '';
    $stmt->close();

    // Delete from all related tables
    $tables = ['stud_hobbies', 'stud_academic_info', 'stud_gen_info', 'stud_basic_info'];
    foreach ($tables as $table) {
        $column = ($table === 'stud_basic_info') ? 'id' : 'student_id';
        $stmt = $conn->prepare("DELETE FROM $table WHERE $column = ?");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $stmt->close();
    }

    // Delete the photo file if it exists
    if ($photo && file_exists($photo)) {
        unlink($photo);
    }

    $conn->commit();
    $_SESSION['msg'] = "Student deleted successfully.";
    $_SESSION['msg_type'] = "success";
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['msg'] = "Error deleting student: " . $e->getMessage();
    $_SESSION['msg_type'] = "danger";
}

header("Location: 05_crud.php");
exit;
?>
<?php ?>