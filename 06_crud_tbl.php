<!DOCTYPE html>
<html>

<head>
    <title>Student List (CRUD)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./css/curd.css">
    
</head>

<body class="bg-light">
    <div class="container py-5">
        <h2 class="text-center mb-4">Student List</h2>

        <?php if (isset($_GET['updated'])): ?>
            <div class="alert alert-success text-center">Student updated successfully!</div>
        <?php endif; ?>

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
            <div class="alert alert-success text-center">Student deleted successfully.</div>
        <?php endif; ?>

        <div class="text-end mb-3">
            <a href="01_form.php" class="btn btn-primary">Add New Student</a>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-dark text-center">
                    <tr>
                        <th>ID</th>
                        <th>Photo</th>
                        <th>Name</th>
                        <th>Email / Phone</th>
                        <th>Gender</th>
                        <th>City</th>
                        <th>Qualification</th>
                        <th>University</th>
                        <th>Percentage</th>
                        <th>Passing Year</th>
                        <th>Hobbies</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="text-center"><?= $row['student_id'] ?></td>
                                <td class="text-center">
                                    <?php if ($row['profile_photo']): ?>
                                        <img src="<?= htmlspecialchars($row['profile_photo']) ?>" class="rounded-circle" width="50" height="50">
                                    <?php else: ?>
                                        <span class="text-muted">No image</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($row['fname']) ?> <?= htmlspecialchars($row['lname']) ?></td>
                                <td>
                                    <?= htmlspecialchars($row['email']) ?><br>
                                    <?= htmlspecialchars($row['phone']) ?>
                                </td>
                                <td class="text-center"><?= htmlspecialchars($row['gender']) ?></td>
                                <td class="text-center"><?= htmlspecialchars($row['city']) ?></td>
                                <td><?= htmlspecialchars($row['qualification_name']) ?></td>
                                <td><?= htmlspecialchars($row['university']) ?></td>
                                <td class="text-center"><?= htmlspecialchars($row['percentage']) ?>%</td>
                                <td class="text-center"><?= htmlspecialchars($row['passing_year']) ?></td>
                                <td><?= htmlspecialchars($row['hobbies']) ?></td>
                                <td class="text-center">
                                    <a href="student_form.php?student_id=<?= $row['student_id'] ?>" class="btn btn-sm btn-success">Edit</a>
                                    <a href="07_delete.php?id=<?= $row['student_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="12" class="text-center">No records found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="./js/delete.js"></script>
</body>

</html>