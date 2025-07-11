$(document).ready(function () {
    // Form submission
    $('#stud_form').on('submit', function (event) {
        event.preventDefault(); // Prevent default form submission

        // Validate form fields
        if (this.checkValidity() === false) {
            event.stopPropagation(); // Stop if the form is invalid
        } else {
            // Prepare form data
            const formData = new FormData(this);

            // AJAX request
            $.ajax({
                url: $(this).attr('action'), // Form action URL
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function (response) {
                    // Redirect after 3 seconds
                    setTimeout(function () {
                        window.location.href = '05_crud.php';
                    }, 3000);
                },
                error: function (xhr, status, error) {
                    // Handle error response
                    alert('An error occurred: ' + error);
                }
            });
        }

        this.classList.add('was-validated'); // Add Bootstrap validation class
    });
});


(() => {
    'use strict';
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
})();

