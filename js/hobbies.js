$(document).ready(function () {
    // Qualification logic
    $('#quali_select').on('change', function () {
        $('#other_qualification_container').toggle($(this).val() === 'OTHERS');
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

        $.post('08_ajax_save_qualification.php', { qualification }, function (res) {
            if (res.status === 'success') {
                $('#quali_select').append($('<option>', {
                    value: res.name,
                    text: res.name,
                    selected: true
                }));
                $('#other_quali_input').val('');
                $('#other_qualification_container').hide();
            } else {
                $msg.text(res.message || 'Failed to save qualification.');
            }
        }, 'json').always(() => {
            $btn.prop('disabled', false).text('Save Qualification');
        });
    });

    // Hobby logic
    $('#hobby_select').on('change', function () {
        const selected = $(this).val() || [];
        $('#other_hobby_container').toggle(selected.includes('OTHERS'));
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

        $.post('08_ajax_save_hobby.php', { hobby }, function (res) {
            if (res.status === 'success') {
                $('#hobby_select').append($('<option>', {
                    value: res.name,
                    text: res.name,
                    selected: true
                }));
                $('#other_hobby_input').val('');
                $('#other_hobby_container').hide();
                $('#hobby_select').trigger('change');
            } else {
                $msg.text(res.message || 'Failed to save hobby.');
            }
        }, 'json').always(() => {
            $btn.prop('disabled', false).text('Save Hobby');
        });
    });
});
