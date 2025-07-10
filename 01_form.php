<?php
require '00_db.php';
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define $isEdit before using it
$isEdit = isset($_GET['student_id']);
$studentData = [];
$studentHobbies = [];

// Fetch existing student data if editing
if ($isEdit) {
    $studentId = intval($_GET['student_id']);

    // Student details
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

    // Student hobbies
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

// Get qualifications
$qualifications = [];
$result = $conn->query("SELECT qualification_name FROM qualifications ORDER BY qualification_name ASC");
while ($row = $result->fetch_assoc()) {
    $qualifications[] = $row['qualification_name'];
}

// Get hobbies
$hobbiesList = [];
$result = $conn->query("SELECT hobby_name FROM hobbies ORDER BY hobby_name ASC");
while ($row = $result->fetch_assoc()) {
    $hobbiesList[] = $row['hobby_name'];
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?= $isEdit ? 'Edit' : 'Add' ?> Student Resume</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./css/style.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
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

        <form id="stud_form" class="needs-validation" enctype="multipart/form-data" method="POST"
            action="<?= $isEdit ? '04_update.php' : '03_insert.php' ?>"
            enctype="multipart/form-data" novalidate>
            <input type="hidden" id="hobbies_final" name="hobbies_final">
            <?php if ($isEdit): ?>
                <input type="hidden" name="student_id" value="<?= $studentId ?>">
            <?php endif; ?>

            <div class="row g-3">
                <!-- Personal Information -->
                <div class="col-md-6 mb-1">
                    <label for="fname" class="form-label">First Name</label>
                    <input type="text" name="fname" id="fname" class="form-control"
                        value="<?= htmlspecialchars($studentData['fname'] ?? '') ?>" required>
                    <div class="invalid-feedback">First Name is required.</div>
                </div>

                <div class="col-md-6 mb-1">
                    <label for="lname" class="form-label">Last Name</label>
                    <input type="text" name="lname" id="lname" class="form-control"
                        value="<?= htmlspecialchars($studentData['lname'] ?? '') ?>" required>
                    <div class="invalid-feedback">Last Name is required.</div>
                </div>

                <div class="col-md-6 mb-1">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email" class="form-control"
                        value="<?= htmlspecialchars($studentData['email'] ?? '') ?>" required>
                    <div class="invalid-feedback">Valid email is required.</div>
                </div>

                <div class="col-md-6 mb-1">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="tel" name="phone" id="phone" class="form-control" maxlength="10"
                        value="<?= htmlspecialchars($studentData['phone'] ?? '') ?>" required>
                    <div class="invalid-feedback">Phone is required.</div>
                </div>

                <div class="col-md-6 mb-1">
                    <label class="form-label d-block">Gender</label>
                    <div class="form-check form-check-inline">
                        <input type="radio" name="gender" id="male" value="Male" class="form-check-input"
                            <?= ($studentData['gender'] ?? '') === 'Male' ? 'checked' : '' ?> required>
                        <label class="form-check-label" for="male">Male</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input type="radio" name="gender" id="female" value="Female" class="form-check-input"
                            <?= ($studentData['gender'] ?? '') === 'Female' ? 'checked' : '' ?> required>
                        <label class="form-check-label" for="female">Female</label>
                    </div>
                    <div class="invalid-feedback">Gender selection is required.</div>
                </div>

                <div class="col-md-6 mb-1">
                    <label for="profile" class="form-label">Profile Photo</label>
                    <input type="file" name="profile" id="profile" class="form-control">
                    <?php if ($isEdit && !empty($studentData['photo'])): ?>
                        <small class="text-muted">Current: <?= basename($studentData['photo']) ?></small>
                        <input type="hidden" name="existing_photo" value="<?= $studentData['photo'] ?>">
                    <?php endif; ?>
                </div>

                <!-- Address Information -->
                <div class="col-md-6 mb-1">
                    <label for="add1" class="form-label">Address 1</label>
                    <input type="text" name="add1" id="add1" class="form-control"
                        value="<?= htmlspecialchars($studentData['address1'] ?? '') ?>" required>
                    <div class="invalid-feedback">Address is required.</div>
                </div>

                <div class="col-md-6 mb-1">
                    <label for="add2" class="form-label">Address 2</label>
                    <input type="text" name="add2" id="add2" class="form-control"
                        value="<?= htmlspecialchars($studentData['address2'] ?? '') ?>">
                </div>

                <div class="col-md-4 mb-1">
                    <label for="city" class="form-label">City</label>
                    <input type="text" name="city" id="city" class="form-control"
                        value="<?= htmlspecialchars($studentData['city'] ?? '') ?>" required>
                    <div class="invalid-feedback">City is required.</div>
                </div>

                <div class="col-md-4 mb-1">
                    <label for="state" class="form-label">State</label>
                    <input type="text" name="state" id="state" class="form-control"
                        value="<?= htmlspecialchars($studentData['state'] ?? '') ?>" required>
                    <div class="invalid-feedback">State is required.</div>
                </div>

                <div class="col-md-4 mb-1">
                    <label for="country" class="form-label">Country</label>
                    <select name="country" id="country" class="form-select" required>
                        <option value="" disabled <?= empty($studentData['country']) ? 'selected' : '' ?>>Select country</option>
                        <?php foreach (['USA', 'India', 'Canada', 'Australia'] as $c): ?>
                            <option value="<?= $c ?>" <?= ($studentData['country'] ?? '') === $c ? 'selected' : '' ?>><?= $c ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback">Country is required.</div>
                </div>

                <div class="col-md-4 mb-1">
                    <label for="zip" class="form-label">ZIP</label>
                    <input type="text" name="zip" id="zip" class="form-control" maxlength="6"
                        value="<?= htmlspecialchars($studentData['zip'] ?? '') ?>" required>
                    <div class="invalid-feedback">ZIP is required.</div>
                </div>

                <!-- Academic Information -->



                <!-- Qualification Dropdown -->
                <div class="col-md-6">
                    <label class="form-label">Qualification</label>
                    <select name="quali" id="quali_select" class="form-select" required>
                        <option value="">-- Select Qualification --</option>
                        <?php foreach ($qualifications as $q): ?>
                            <option value="<?= htmlspecialchars($q) ?>"
                                <?= (!empty($studentData) && $studentData['qualification_name'] === $q) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($q) ?>
                            </option>
                        <?php endforeach; ?>
                        <option value="OTHERS">Others</option>
                    </select>
                </div>

                <!-- Custom Qualification Input & Save Button -->
                <div id="other_qualification_container" class="col-md-12" style="display: none;">
                    <label class="form-label">Enter New Qualification</label>
                    <div class="input-group">
                        <input type="text" id="other_quali_input" class="form-control" placeholder="Type new qualification">
                        <button type="button" id="save_quali_btn" class="btn btn-success">Save Qualification</button>
                    </div>
                    <div id="save_quali_msg" class="form-text text-danger mt-1"></div>
                </div>




                <div class="col-md-4 mb-1">
                    <label for="percentage" class="form-label">Percentage</label>
                    <input type="number" name="percentage" id="percentage" class="form-control" min="0" max="100" step="0.01"
                        value="<?= htmlspecialchars($studentData['percentage'] ?? '') ?>" required>
                    <div class="invalid-feedback">Percentage is required (0-100).</div>
                </div>

                <div class="col-md-4 mb-1">
                    <label for="passing_year" class="form-label">Passing Year</label>
                    <input type="number" name="passing_year" id="passing_year" class="form-control" min="1900" max="<?= date('Y') + 5 ?>"
                        value="<?= htmlspecialchars($studentData['passing_year'] ?? '') ?>" required>
                    <div class="invalid-feedback">Passing year is required.</div>
                </div>

                <div class="col-md-4 mb-1">
                    <label for="university" class="form-label">University</label>
                    <input type="text" name="university" id="university" class="form-control"
                        value="<?= htmlspecialchars($studentData['university'] ?? '') ?>" required>
                    <div class="invalid-feedback">University is required.</div>
                </div>

                <!-- Hobbies -->
                <div class="col-md-6">
                    <label class="form-label">Hobbies</label>
                    <select name="hobby" id="hobby_select" class="form-select" required>
                        <option value="">-- Select Hobby --</option>
                        <?php foreach ($hobbiesList as $h): ?>
                            <option value="<?= htmlspecialchars($h) ?>"
                                <?= (!empty($studentData) && $studentData['hobby_name'] === $h) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($h) ?>
                            </option>
                        <?php endforeach; ?>
                        <option value="OTHERS">Others</option>
                    </select>
                </div>

                <div id="other_hobby_container" class="col-md-12" style="display: none;">
                    <label class="form-label">Enter New Hobby</label>
                    <div class="input-group">
                        <input type="text" id="other_hobby_input" class="form-control" placeholder="Type new hobby">
                        <button type="button" id="save_hobby_btn" class="btn btn-success">Save Hobby</button>
                    </div>
                    <div id="save_hobby_msg" class="form-text text-danger mt-1"></div>
                </div>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <a href="05_crud.php" class="btn btn-secondary">Back to List</a>
                <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Update' : 'Submit' ?></button>
            </div>
        </form>
    </div>

    <script src="./js/qualifications.js"></script>
    <script>
        const qualifications = <?= json_encode($qualifications) ?>;
        const hobbies = <?= json_encode($hobbiesList) ?>;
        const isEditMode = <?= $isEdit ? 'true' : 'false' ?>;
    </script>

    <script src="./js/hobbies.js"></script>
    <script src="./js/form.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>