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
    });
});
