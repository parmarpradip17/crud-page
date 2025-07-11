<?php
require '00_db.php';
session_start();

$id = isset($_POST['student_id']) ? intval($_POST['student_id']) : (isset($_POST['id']) ? intval($_POST['id']) : 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id > 0) {
    try {
        $conn->begin_transaction();

        // Optional debug
        // echo "Deleting student ID: $id";

        // 1. Delete hobbies
        $stmt = $conn->prepare("DELETE FROM stud_hobbies WHERE student_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        // 2. Delete academic info
        $stmt = $conn->prepare("DELETE FROM stud_academic_info WHERE student_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        // 3. Delete general info
        $stmt = $conn->prepare("DELETE FROM stud_gen_info WHERE student_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        // 4. Delete main record
        $stmt = $conn->prepare("DELETE FROM stud_basic_info WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        $conn->commit();
        echo "Deleted successfully.";
    } catch (Exception $e) {
        $conn->rollback();
        echo "SQL Error: " . $conn->error;
    }
} else {
    echo "Invalid or missing student ID.";
}
