<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-body p-4">
                    <h3 class="card-title text-center mb-4">Verify Your Email</h3>

                    <!-- Info Message -->
                    <div class="alert alert-info mb-4">
                        <small>
                            We've sent a 6-digit verification code to<br>
                            <strong><?= h($email) ?></strong>
                        </small>
                    </div>

                    <!-- OTP Verification Form -->
                    <form id="verifyOtpForm" method="POST" action="/verifications/verify-otp">
                        <?= $this->Form->secure() ?>

                        <!-- OTP Input -->
                        <div class="mb-4">
                            <label for="otp" class="form-label">Enter Verification Code</label>
                            <input type="text"
                                   class="form-control form-control-lg text-center"
                                   id="otp"
                                   name="otp"
                                   placeholder="000000"
                                   maxlength="6"
                                   pattern="\d{6}"
                                   inputmode="numeric"
                                   autocomplete="one-time-code"
                                   required
                                   style="letter-spacing: 10px; font-size: 24px;">
                            <div class="form-text text-center">Please enter the 6-digit code</div>
                        </div>

                        <!-- Verify Button -->
                        <button type="submit"
                                class="btn btn-primary w-100 mb-3"
                                id="verifyBtn">
                            Verify Email
                        </button>
                    </form>

                    <!-- Resend OTP Link -->
                    <div class="text-center">
                        <small class="text-muted">
                            Didn't receive the code?
                            <a href="/register" class="text-decoration-none">Request new OTP</a>
                        </small>
                    </div>

                    <!-- Timer Display (Optional) -->
                    <div class="text-center mt-3">
                        <small class="text-muted" id="timer"></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const otpInput = document.getElementById('otp');
    const verifyBtn = document.getElementById('verifyBtn');
    const verifyForm = document.getElementById('verifyOtpForm');

    // Auto-focus on OTP input
    otpInput.focus();

    // Only allow numbers in OTP input
    otpInput.addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    // Auto-submit when 6 digits are entered (optional)
    otpInput.addEventListener('input', function() {
        if (this.value.length === 6) {
            // Optionally auto-submit
            // verifyForm.dispatchEvent(new Event('submit'));
        }
    });

    // Form submission via AJAX
    $(document).ready(function() {
        $('#verifyOtpForm').submit(function(e) {
            e.preventDefault();

            const otp = $('#otp').val().trim();

            // Validate OTP
            if (otp.length !== 6 || !/^\d{6}$/.test(otp)) {
                toastr.error('Please enter a valid 6-digit OTP', '', {
                    showMethod: "slideDown",
                    hideMethod: "slideUp",
                    timeOut: 1500,
                    closeButton: true
                });
                return;
            }

            // Show loading state
            $('#verifyBtn').prop('disabled', true);
            $('#verifyBtn').html('<span class="spinner-border spinner-border-sm"></span> Verifying...');

            // AJAX call to verify OTP
            $.ajax({
                type: "POST",
                url: $(this).attr('action'),
                data: new FormData(this),
                contentType: false,
                cache: false,
                processData: false,
                headers: {
                    'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(data) {
                    if (data.success) {
                        // Show success message
                        toastr.success(data.message || 'Email verified successfully!', '', {
                            showMethod: "slideDown",
                            hideMethod: "slideUp",
                            timeOut: 1500,
                            closeButton: true
                        });

                        // Redirect to success page after short delay
                        setTimeout(function() {
                            window.location.href = '/success';
                        }, 1500);
                    } else {
                        // Reset button
                        $('#verifyBtn').prop('disabled', false);
                        $('#verifyBtn').text('Verify Email');

                        // Clear OTP input
                        $('#otp').val('').focus();

                        // Show error
                        toastr.error(data.message || 'Invalid OTP. Please try again.', '', {
                            showMethod: "slideDown",
                            hideMethod: "slideUp",
                            timeOut: 2000,
                            closeButton: true
                        });
                    }
                },
                error: function(xhr) {
                    // Reset button
                    $('#verifyBtn').prop('disabled', false);
                    $('#verifyBtn').text('Verify Email');

                    // Show error
                    toastr.error('An error occurred. Please try again.', '', {
                        showMethod: "slideDown",
                        hideMethod: "slideUp",
                        timeOut: 2000,
                        closeButton: true
                    });
                }
            });
        });
    });

    // Optional: OTP expiry timer (10 minutes = 600 seconds)
    let timeLeft = 600;
    const timerDisplay = document.getElementById('timer');

    const countdown = setInterval(function() {
        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;

        timerDisplay.textContent = `Code expires in ${minutes}:${seconds.toString().padStart(2, '0')}`;

        if (timeLeft <= 0) {
            clearInterval(countdown);
            timerDisplay.textContent = 'Code expired. Please request a new one.';
            timerDisplay.classList.remove('text-muted');
            timerDisplay.classList.add('text-danger');
            verifyBtn.disabled = true;
        }

        timeLeft--;
    }, 1000);
});
</script>
