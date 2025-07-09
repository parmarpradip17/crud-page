<?php
$conn = new mysqli("localhost", "root", "", "stud_resume");
if ($conn->connect_error) {
    echo "<div class='alert alert-danger'>Database connection failed: " . $conn->connect_error . "</div>" ;
}



// // Clear multi_query results
// while ($conn->next_result()) {;
// }
?>
<?php ?>