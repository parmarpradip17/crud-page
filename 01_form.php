<?php
require '00_db.php';
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$isEdit = isset($_GET['student_id']);
$studentData = [];
$studentHobbies = [];

if ($isEdit) {
    $studentId = intval($_GET['student_id']);

    // Fetch student main data
    $stmt = $conn->prepare("
        SELECT sb.*, sg.*, sa.percentage, sa.passing_year, sa.university, q.qualification_name
        FROM stud_basic_info sb
        JOIN stud_gen_info sg ON sb.id = sg.student_id
        JOIN stud_academic_info sa ON sb.id = sa.student_id
        JOIN qualifications q ON sa.qualification_id = q.id
        WHERE sb.id = ?
    ");
    $stmt->bind_param("i", $studentId);
    $stmt->execute();
    $result = $stmt->get_result();
    $studentData = $result->fetch_assoc();
    $stmt->close();

    // Fetch student hobbies
    $stmt = $conn->prepare("
        SELECT h.hobby_name 
        FROM stud_hobbies sh
        JOIN hobbies h ON sh.hobby_id = h.id
        WHERE sh.student_id = ?
    ");
    $stmt->bind_param("i", $studentId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $studentHobbies[] = $row['hobby_name'];
    }
    $stmt->close();
}

// Fetch qualifications
$qualifications = [];
$result = $conn->query("SELECT qualification_name FROM qualifications");
while ($row = $result->fetch_assoc()) {
    $qualifications[] = $row['qualification_name'];
}

// Fetch all hobbies
$allHobbies = [];
$result = $conn->query("SELECT hobby_name FROM hobbies");
while ($row = $result->fetch_assoc()) {
    $allHobbies[] = $row['hobby_name'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?= $isEdit ? 'Edit' : 'Add' ?> Student Resume</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container py-5">
        <h2 class="mb-4"><?= $isEdit ? 'Edit' : 'Add' ?> Student Resume</h2>

        <?php if (isset($_SESSION['msg'])): ?>
            <div class="alert alert-<?= $_SESSION['msg_type'] ?? 'info' ?>">
                <?= $_SESSION['msg'] ?>
                <?php unset($_SESSION['msg'], $_SESSION['msg_type']); ?>
            </div>
        <?php endif; ?>

        <form id="stud_form" method="POST" action="<?= $isEdit ? '04_update.php' : '03_insert.php' ?>"
            enctype="multipart/form-data" class="needs-validation" novalidate>
            <input type="hidden" id="hobbies_final" name="hobbies_final">
            <?php if ($isEdit): ?>
                <input type="hidden" name="student_id" value="<?= $studentId ?>">
            <?php endif; ?>

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="fname" class="form-label">First Name</label>
                    <input type="text" name="fname" id="fname" class="form-control"
                        value="<?= htmlspecialchars($studentData['fname'] ?? '') ?>" required>
                </div>

                <div class="col-md-6">
                    <label for="lname" class="form-label">Last Name</label>
                    <input type="text" name="lname" id="lname" class="form-control"
                        value="<?= htmlspecialchars($studentData['lname'] ?? '') ?>" required>
                </div>

                <div class="col-md-6">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email" class="form-control"
                        value="<?= htmlspecialchars($studentData['email'] ?? '') ?>" required>
                </div>

                <div class="col-md-6">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="tel" name="phone" id="phone" class="form-control" maxlength="10" pattern="\d{10}"
                        value="<?= htmlspecialchars($studentData['phone'] ?? '') ?>" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label d-block">Gender</label>
                    <div class="form-check form-check-inline">
                        <input type="radio" name="gender" id="male" value="Male" class="form-check-input"
                            <?= ($studentData['gender'] ?? '') === 'Male' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="male">Male</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input type="radio" name="gender" id="female" value="Female" class="form-check-input"
                            <?= ($studentData['gender'] ?? '') === 'Female' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="female">Female</label>
                    </div>
                </div>

                <div class="col-md-6">
                    <label for="profile" class="form-label">Profile Photo</label>
                    <input type="file" name="profile" id="profile" class="form-control">
                    <?php if ($isEdit && !empty($studentData['photo'])): ?>
                        <small class="text-muted d-block">Current: <?= basename($studentData['photo']) ?></small>
                        <img src="<?= htmlspecialchars($studentData['photo']) ?>" class="img-thumbnail mt-2" style="max-height:100px;">
                        <input type="hidden" name="existing_photo" value="<?= htmlspecialchars($studentData['photo']) ?>">
                    <?php endif; ?>
                </div>

                <!-- Address -->
                <div class="col-md-6">
                    <label for="add1" class="form-label">Address 1</label>
                    <input type="text" name="add1" id="add1" class="form-control"
                        value="<?= htmlspecialchars($studentData['address1'] ?? '') ?>" required>
                </div>

                <div class="col-md-6">
                    <label for="add2" class="form-label">Address 2</label>
                    <input type="text" name="add2" id="add2" class="form-control"
                        value="<?= htmlspecialchars($studentData['address2'] ?? '') ?>">
                </div>

                <div class="col-md-4">
                    <label for="city" class="form-label">City</label>
                    <input type="text" name="city" id="city" class="form-control"
                        value="<?= htmlspecialchars($studentData['city'] ?? '') ?>" required>
                </div>

                <div class="col-md-4">
                    <label for="state" class="form-label">State</label>
                    <input type="text" name="state" id="state" class="form-control"
                        value="<?= htmlspecialchars($studentData['state'] ?? '') ?>" required>
                </div>

                <div class="col-md-4">
                    <label for="country" class="form-label">Country</label>
                    <select name="country" id="country" class="form-select" required>
                        <option value="">Select Country</option>
                        <?php foreach (['USA', 'India', 'Canada', 'Australia'] as $c): ?>
                            <option value="<?= $c ?>" <?= ($studentData['country'] ?? '') === $c ? 'selected' : '' ?>>
                                <?= $c ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="zip" class="form-label">ZIP</label>
                    <input type="text" name="zip" id="zip" class="form-control" maxlength="6"
                        value="<?= htmlspecialchars($studentData['zip'] ?? '') ?>" required>
                </div>

                <!-- Qualification -->
                <div class="col-md-6">
                    <label class="form-label">Qualification</label>
                    <select name="quali" id="quali_select" class="form-select" required>
                        <option value="">-- Select Qualification --</option>
                        <?php foreach ($qualifications as $q): ?>
                            <option value="<?= htmlspecialchars($q) ?>" <?= ($studentData['qualification_name'] ?? '') === $q ? 'selected' : '' ?>>
                                <?= htmlspecialchars($q) ?>
                            </option>
                        <?php endforeach; ?>
                        <option value="OTHERS">Others</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="percentage" class="form-label">Percentage</label>
                    <input type="number" name="percentage" id="percentage" class="form-control" min="0" max="100"
                        value="<?= htmlspecialchars($studentData['percentage'] ?? '') ?>" required>
                </div>

                <div class="col-md-4">
                    <label for="passing_year" class="form-label">Passing Year</label>
                    <input type="number" name="passing_year" id="passing_year" class="form-control" min="1900" max="<?= date('Y') + 5 ?>"
                        value="<?= htmlspecialchars($studentData['passing_year'] ?? '') ?>" required>
                </div>

                <div class="col-md-4">
                    <label for="university" class="form-label">University</label>
                    <input type="text" name="university" id="university" class="form-control"
                        value="<?= htmlspecialchars($studentData['university'] ?? '') ?>" required>
                </div>

                <!-- Hobbies -->
                <div class="col-md-6">
                    <label class="form-label">Hobbies</label>
                    <select name="hobby_select[]" id="hobby_select" class="form-control" multiple>
                        <?php foreach ($allHobbies as $hobby): ?>
                            <option value="<?= htmlspecialchars($hobby) ?>" <?= in_array($hobby, $studentHobbies) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($hobby) ?>
                            </option>
                        <?php endforeach; ?>
                        <option value="OTHERS">OTHERS</option>
                    </select>
                    <small class="form-text text-muted">Hold Ctrl/Cmd to select multiple.</small>
                </div>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <a href="05_crud.php" class="btn btn-secondary">Back to List</a>
                <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Update' : 'Submit' ?></button>
            </div>
        </form>
    </div>

    <!-- Scripts -->
    <script>
        document.getElementById('stud_form').addEventListener('submit', function(e) {
            const select = document.getElementById('hobby_select');
            const hiddenInput = document.getElementById('hobbies_final');
            let selected = Array.from(select.selectedOptions).map(opt => opt.value);

            if (selected.includes('OTHERS')) {
                const other = prompt('Enter your hobby:');
                if (other && other.trim() !== '') {
                    selected[selected.indexOf('OTHERS')] = other.trim();
                } else {
                    selected = selected.filter(v => v !== 'OTHERS');
                }
            }

            hiddenInput.value = selected.join(',');
        });
    </script>
    <script src="./js/form.js"></script>
    <script src="./js/qualifications.js"></script>
    <script src="./js/hobbies.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>