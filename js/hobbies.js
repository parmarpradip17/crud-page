$(document).ready(function () {
    $('#hobby_select').on('change', function () {
        if ($(this).val().includes('OTHERS')) {
            $('#other_hobby_container').show();
        } else {
            $('#other_hobby_container').hide();
        }
    });

    $('#save_hobby_btn').on('click', function () {
        const newHobby = $('#other_hobby_input').val().trim();

        if (!newHobby) {
            $('#save_hobby_msg').text('Hobby name cannot be empty.');
            return;
        }

        $.ajax({
            url: 'save_hobby.php',
            method: 'POST',
            data: { hobby_name: newHobby },
            success: function (response) {
                try {
                    const res = JSON.parse(response);
                    if (res.status === 'success') {
                        // Add to select
                        $('#hobby_select').append(`<option value="${newHobby}" selected>${newHobby}</option>`);
                        $('#hobby_select').val([...$('#hobby_select').val().filter(v => v !== 'OTHERS'), newHobby]);
                        $('#other_hobby_container').hide();
                        $('#other_hobby_input').val('');
                        $('#save_hobby_msg').text('');
                    } else {
                        $('#save_hobby_msg').text(res.message || 'Error saving hobby.');
                    }
                } catch (e) {
                    $('#save_hobby_msg').text('Unexpected error occurred.');
                }
            },
            error: function () {
                $('#save_hobby_msg').text('Server error while saving hobby.');
            }
        });
    });
});
