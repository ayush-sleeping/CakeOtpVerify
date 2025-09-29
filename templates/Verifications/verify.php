<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-body p-4">
                    <h3 class="card-title text-center mb-4">Verify Your Details</h3>

                    <!-- Info Message -->
                    <div class="alert alert-info mb-4">
                        <small>
                            We've sent verification codes to:
                        </small>
                    </div>

                    <!-- OTP Verification Form -->
                    <form id="verifyOtpForm" method="POST" action="/verifications/verify-otp">
                        <?= $this->Form->secure() ?>

                        <!-- Email OTP Section -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Email Verification</label>
                            <div class="bg-light p-2 rounded mb-2">
                                <small class="text-muted"><?= h($email) ?></small>
                            </div>
                            <input type="text"
                                   class="form-control form-control-lg text-center"
                                   id="email_otp"
                                   name="email_otp"
                                   placeholder="000000"
                                   maxlength="6"
                                   pattern="\d{6}"
                                   inputmode="numeric"
                                   autocomplete="one-time-code"
                                   required
                                   style="letter-spacing: 10px; font-size: 24px;">
                            <div class="form-text text-center">Enter 6-digit code sent to your email</div>
                        </div>

                        <!-- Phone OTP Section -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Phone Verification</label>
                            <div class="bg-light p-2 rounded mb-2">
                                <small class="text-muted"><?= h($phone) ?></small>
                            </div>
                            <input type="text"
                                   class="form-control form-control-lg text-center"
                                   id="phone_otp"
                                   name="phone_otp"
                                   placeholder="000000"
                                   maxlength="6"
                                   pattern="\d{6}"
                                   inputmode="numeric"
                                   autocomplete="one-time-code"
                                   required
                                   style="letter-spacing: 10px; font-size: 24px;">
                            <div class="form-text text-center">Enter 6-digit code sent to your phone</div>
                        </div>

                        <!-- Verify Button -->
                        <button type="submit"
                                class="btn btn-primary w-100 mb-3"
                                id="verifyBtn">
                            Verify & Continue
                        </button>
                    </form>

                    <!-- Resend OTP Link -->
                    <div class="text-center">
                        <small class="text-muted">
                            Didn't receive the codes?
                            <a href="/register" class="text-decoration-none">Go back and request new OTPs</a>
                        </small>
                    </div>

                    <!-- Timer Display -->
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
    const emailOtpInput = document.getElementById('email_otp');
    const phoneOtpInput = document.getElementById('phone_otp');
    const verifyBtn = document.getElementById('verifyBtn');
    const verifyForm = document.getElementById('verifyOtpForm');

    // Auto-focus on email OTP input
    emailOtpInput.focus();

    // Only allow numbers in OTP inputs
    emailOtpInput.addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
        // Auto-focus to phone OTP when email OTP is complete
        if (this.value.length === 6) {
            phoneOtpInput.focus();
        }
    });

    phoneOtpInput.addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    // Form submission via AJAX
    $(document).ready(function() {
        $('#verifyOtpForm').submit(function(e) {
            e.preventDefault();

            const emailOtp = $('#email_otp').val().trim();
            const phoneOtp = $('#phone_otp').val().trim();

            // Validate both OTPs
            if (emailOtp.length !== 6 || !/^\d{6}$/.test(emailOtp)) {
                toastr.error('Please enter a valid 6-digit email OTP', '', {
                    showMethod: "slideDown",
                    hideMethod: "slideUp",
                    timeOut: 1500,
                    closeButton: true
                });
                $('#email_otp').focus();
                return;
            }

            if (phoneOtp.length !== 6 || !/^\d{6}$/.test(phoneOtp)) {
                toastr.error('Please enter a valid 6-digit phone OTP', '', {
                    showMethod: "slideDown",
                    hideMethod: "slideUp",
                    timeOut: 1500,
                    closeButton: true
                });
                $('#phone_otp').focus();
                return;
            }

            // Show loading state
            $('#verifyBtn').prop('disabled', true);
            $('#verifyBtn').html('<span class="spinner-border spinner-border-sm"></span> Verifying...');

            // AJAX call to verify both OTPs
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
                        toastr.success(data.message || 'Verification successful!', '', {
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
                        $('#verifyBtn').text('Verify & Continue');

                        // Clear OTP inputs
                        $('#email_otp').val('');
                        $('#phone_otp').val('');
                        $('#email_otp').focus();

                        // Show error with specific message
                        toastr.error(data.message || 'Invalid OTP. Please try again.', '', {
                            showMethod: "slideDown",
                            hideMethod: "slideUp",
                            timeOut: 3000,
                            closeButton: true
                        });
                    }
                },
                error: function(xhr) {
                    // Reset button
                    $('#verifyBtn').prop('disabled', false);
                    $('#verifyBtn').text('Verify & Continue');

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

        timerDisplay.textContent = `Codes expire in ${minutes}:${seconds.toString().padStart(2, '0')}`;

        if (timeLeft <= 0) {
            clearInterval(countdown);
            timerDisplay.textContent = 'Codes expired. Please request new ones.';
            timerDisplay.classList.remove('text-muted');
            timerDisplay.classList.add('text-danger');
            verifyBtn.disabled = true;
        }

        timeLeft--;
    }, 1000);
});
</script>
