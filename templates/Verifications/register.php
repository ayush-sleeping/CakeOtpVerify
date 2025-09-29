<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-body p-4">
                    <h3 class="card-title text-center mb-4">Register</h3>

                    <!-- Email Field -->
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <div class="input-group">
                            <input type="email"
                                class="form-control"
                                id="email"
                                name="email"
                                placeholder="Enter your email"
                                required>
                            <button class="btn btn-outline-primary"
                                type="button"
                                id="emailOtpBtn"
                                style="display: none;">
                                Get OTP
                            </button>
                        </div>
                        <small class="text-muted" id="emailStatus"></small>
                    </div>

                    <!-- Proceed Button -->
                    <button type="button"
                        class="btn btn-secondary w-100"
                        id="proceedBtn"
                        disabled>
                        Proceed to Verification
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // State variables
        let emailOtpSent = false;

        // DOM elements
        const emailInput = document.getElementById('email');
        const emailOtpBtn = document.getElementById('emailOtpBtn');
        const emailStatus = document.getElementById('emailStatus');
        const proceedBtn = document.getElementById('proceedBtn');

        // ============================================
        // STEP 1: Email Validation - Show Get OTP Button
        // ============================================
        emailInput.addEventListener('input', function() {
            const email = this.value.trim();

            if (validateEmail(email)) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');

                // Show Get OTP button only if OTP not sent yet
                if (!emailOtpSent) {
                    emailOtpBtn.style.display = 'block';
                }
            } else {
                this.classList.remove('is-valid');
                this.classList.add('is-invalid');
                emailOtpBtn.style.display = 'none';
            }
        });

        // Email validation helper
        function validateEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        // ============================================
        // STEP 2: Send OTP via AJAX
        // ============================================
        emailOtpBtn.addEventListener('click', function() {
            const email = emailInput.value.trim();

            if (!validateEmail(email)) {
                toastr.error('Please enter a valid email', '', {
                    showMethod: "slideDown",
                    hideMethod: "slideUp",
                    timeOut: 1500,
                    closeButton: true
                });
                return;
            }

            // Show loading state
            emailOtpBtn.disabled = true;
            emailOtpBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Sending...';

            // AJAX call to send OTP
            $.ajax({
                type: "POST",
                url: "/verifications/send-otp",
                data: {
                    email: email
                },
                headers: {
                    'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(data) {
                    if (data.success) {
                        // Mark OTP as sent
                        emailOtpSent = true;

                        // Update UI
                        emailStatus.textContent = '✓ OTP sent to your email';
                        emailStatus.className = 'text-success';
                        emailOtpBtn.style.display = 'none';

                        // Enable proceed button
                        proceedBtn.disabled = false;
                        proceedBtn.classList.remove('btn-secondary');
                        proceedBtn.classList.add('btn-success');
                        proceedBtn.textContent = '✓ Proceed to Verification';

                        // Show success message
                        toastr.success(data.message || 'OTP sent successfully!', '', {
                            showMethod: "slideDown",
                            hideMethod: "slideUp",
                            timeOut: 2000,
                            closeButton: true
                        });
                    } else {
                        // Reset button
                        emailOtpBtn.disabled = false;
                        emailOtpBtn.textContent = 'Get OTP';

                        // Show error
                        toastr.error(data.message || 'Failed to send OTP', '', {
                            showMethod: "slideDown",
                            hideMethod: "slideUp",
                            timeOut: 2000,
                            closeButton: true
                        });
                    }
                },
                error: function(xhr) {
                    // Reset button
                    emailOtpBtn.disabled = false;
                    emailOtpBtn.textContent = 'Get OTP';

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

        // ============================================
        // STEP 3: Proceed to Verification Page
        // ============================================
        proceedBtn.addEventListener('click', function() {
            if (!emailOtpSent) {
                toastr.error('Please send OTP first', '', {
                    showMethod: "slideDown",
                    hideMethod: "slideUp",
                    timeOut: 1500,
                    closeButton: true
                });
                return;
            }

            // Show loading
            proceedBtn.disabled = true;
            proceedBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Proceeding...';

            // Redirect to verify page
            window.location.href = '/verify';
        });
    });
</script>
