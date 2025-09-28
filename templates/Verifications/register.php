<?php

/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Verification $verification
 */
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0">Registration - Step 1</h3>
                </div>
                <div class="card-body">
                    <form id="registrationForm">
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
                                <button class="btn btn-outline-primary otp-btn"
                                    type="button"
                                    id="emailOtpBtn"
                                    style="display: none;"
                                    data-type="email">
                                    Get OTP
                                </button>
                            </div>
                            <div class="invalid-feedback"></div>
                            <small class="text-muted" id="emailStatus"></small>
                        </div>

                        <!-- Phone Field -->
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <div class="input-group">
                                <input type="tel"
                                    class="form-control"
                                    id="phone"
                                    name="phone"
                                    placeholder="Enter your phone number"
                                    required>
                                <button class="btn btn-outline-primary otp-btn"
                                    type="button"
                                    id="phoneOtpBtn"
                                    style="display: none;"
                                    data-type="phone">
                                    Get OTP
                                </button>
                            </div>
                            <div class="invalid-feedback"></div>
                            <small class="text-muted" id="phoneStatus"></small>
                        </div>

                        <!-- Progress Indicator -->
                        <div class="alert alert-info" role="alert">
                            <small>
                                <span id="otpProgress">Please request OTP for both email and phone to proceed.</span>
                            </small>
                        </div>

                        <!-- Proceed Button -->
                        <button type="button"
                            class="btn btn-success w-100"
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
        let emailOtpSent = false;
        let phoneOtpSent = false;
        let verificationId = null;
        let cooldowns = {
            email: false,
            phone: false
        };

        // Email validation
        const emailInput = document.getElementById('email');
        const emailOtpBtn = document.getElementById('emailOtpBtn');
        const emailStatus = document.getElementById('emailStatus');

        emailInput.addEventListener('input', function() {
            const isValid = validateEmail(this.value);
            if (isValid) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
                if (!emailOtpSent) {
                    emailOtpBtn.style.display = 'block';
                }
            } else {
                this.classList.remove('is-valid');
                this.classList.add('is-invalid');
                emailOtpBtn.style.display = 'none';
            }
        });

        // Phone validation
        const phoneInput = document.getElementById('phone');
        const phoneOtpBtn = document.getElementById('phoneOtpBtn');
        const phoneStatus = document.getElementById('phoneStatus');

        phoneInput.addEventListener('input', function() {
            const isValid = validatePhone(this.value);
            if (isValid) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
                if (!phoneOtpSent) {
                    phoneOtpBtn.style.display = 'block';
                }
            } else {
                this.classList.remove('is-valid');
                this.classList.add('is-invalid');
                phoneOtpBtn.style.display = 'none';
            }
        });

        // OTP Button Click Handlers
        document.querySelectorAll('.otp-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const type = this.dataset.type;
                const value = type === 'email' ? emailInput.value : phoneInput.value;

                if (cooldowns[type]) {
                    alert('Please wait before requesting another OTP');
                    return;
                }

                sendOtp(type, value, this);
            });
        });

        // Send OTP Function
        function sendOtp(type, value, button) {
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Sending...';

            fetch('/verifications/send-otp', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': getCsrfToken()
                    },
                    body: JSON.stringify({
                        type: type,
                        value: value,
                        verification_id: verificationId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (data.verification_id) {
                            verificationId = data.verification_id;
                        }

                        // Mark as sent
                        if (type === 'email') {
                            emailOtpSent = true;
                            emailStatus.textContent = '✓ OTP sent to your email';
                            emailStatus.classList.add('text-success');
                        } else {
                            phoneOtpSent = true;
                            phoneStatus.textContent = '✓ OTP sent to your phone';
                            phoneStatus.classList.add('text-success');
                        }

                        // Start cooldown
                        startCooldown(type, button);

                        // Check if both OTPs sent
                        checkProceedButton();
                    } else {
                        alert(data.message || 'Failed to send OTP');
                        button.disabled = false;
                        button.textContent = 'Get OTP';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                    button.disabled = false;
                    button.textContent = 'Get OTP';
                });
        }

        // Cooldown timer
        function startCooldown(type, button) {
            cooldowns[type] = true;
            let seconds = 60;

            const interval = setInterval(() => {
                seconds--;
                button.textContent = `Resend in ${seconds}s`;

                if (seconds <= 0) {
                    clearInterval(interval);
                    cooldowns[type] = false;
                    button.disabled = false;
                    button.textContent = 'Resend OTP';
                }
            }, 1000);
        }

        // Check if both OTPs sent
        function checkProceedButton() {
            const proceedBtn = document.getElementById('proceedBtn');
            const otpProgress = document.getElementById('otpProgress');

            if (emailOtpSent && phoneOtpSent) {
                proceedBtn.disabled = false;
                otpProgress.textContent = '✓ Both OTPs sent! You can now proceed to verification.';
            } else if (emailOtpSent) {
                otpProgress.textContent = 'Email OTP sent. Please request phone OTP to proceed.';
            } else if (phoneOtpSent) {
                otpProgress.textContent = 'Phone OTP sent. Please request email OTP to proceed.';
            }
        }

        // Proceed button click
        document.getElementById('proceedBtn').addEventListener('click', function() {
            if (emailOtpSent && phoneOtpSent && verificationId) {
                // Store data in form and submit
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '/verifications/register';

                const emailField = document.createElement('input');
                emailField.type = 'hidden';
                emailField.name = 'email';
                emailField.value = emailInput.value;
                form.appendChild(emailField);

                const phoneField = document.createElement('input');
                phoneField.type = 'hidden';
                phoneField.name = 'phone';
                phoneField.value = phoneInput.value;
                form.appendChild(phoneField);

                const csrfField = document.createElement('input');
                csrfField.type = 'hidden';
                csrfField.name = '_csrfToken';
                csrfField.value = getCsrfToken();
                form.appendChild(csrfField);

                document.body.appendChild(form);
                form.submit();
            }
        });

        // Validation functions
        function validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }

        function validatePhone(phone) {
            const re = /^[\+]?[(]?[0-9]{1,4}[)]?[-\s\.]?[(]?[0-9]{1,4}[)]?[-\s\.]?[0-9]{1,5}[-\s\.]?[0-9]{1,5}$/;
            return re.test(phone);
        }

        // Get CSRF Token
        function getCsrfToken() {
            const token = document.querySelector('meta[name="csrf-token"]');
            return token ? token.getAttribute('content') : '';
        }
    });
</script>
