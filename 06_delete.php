<?php
require '00_db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['student_id'])) {
        $id = intval($_POST['student_id']);
    } elseif (!empty($_POST['id'])) {
        $id = intval($_POST['id']);
    } else {
        echo "Missing student ID.";
        exit;
    }

    if ($id <= 0) {
        echo "Invalid student ID.";
        exit;
    }

    try {
        $conn->begin_transaction();

        $stmt = $conn->prepare("DELETE FROM stud_basic_info WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        $conn->commit();
        echo "Deleted successfully.";
    } catch (Exception $e) {
        $conn->rollback();
        echo "SQL Error: " . $e->getMessage(); // Show actual exception message
    }
} else {
    echo "Invalid request method.";
}
