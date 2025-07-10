<?php
require '00_db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hobby = trim($_POST['hobby'] ?? '');

    if ($hobby === '') {
        echo json_encode(['status' => 'error', 'message' => 'Hobby is required.']);
        exit;
    }

    // Check if hobby exists
    $stmt = $conn->prepare("SELECT id FROM hobbies WHERE hobby_name = ?");
    $stmt->bind_param("s", $hobby);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        echo json_encode(['status' => 'success', 'id' => $row['id'], 'name' => $hobby]);
    } else {
        // Insert hobby
        $stmt = $conn->prepare("INSERT INTO hobbies (hobby_name) VALUES (?)");
        $stmt->bind_param("s", $hobby);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'id' => $stmt->insert_id, 'name' => $hobby]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Insert failed: ' . $stmt->error]);
        }
    }

    $stmt->close();
    $conn->close();
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
?>
<?php ?>