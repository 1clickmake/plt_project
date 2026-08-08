/* Contact Form Submission */
async function submitContactForm(e) {
    e.preventDefault();
    const btn = document.getElementById('btn-submit-contact');
    const originalContent = btn.innerHTML;

    // Disable button & show loading
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Sending...';

    const formData = new FormData(e.target);
    const data = {};
    formData.forEach((value, key) => data[key] = value);

    try {
        const response = await fetch('/contact/send', {
            method: 'POST',
            body: formData // Use FormData directly as it's standard
        });

        const result = await response.json();

        if (result.success) {
            alert('Your message has been sent successfully!');
            e.target.reset();
        } else {
            alert('Error: ' + (result.message || 'Something went wrong.'));
        }
    } catch (error) {
        console.error('Contact Form Error:', error);
        alert('Failed to send message. Please try again later.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalContent;
    }
}

/* Registration & Auth Logic */
$(document).ready(function() {
    // Only run on register page
    if ($('.auth-container-register').length > 0) {
        let timer;
        const delay = 500; // Delay in ms

        function checkDuplicate(field, value, feedbackId, inputId) {
            const feedback = $('#' + feedbackId);
            const input = $('#' + inputId);

            $.get('/auth/check-duplicate', { field: field, value: value }, function(response) {

                if (response.exists) {
                    let msg = 'This ' + (field === 'user_id' ? 'ID' : 'email') + ' is already taken.';
                    if (response.message) msg = response.message;

                    feedback.text(msg).removeClass('feedback-success').addClass('feedback-error');
                    input.removeClass('input-success').addClass('input-error');
                } else {
                    feedback.text('Available!').removeClass('feedback-error').addClass('feedback-success');
                    input.removeClass('input-error').addClass('input-success');
                }
            });
        }

        $('#user_id').on('input', function() {
            clearTimeout(timer);
            const value = $(this).val();
            const feedback = $('#user_id_feedback');
            const input = $(this);

            feedback.text('').removeClass('feedback-success feedback-error');
            input.removeClass('input-error input-success');

            if (value.length >= 3) {
                timer = setTimeout(function() {
                    checkDuplicate('user_id', value, 'user_id_feedback', 'user_id');
                }, delay);
            }
        });

        $('#email').on('input', function() {
            clearTimeout(timer);
            const value = $(this).val();
            const feedback = $('#email_feedback');
            const input = $(this);

            feedback.text('').removeClass('feedback-success feedback-error');
            input.removeClass('input-error input-success');

            if (value.length >= 5 && value.includes('@')) {
                timer = setTimeout(function() {
                    checkDuplicate('email', value, 'email_feedback', 'email');
                }, delay);
            }
        });

        $('#username').on('input', function() {
            clearTimeout(timer);
            const value = $(this).val();
            const feedback = $('#username_feedback');
            const input = $(this);

            feedback.text('').removeClass('feedback-success feedback-error');
            input.removeClass('input-error input-success');

            if (value.length >= 2) {
                timer = setTimeout(function() {
                    checkDuplicate('username', value, 'username_feedback', 'username');
                }, delay);
            }
        });
    }

    // Success Modal Logic
    if ($('#successModal').length > 0) {
        let seconds = 3;
        const countdownEl = document.getElementById('countdown');

        const timer = setInterval(() => {
            seconds--;
            if(countdownEl) countdownEl.innerText = seconds;
            if (seconds <= 0) {
                clearInterval(timer);
                window.location.href = '/login';
            }
        }, 1000);

        // Overlay click to skip
        document.getElementById('successModal').addEventListener('click', function(e) {
            if (e.target === this) {
                window.location.href = '/login';
            }
        });
    }
});
