$(document).ready(function () {
    const form = $('#stud_form');
    const qualifications = window.qualifications || [];
    const hobbies = window.hobbies || [];
    const isEditMode = window.isEditMode || false;

    // ---- QUALIFICATION AUTOCOMPLETE ----
    const $qualiInput = $('#quali_input');
    const $qualiDropdown = $('#quali_dropdown');
    const $qualiAddContainer = $('#quali_add_container');
    const $qualiAddInput = $('#quali_add_input');

    $qualiInput.on('input', function () {
        const term = $qualiInput.val().toLowerCase().trim();
        $qualiDropdown.empty();

        if (!term) return $qualiDropdown.hide();

        const filtered = qualifications.filter(q => q.toLowerCase().includes(term));
        if (filtered.length) {
            filtered.forEach(item => $qualiDropdown.append(`<div class="dropdown-item">${item}</div>`));
        } else {
            $qualiDropdown.append(`<div class="dropdown-item text-muted">No match. Add new?</div>`);
        }
        $qualiDropdown.show();
    });

    $qualiDropdown.on('click', '.dropdown-item', function () {
        const val = $(this).text();
        if (val === 'No match. Add new?') {
            $qualiAddContainer.show();
        } else {
            $qualiInput.val(val);
            $qualiAddContainer.hide();
        }
        $qualiDropdown.hide();
    });

    $('#quali_add_btn').on('click', function () {
        const newQuali = $qualiAddInput.val().trim();
        if (!newQuali) return;
        qualifications.push(newQuali);
        $qualiInput.val(newQuali);
        $qualiAddInput.val('');
        $qualiAddContainer.hide();
    });

    // ---- HOBBIES MULTISELECT ----
    const $hobbiesInput = $('#hobbies_input');
    const $hobbiesDropdown = $('#hobbies_dropdown');
    const $selectedHobbies = $('#selected_hobbies');
    const $hobbiesFinal = $('#hobbies_final');
    const $hobbyAddContainer = $('#hobby_add_container');
    const $hobbyAddInput = $('#hobby_add_input');

    function updateHobbiesFinal() {
        const list = [];
        $selectedHobbies.find('.badge').each(function () {
            list.push($(this).text().trim().split(' ')[0]);
        });
        $hobbiesFinal.val(list.join(', '));

        if (list.length === 0) {
            $hobbiesInput[0].setCustomValidity('Please select at least one hobby.');
        } else {
            $hobbiesInput[0].setCustomValidity('');
        }
    }

    $hobbiesInput.on('input', function () {
        const term = $hobbiesInput.val().toLowerCase().trim();
        $hobbiesDropdown.empty();

        const filtered = hobbies.filter(h => h.toLowerCase().includes(term));
        if (filtered.length) {
            filtered.forEach(h => {
                if (!$hobbiesFinal.val().includes(h)) {
                    $hobbiesDropdown.append(`<div class="dropdown-item">${h}</div>`);
                }
            });
        } else {
            $hobbiesDropdown.append('<div class="dropdown-item text-muted">No match. Add new?</div>');
        }
        $hobbiesDropdown.show();
    });

    $hobbiesDropdown.on('click', '.dropdown-item', function () {
        const hobby = $(this).text();
        if (hobby === 'No match. Add new?') {
            $hobbyAddContainer.show();
        } else {
            const badge = $(`<span class="badge bg-primary me-1 mb-1">${hobby} <span class="remove-hobby ms-1" style="cursor:pointer;">&times;</span></span>`);
            $selectedHobbies.append(badge);
            updateHobbiesFinal();
        }
        $hobbiesInput.val('');
        $hobbiesDropdown.hide();
    });

    $selectedHobbies.on('click', '.remove-hobby', function () {
        $(this).parent().remove();
        updateHobbiesFinal();
    });

    $('#hobby_add_btn').on('click', function () {
        const newHobby = $hobbyAddInput.val().trim();
        if (newHobby) {
            hobbies.push(newHobby);
            const badge = $(`<span class="badge bg-success me-1 mb-1">${newHobby} <span class="remove-hobby ms-1" style="cursor:pointer;">&times;</span></span>`);
            $selectedHobbies.append(badge);
            updateHobbiesFinal();
            $hobbyAddInput.val('');
            $hobbyAddContainer.hide();
        }
    });

    // ---- FORM VALIDATION & SUBMISSION WITH 3s DELAY ----
    form.on('submit', function (e) {
        e.preventDefault();
        form.addClass('was-validated');

        if (!$hobbiesFinal.val()) {
            $hobbiesInput[0].setCustomValidity('Please select at least one hobby.');
        } else {
            $hobbiesInput[0].setCustomValidity('');
        }

        if (!this.checkValidity()) {
            $('html, body').animate({ scrollTop: $('.is-invalid').first().offset().top - 100 }, 300);
            return;
        }

        const $submitBtn = form.find('[type="submit"]');
        const originalText = $submitBtn.text();
        $submitBtn.prop('disabled', true).text(isEditMode ? 'Updating...' : 'Submitting...');

        const formData = new FormData(this);

        if (!formData.has('hobbies_final')) {
            formData.append('hobbies_final', $hobbiesFinal.val());
        }

        // Delay AJAX submission by 3 seconds
        setTimeout(function () {
            $.ajax({
                url: form.attr('action'),
                method: form.attr('method'),
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.redirect) {
                        window.location.href = response.redirect;
                    } else {
                        window.location.href = '05_crud.php';
                    }
                },
                error: function (xhr) {
                    let errorMsg = 'Form submission failed. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    alert(errorMsg);
                    $submitBtn.prop('disabled', false).text(originalText);
                }
            });
        }, 3000); // <-- 3 second delay
    });

    // ---- FILE VALIDATION ----
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
        }
    });
});
