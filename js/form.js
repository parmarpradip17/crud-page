// $(document).ready(function () {
//     // Form submission
//     $('#stud_form').on('submit', function (event) {
//         event.preventDefault(); // Prevent default form submission

//         // Validate form fields
//         if (this.checkValidity() === false) {
//             event.stopPropagation(); // Stop if the form is invalid
//         } else {
//             // Prepare form data
//             const formData = new FormData(this);

//             // AJAX request
//             $.ajax({
//                 url: $(this).attr('action'), // Form action URL
//                 type: 'POST',
//                 data: formData,
//                 contentType: false,
//                 processData: false,
//                 success: function (response) {
//                     // Redirect after 3 seconds
//                     setTimeout(function () {
//                         window.location.href = '05_crud.php';
//                     }, 3000);
//                 },
//                 error: function (xhr, status, error) {
//                     // Handle error response
//                     alert('An error occurred: ' + error);
//                 }
//             });
//         }

//         this.classList.add('was-validated'); // Add Bootstrap validation class
//     });
// });

$(document).ready(function () {
    $('#stud_form').on('submit', function (event) {
        event.preventDefault();

        // Validate form fields
        if (this.checkValidity() === false) {
            event.stopPropagation();
        } else {
            const formData = new FormData(this);

            // AJAX request
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function (response) {
                    // Show animated success message
                    $('#successMessage').removeClass('d-none').hide().fadeIn(500);

                    // Countdown timer
                    let seconds = 3;
                    const countdownInterval = setInterval(() => {
                        $('#countdownNumber').text(seconds);
                        if (seconds <= 0) {
                            clearInterval(countdownInterval);
                            window.location.href = '05_crud.php';
                        }
                        seconds--;
                    }, 1000);
                },
                error: function (xhr, status, error) {
                    alert('An error occurred: ' + error);
                }
            });
        }

        this.classList.add('was-validated');
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

