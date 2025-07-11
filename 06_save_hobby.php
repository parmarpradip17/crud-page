<?php
require '00_db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hobby_name'])) {
    $hobby = trim($_POST['hobby_name']);
    if ($hobby === '') {
        echo json_encode(['status' => 'error', 'message' => 'Hobby name is required.']);
        exit;
    }

    $stmt = $conn->prepare("INSERT IGNORE INTO hobbies (hobby_name) VALUES (?)");
    $stmt->bind_param("s", $hobby);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database insert failed.']);
    }
    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
}
?>
<?php ?>