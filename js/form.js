$(document).ready(function () {
    const $form = $('#stud_form');
    const qualifications = window.qualifications || [];
    const hobbies = window.hobbies || [];
    const isEditMode = window.isEditMode || false;

    // === Qualification Autocomplete ===
    const $qualiInput = $('#quali_input');
    const $qualiDropdown = $('#quali_dropdown');
    const $qualiAddContainer = $('#quali_add_container');
    const $qualiAddInput = $('#quali_add_input');

    $qualiInput.on('input', function () {
        const term = $(this).val().toLowerCase().trim();
        $qualiDropdown.empty().toggle(!!term);

        if (!term) return;

        const filtered = qualifications.filter(q => q.toLowerCase().includes(term));
        if (filtered.length) {
            filtered.forEach(q => {
                $('<div/>', {
                    class: 'dropdown-item',
                    text: q
                }).appendTo($qualiDropdown);
            });
        } else {
            $('<div/>', {
                class: 'dropdown-item text-muted',
                text: 'No match. Add new?',
                'data-add-new': true
            }).appendTo($qualiDropdown);
        }
    });

    $qualiDropdown.on('click', '.dropdown-item', function () {
        if ($(this).data('add-new')) {
            $qualiAddContainer.show();
        } else {
            $qualiInput.val($(this).text());
            $qualiAddContainer.hide();
        }
        $qualiDropdown.hide();
    });

    $('#quali_add_btn').on('click', function () {
        const newQuali = $qualiAddInput.val().trim();
        if (!newQuali) return;
        if (!qualifications.includes(newQuali)) qualifications.push(newQuali);
        $qualiInput.val(newQuali);
        $qualiAddInput.val('');
        $qualiAddContainer.hide();
    });

    // === Hobbies Multiselect ===
    const $hobbiesInput = $('#hobbies_input');
    const $hobbiesDropdown = $('#hobbies_dropdown');
    const $selectedHobbies = $('#selected_hobbies');
    const $hobbiesFinal = $('#hobbies_final');
    const $hobbyAddContainer = $('#hobby_add_container');
    const $hobbyAddInput = $('#hobby_add_input');

    function updateHobbiesFinal() {
        const list = $selectedHobbies.find('.badge').map(function () {
            return $(this).data('hobby');
        }).get();

        $hobbiesFinal.val(list.join(', '));

        $hobbiesInput[0].setCustomValidity(list.length ? '' : 'Please select at least one hobby.');
    }

    $hobbiesInput.on('input', function () {
        const term = $(this).val().toLowerCase().trim();
        $hobbiesDropdown.empty().toggle(true);

        const selected = new Set($selectedHobbies.find('.badge').map(function () {
            return $(this).data('hobby');
        }).get());

        const filtered = hobbies.filter(h => h.toLowerCase().includes(term) && !selected.has(h));

        if (filtered.length) {
            filtered.forEach(h => {
                $('<div/>', {
                    class: 'dropdown-item',
                    text: h
                }).appendTo($hobbiesDropdown);
            });
        } else {
            $('<div/>', {
                class: 'dropdown-item text-muted',
                text: 'No match. Add new?',
                'data-add-new': true
            }).appendTo($hobbiesDropdown);
        }
    });

    $hobbiesDropdown.on('click', '.dropdown-item', function () {
        if ($(this).data('add-new')) {
            $hobbyAddContainer.show();
        } else {
            const hobby = $(this).text();
            if ($selectedHobbies.find(`.badge[data-hobby="${hobby}"]`).length === 0) {
                $('<span/>', {
                    class: 'badge bg-primary me-1 mb-1',
                    'data-hobby': hobby,
                    html: `${hobby} <span class="remove-hobby ms-1" style="cursor:pointer;">&times;</span>`
                }).appendTo($selectedHobbies);
                updateHobbiesFinal();
            }
        }
        $hobbiesInput.val('');
        $hobbiesDropdown.hide();
    });

    $selectedHobbies.on('click', '.remove-hobby', function () {
        $(this).closest('.badge').remove();
        updateHobbiesFinal();
    });

    $('#hobby_add_btn').on('click', function () {
        const newHobby = $hobbyAddInput.val().trim();
        if (newHobby && !hobbies.includes(newHobby)) {
            hobbies.push(newHobby);
            $('<span/>', {
                class: 'badge bg-success me-1 mb-1',
                'data-hobby': newHobby,
                html: `${newHobby} <span class="remove-hobby ms-1" style="cursor:pointer;">&times;</span>`
            }).appendTo($selectedHobbies);
            updateHobbiesFinal();
        }
        $hobbyAddInput.val('');
        $hobbyAddContainer.hide();
    });

    // === Form Submission ===
    $form.on('submit', function (e) {
        e.preventDefault();
        $form.addClass('was-validated');
        updateHobbiesFinal();

        if (!this.checkValidity()) {
            $('html, body').animate({ scrollTop: $('.is-invalid').first().offset().top - 100 }, 300);
            return;
        }

        const $submitBtn = $form.find('[type="submit"]');
        const originalText = $submitBtn.text();
        $submitBtn.prop('disabled', true).text(isEditMode ? 'Updating...' : 'Submitting...');

        const formData = new FormData(this);
        if (!formData.has('hobbies_final')) {
            formData.append('hobbies_final', $hobbiesFinal.val());
        }

        setTimeout(() => {
            $.ajax({
                url: $form.attr('action'),
                method: $form.attr('method') || 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.redirect) {
                        window.location.href = response.redirect;
                    } else {
                        alert('Success!');
                        $submitBtn.prop('disabled', false).text(originalText);
                    }
                },
                error: function (xhr) {
                    alert(xhr.responseJSON?.message || 'Form submission failed. Please try again.');
                    $submitBtn.prop('disabled', false).text(originalText);
                }
            });
        }, 3000);
    });

    // === File Validation ===
    $('#profile').on('change', function () {
        const file = this.files[0];
        if (file) {
            const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!validTypes.includes(file.type)) {
                this.setCustomValidity('Only JPG, PNG, and GIF images are allowed.');
            } else if (file.size > 2 * 1024 * 1024) {
                this.setCustomValidity('File size must be less than 2MB.');
            } else {
                this.setCustomValidity('');
            }
        } else {
            this.setCustomValidity('');
        }
    });
});
