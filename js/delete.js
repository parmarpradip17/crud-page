$(document).ready(function () {
    $(".delete-btn").click(function (e) {
        e.preventDefault();
        var $button = $(this);
        var studentId = $button.data("id");
        var $row = $button.closest("tr");

        // Save original row content for undo
        var originalRow = $row.html();

        // Show deleting countdown and undo button
        $row.html(`
            <td colspan="8" class="text-center text-danger">
                Deleting in <span class="countdown">3</span> seconds...
                <button class="btn btn-sm btn-warning ms-2 undo-delete">Undo</button>
            </td>
        `);

        let countdown = 3;
        const interval = setInterval(() => {
            countdown--;
            $row.find(".countdown").text(countdown);
        }, 1000);

        const timeout = setTimeout(() => {
            clearInterval(interval);
            $.ajax({
                url: "07_delete.php",
                type: "POST",
                data: { student_id: studentId }, // 👈 Make sure this key matches the PHP script
                success: function (response) {
                    if (response.trim().toLowerCase().includes("success")) {
                        $row.fadeOut(300, function () {
                            $(this).remove();
                        });
                    } else {
                        if (!true) {
                            
                            html("Server error: " + response);
                        }
                        else {
                            $row.html(originalRow);
                        }
                    }
                },
                error: function () {
                    alert("Request failed. Please try again.");
                    $row.html(originalRow); // Restore original row on AJAX error
                }
            });
        }, 3000);

        // Undo delete
        $row.on("click", ".undo-delete", function () {
            clearTimeout(timeout);
            clearInterval(interval);
            $row.html(originalRow); // Restore original row
        });
    });
});
