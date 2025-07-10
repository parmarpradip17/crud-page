<?php
require '00_db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $qualification = trim($_POST['qualification'] ?? '');

    if ($qualification === '') {
        echo json_encode(['status' => 'error', 'message' => 'Qualification is required.']);
        exit;
    }

    // Check if qualification exists
    $stmt = $conn->prepare("SELECT id FROM qualifications WHERE qualification_name = ?");
    $stmt->bind_param("s", $qualification);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        echo json_encode(['status' => 'success', 'id' => $row['id'], 'name' => $qualification]);
    } else {
        // Insert qualification
        $stmt = $conn->prepare("INSERT INTO qualifications (qualification_name) VALUES (?)");
        $stmt->bind_param("s", $qualification);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'id' => $stmt->insert_id, 'name' => $qualification]);
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