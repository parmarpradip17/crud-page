<?php
// 05_crud.php (your provided file, unchanged)
require '00_db.php';
session_start();

$sql = "
SELECT
    sb.id AS student_id,
    sb.fname,
    sb.lname,
    sb.email,
    sb.phone,
    sg.gender,
    sg.city,
    sg.photo AS profile_photo,
    q.qualification_name,
    sa.percentage,
    sa.passing_year,
    sa.university,
    GROUP_CONCAT(h.hobby_name SEPARATOR ', ') AS hobbies
FROM stud_basic_info sb
LEFT JOIN stud_gen_info sg ON sb.id = sg.student_id
LEFT JOIN stud_academic_info sa ON sb.id = sa.student_id
LEFT JOIN qualifications q ON sa.qualification_id = q.id
LEFT JOIN stud_hobbies sh ON sb.id = sh.student_id
LEFT JOIN hobbies h ON sh.hobby_id = h.id
GROUP BY sb.id
ORDER BY sb.id ASC"; // Added ORDER BY for consistent display

$result = $conn->query($sql);

// Check for query errors
if (!$result) {
    die("SQL Error: " . $conn->error);
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Student List (CRUD)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./css/curd.css"> <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body class="bg-light">
    <div class="container py-5">
        <h2 class="text-center mb-4">Student List</h2>

        <?php if (isset($_SESSION['msg'])): ?>
            <div class="alert alert-<?= $_SESSION['msg_type'] ?> alert-dismissible fade show">
                <?= $_SESSION['msg'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['msg'], $_SESSION['msg_type']); ?>
        <?php endif; ?>

        <div class="d-flex justify-content-between mb-3">
            <div>
                <span class="badge bg-primary">Total Students: <?= $result->num_rows ?></span>
            </div>
            <a href="01_form.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add New Student
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-dark text-center">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Photo</th>
                        <th>Email </th>
                        <th>Phone</th>
                        <th>Gender</th>
                        <th>City</th>
                        <th>Qualification</th>
                        <th>Hobbies</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="text-center"><?= htmlspecialchars($row['student_id']) ?></td>
                                <td class="text-center">
                                    <?php if ($row['profile_photo'] && file_exists($row['profile_photo'])): ?>
                                        <img src="<?= htmlspecialchars($row['profile_photo']) ?>"
                                            class="rounded-circle" width="50" height="50"
                                            alt="<?= htmlspecialchars($row['fname']) ?>">
                                    <?php else: ?>
                                        <span class="text-muted">No photo</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($row['fname']) ?> <?= htmlspecialchars($row['lname']) ?>
                                    <div class="text-muted small">Student #<?= htmlspecialchars($row['student_id']) ?></div>
                                </td>
                                <td class="text-center"><?= htmlspecialchars($row['email']) ?></td>
                                <td class="text-center"><?= htmlspecialchars($row['phone']) ?></td>
                                <td class="text-center"><?= htmlspecialchars($row['gender']) ?></td>
                                <td class="text-center"><?= htmlspecialchars($row['city']) ?></td>
                                <td><?= htmlspecialchars($row['qualification_name'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($row['hobbies'] ?? 'N/A') ?></td>
                                <td class="text-center">
                                    <a href="01_form.php?student_id=<?= htmlspecialchars($row['student_id']) ?>"
                                        class="btn btn-sm btn-success " target='_blank'>Update</a>
                                    <a href="07_delete.php?id=<?= htmlspecialchars($row['student_id']) ?>"
                                        class="btn btn-sm btn-danger "
                                        onclick="return confirm('Are you sure you want to delete this student?')">
                                        Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="text-center py-4">
                                <div class="text-muted">No students found. <a href="01_form.php">Add a new student</a>.</div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>