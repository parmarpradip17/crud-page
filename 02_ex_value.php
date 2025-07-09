<?php
header('Content-Type: application/json');
$conn = new mysqli("localhost", "root", "", "stud_resume");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database error.']);
    exit;
}

$type = $_POST['type'];
$value = trim($_POST['value']);

if ($type === 'qualifications') {
    $stmt = $conn->prepare("INSERT IGNORE INTO qualifications (qualification_name) VALUES (?)");
} elseif ($type === 'hobbies') {
    $stmt = $conn->prepare("INSERT IGNORE INTO hobbies (hobby_name) VALUES (?)");
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid type.']);
    exit;
}

$stmt->bind_param("s", $value);
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => ucfirst($type) . ' added successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error adding to database.']);
}
?>
<?php ?>