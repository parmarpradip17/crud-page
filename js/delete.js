$(document).ready(function () {
    $(document).on('click', '.delete-btn', function (e) {
        e.preventDefault();
        const $button = $(this);
        const id = $button.data('id') || $button.closest('a').attr('href').split('=')[1];
        const $row = $button.closest('tr');

        // Show confirmation with countdown
        const originalContent = $row.html();
        $row.html(`
            <td colspan="12" class="text-center text-danger">
                Deleting in <span class="countdown">3</span> seconds...
                <button class="btn btn-sm btn-warning ms-2 undo-delete">Undo</button>
            </td>
        `);

        let countdown = 3;
        const countdownInterval = setInterval(() => {
            countdown--;
            $row.find('.countdown').text(countdown);
            if (countdown <= 0) clearInterval(countdownInterval);
        }, 1000);

        const deleteTimeout = setTimeout(() => {
            clearInterval(countdownInterval);

            $.ajax({
                url: '06_delete.php',
                type: 'POST',
                data: { id: id },
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        $row.fadeOut(300, function () {
                            $(this).remove();
                            // Show success message
                            $('<div class="alert alert-success">Student deleted successfully.</div>')
                                .insertBefore('.table-responsive')
                                .delay(3000).fadeOut();
                        });
                    } else {
                        showError(response.message || 'Failed to delete student');
                        $row.html(originalContent);
                    }
                },
                error: function (xhr) {
                    showError(xhr.responseJSON?.message || 'Server error occurred');
                    $row.html(originalContent);
                }
            });
        }, 3000);

        // Undo functionality
        $row.on('click', '.undo-delete', function () {
            clearTimeout(deleteTimeout);
            clearInterval(countdownInterval);
            $row.html(originalContent);
        });
    });

    function showError(message) {
        $('<div class="alert alert-danger">' + message + '</div>')
            .insertBefore('.table-responsive')
            .delay(3000).fadeOut();
    }
});