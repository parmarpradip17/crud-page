$(document).ready(function () {
    // === Qualification Handling ===
    $('#quali_select').on('change', function () {
        if ($(this).val() === 'OTHERS') {
            $('#other_qualification_container').show();
        } else {
            $('#other_qualification_container').hide();
        }
    }).trigger('change');

    $('#save_quali_btn').on('click', function () {
        const qualification = $('#other_quali_input').val().trim();
        const $btn = $(this);
        const $msg = $('#save_quali_msg');

        if (qualification === '') {
            $msg.text('Please enter a qualification.');
            return;
        }

        $btn.prop('disabled', true).text('Saving...');
        $msg.text('');

        $.ajax({
            url: '08_ajax_save_qualification.php',
            method: 'POST',
            data: { qualification },
            dataType: 'json',
            success: function (res) {
                if (res.status === 'success') {
                    const $newOption = $('<option>', {
                        value: res.name,
                        text: res.name,
                        selected: true
                    });
                    $('#quali_select').append($newOption);
                    $('#other_quali_input').val('');
                    $('#other_qualification_container').hide();
                } else {
                    $msg.text(res.message || 'Failed to save qualification.');
                }
            },
            error: function () {
                $msg.text('AJAX error occurred.');
            },
            complete: function () {
                $btn.prop('disabled', false).text('Save Qualification');
            }
        });
    });

    // === Hobbies Handling (Same Logic as Qualification) ===
    $('#hobby_select').on('change', function () {
        if ($(this).val() === 'OTHERS') {
            $('#other_hobby_container').show();
        } else {
            $('#other_hobby_container').hide();
        }
    }).trigger('change');

    $('#save_hobby_btn').on('click', function () {
        const hobby = $('#other_hobby_input').val().trim();
        const $btn = $(this);
        const $msg = $('#save_hobby_msg');

        if (hobby === '') {
            $msg.text('Please enter a hobby.');
            return;
        }

        $btn.prop('disabled', true).text('Saving...');
        $msg.text('');

        $.ajax({
            url: '08_ajax_save_hobby.php', // Use your correct backend endpoint
            method: 'POST',
            data: { hobby },
            dataType: 'json',
            success: function (res) {
                if (res.status === 'success') {
                    const $newOption = $('<option>', {
                        value: res.name,
                        text: res.name,
                        selected: true
                    });
                    $('#hobby_select').append($newOption);
                    $('#other_hobby_input').val('');
                    $('#other_hobby_container').hide();
                } else {
                    $msg.text(res.message || 'Failed to save hobby.');
                }
            },
            error: function () {
                $msg.text('AJAX error occurred.');
            },
            complete: function () {
                $btn.prop('disabled', false).text('Save Hobby');
            }
        });
    });
});
