<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-body p-4">
                    <h3 class="card-title text-center mb-4">Register</h3>

                    <!-- Main Parent Form -->
                    <form id="mainForm">

                        <!-- Email Section (Child Form 1) -->
                        <div class="email-section mb-3">
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

                        <!-- Phone Section (Child Form 2) -->
                        <div class="phone-section mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <div class="input-group">
                                <input type="tel"
                                    class="form-control"
                                    id="phone"
                                    name="phone"
                                    placeholder="Enter your phone number (e.g., +1234567890)"
                                    required>
                                <button class="btn btn-outline-primary"
                                    type="button"
                                    id="phoneOtpBtn"
                                    style="display: none;">
                                    Get OTP
                                </button>
                            </div>
                            <small class="text-muted" id="phoneStatus"></small>
                            <div class="form-text">Include country code (e.g., +1 for US, +91 for India)</div>
                        </div>

                        <!-- Proceed Button -->
                        <button type="submit"
                            class="btn btn-secondary w-100"
                            id="proceedBtn"
                            disabled>
                            Proceed to Verification
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // State variables
        let emailOtpSent = false;
        let phoneOtpSent = false;

        // DOM elements
        const mainForm = document.getElementById('mainForm');
        const emailInput = document.getElementById('email');
        const emailOtpBtn = document.getElementById('emailOtpBtn');
        const emailStatus = document.getElementById('emailStatus');

        const phoneInput = document.getElementById('phone');
        const phoneOtpBtn = document.getElementById('phoneOtpBtn');
        const phoneStatus = document.getElementById('phoneStatus');

        const proceedBtn = document.getElementById('proceedBtn');

        // ============================================
        // EMAIL VALIDATION - Show Get OTP Button
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
        // PHONE VALIDATION - Show Get OTP Button
        // ============================================
        phoneInput.addEventListener('input', function() {
            const phone = this.value.trim();

            if (validatePhone(phone)) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');

                // Show Get OTP button only if OTP not sent yet
                if (!phoneOtpSent) {
                    phoneOtpBtn.style.display = 'block';
                }
            } else {
                this.classList.remove('is-valid');
                this.classList.add('is-invalid');
                phoneOtpBtn.style.display = 'none';
            }
        });

        // Phone validation helper (basic validation for international format)
        function validatePhone(phone) {
            // Must start with + and have 10-15 digits
            const phoneRegex = /^\+[1-9]\d{9,14}$/;
            return phoneRegex.test(phone);
        }

        // ============================================
        // SEND EMAIL OTP
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

                        // Check if both OTPs are sent
                        updateProceedButton();

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
        // SEND PHONE OTP
        // ============================================
        phoneOtpBtn.addEventListener('click', function() {
            const phone = phoneInput.value.trim();

            if (!validatePhone(phone)) {
                toastr.error('Please enter a valid phone number with country code', '', {
                    showMethod: "slideDown",
                    hideMethod: "slideUp",
                    timeOut: 1500,
                    closeButton: true
                });
                return;
            }

            // Show loading state
            phoneOtpBtn.disabled = true;
            phoneOtpBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Sending...';

            // AJAX call to send Phone OTP
            $.ajax({
                type: "POST",
                url: "/verifications/send-phone-otp",
                data: {
                    phone: phone
                },
                headers: {
                    'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(data) {
                    if (data.success) {
                        // Mark OTP as sent
                        phoneOtpSent = true;

                        // Update UI
                        phoneStatus.textContent = '✓ OTP sent to your phone';
                        phoneStatus.className = 'text-success';
                        phoneOtpBtn.style.display = 'none';

                        // Check if both OTPs are sent
                        updateProceedButton();

                        // Show success message
                        toastr.success(data.message || 'OTP sent successfully!', '', {
                            showMethod: "slideDown",
                            hideMethod: "slideUp",
                            timeOut: 2000,
                            closeButton: true
                        });
                    } else {
                        // Reset button
                        phoneOtpBtn.disabled = false;
                        phoneOtpBtn.textContent = 'Get OTP';

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
                    phoneOtpBtn.disabled = false;
                    phoneOtpBtn.textContent = 'Get OTP';

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
        // UPDATE PROCEED BUTTON STATE
        // ============================================
        function updateProceedButton() {
            if (emailOtpSent && phoneOtpSent) {
                proceedBtn.disabled = false;
                proceedBtn.classList.remove('btn-secondary');
                proceedBtn.classList.add('btn-success');
                proceedBtn.textContent = '✓ Proceed to Verification';
            }
        }

        // ============================================
        // PROCEED TO VERIFICATION PAGE (Main Form Submit)
        // ============================================
        mainForm.addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent default form submission

            if (!emailOtpSent || !phoneOtpSent) {
                toastr.error('Please send OTP to both email and phone', '', {
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
