<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <form id="registrationForm" method="POST" enctype="multipart/form-data" autocomplete="off">

                        <!-- Email Field -->
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <div class="input-group">
                                <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
                                <button class="btn btn-outline-primary otp-btn" type="button" id="emailOtpBtn" style="display: none;" data-type="email">Get OTP</button>
                            </div>
                            <div class="invalid-feedback"></div>
                            <small class="text-muted" id="emailStatus"></small>
                        </div>

                        <!-- Phone Field -->
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <div class="input-group">
                                <input type="tel" class="form-control" id="phone" name="phone" placeholder="Enter your phone number" required>
                                <button class="btn btn-outline-primary otp-btn" type="button" id="phoneOtpBtn" style="display: none;" data-type="phone">Get OTP</button>
                            </div>
                            <div class="invalid-feedback"></div>
                            <small class="text-muted" id="phoneStatus"></small>
                        </div>

                        <!-- Proceed Button -->
                        <button type="button" class="btn btn-primary w-100" id="proceedBtn" disabled>
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
    // Global variables
    let emailOtpSent = false;
    let phoneOtpSent = false;
    let verificationId = null;

    // Get DOM elements
    const emailInput = document.getElementById('email');
    const phoneInput = document.getElementById('phone');
    const emailOtpBtn = document.getElementById('emailOtpBtn');
    const phoneOtpBtn = document.getElementById('phoneOtpBtn');
    const emailStatus = document.getElementById('emailStatus');
    const phoneStatus = document.getElementById('phoneStatus');
    const proceedBtn = document.getElementById('proceedBtn');

    // ==================================================
    // STEP 1: EMAIL & PHONE VALIDATION + SHOW GET OTP BUTTONS
    // ==================================================

    // Email validation and show Get OTP button
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

    // Phone validation and show Get OTP button
    phoneInput.addEventListener('input', function() {
        const phone = this.value.replace(/\D/g, ''); // Remove non-digits

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

    // Validation helper functions
    function validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    function validatePhone(phone) {
        return /^\d{10}$/.test(phone);
    }

    // ==================================================
    // STEP 2: SEND OTP VIA AJAX CALLS TO CONTROLLER
    // ==================================================

    // Email OTP button click
    emailOtpBtn.addEventListener('click', function() {
        const email = emailInput.value.trim();
        sendOtpToController('email', email, this);
    });

    // Phone OTP button click
    phoneOtpBtn.addEventListener('click', function() {
        const phone = phoneInput.value.replace(/\D/g, '');
        sendOtpToController('phone', phone, this);
    });

    // Send OTP to controller via Ajax
    function sendOtpToController(type, value, button) {
        // Disable button and show loading
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Sending...';

        // Prepare data for Ajax call
        const requestData = {
            type: type,
            value: value
        };

        // Add verification_id if exists
        if (verificationId) {
            requestData.verification_id = verificationId;
        }

        // Make Ajax call to controller
        fetch('/verifications/send-otp', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': getCsrfToken()
            },
            body: JSON.stringify(requestData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Store verification ID if provided
                if (data.verification_id) {
                    verificationId = data.verification_id;
                }

                // Update UI based on type
                if (type === 'email') {
                    emailOtpSent = true;
                    emailStatus.textContent = '✓ OTP sent to your email';
                    emailStatus.className = 'text-success';
                    button.style.display = 'none'; // Hide button after sending
                } else if (type === 'phone') {
                    phoneOtpSent = true;
                    phoneStatus.textContent = '✓ OTP sent to your phone';
                    phoneStatus.className = 'text-success';
                    button.style.display = 'none'; // Hide button after sending
                }

                // Check if proceed button should be enabled
                checkProceedButtonStatus();

            } else {
                // Handle error
                alert(data.message || 'Failed to send OTP. Please try again.');
                button.disabled = false;
                button.textContent = 'Get OTP';
            }
        })
        .catch(error => {
            console.error('Ajax Error:', error);
            alert('Network error. Please check your connection and try again.');
            button.disabled = false;
            button.textContent = 'Get OTP';
        });
    }

    // ==================================================
    // STEP 3: ENABLE PROCEED BUTTON WHEN BOTH OTP SENT
    // ==================================================

    // Check if both OTPs are sent and enable Proceed button
    function checkProceedButtonStatus() {
        if (emailOtpSent && phoneOtpSent) {
            proceedBtn.disabled = false;
            proceedBtn.classList.remove('btn-secondary');
            proceedBtn.classList.add('btn-success');
            proceedBtn.textContent = '✓ Proceed to Verification';
        }
    }

    // ==================================================
    // STEP 4: REDIRECT TO VERIFY PAGE ON PROCEED CLICK
    // ==================================================

    // Proceed button click - redirect to verify page
    proceedBtn.addEventListener('click', function() {
        if (emailOtpSent && phoneOtpSent && verificationId) {
            // Create hidden form to submit data
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/verifications/proceed-to-verify';

            // Add email field
            const emailField = document.createElement('input');
            emailField.type = 'hidden';
            emailField.name = 'email';
            emailField.value = emailInput.value.trim();
            form.appendChild(emailField);

            // Add phone field
            const phoneField = document.createElement('input');
            phoneField.type = 'hidden';
            phoneField.name = 'phone';
            phoneField.value = phoneInput.value.replace(/\D/g, '');
            form.appendChild(phoneField);

            // Add verification ID
            const verificationField = document.createElement('input');
            verificationField.type = 'hidden';
            verificationField.name = 'verification_id';
            verificationField.value = verificationId;
            form.appendChild(verificationField);

            // Add CSRF token
            const csrfField = document.createElement('input');
            csrfField.type = 'hidden';
            csrfField.name = '_csrfToken';
            csrfField.value = getCsrfToken();
            form.appendChild(csrfField);

            // Submit form
            document.body.appendChild(form);
            form.submit();
        } else {
            alert('Please send both email and phone OTPs first.');
        }
    });

    // ==================================================
    // HELPER FUNCTIONS
    // ==================================================

    // Get CSRF Token from meta tag
    function getCsrfToken() {
        const token = document.querySelector('meta[name="csrf-token"]');
        return token ? token.getAttribute('content') : '';
    }
});
</script>
