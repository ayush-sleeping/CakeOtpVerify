<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0">Verification - Step 2</h3>
                </div>
                <div class="card-body">
                    <?= $this->Form->create(null, ['id' => 'verifyForm']) ?>

                    <!-- Email Verification -->
                    <div class="mb-4">
                        <label class="form-label">Email</label>
                        <input type="email"
                            class="form-control mb-2"
                            value="<?= h($verification->email) ?>"
                            readonly>

                        <label for="email_otp" class="form-label">Enter Email OTP</label>
                        <div class="input-group">
                            <input type="text"
                                class="form-control"
                                id="email_otp"
                                name="email_otp"
                                placeholder="6-digit code"
                                maxlength="6"
                                pattern="[0-9]{6}"
                                required>
                            <button class="btn btn-outline-secondary resend-btn"
                                type="button"
                                data-type="email">
                                Resend OTP
                            </button>
                        </div>
                        <small class="text-muted" id="emailOtpStatus"></small>
                    </div>

                    <!-- Phone Verification -->
                    <div class="mb-4">
                        <label class="form-label">Phone Number</label>
                        <input type="tel"
                            class="form-control mb-2"
                            value="<?= h($verification->phone) ?>"
                            readonly>

                        <label for="phone_otp" class="form-label">Enter Phone OTP</label>
                        <div class="input-group">
                            <input type="text"
                                class="form-control"
                                id="phone_otp"
                                name="phone_otp"
                                placeholder="6-digit code"
                                maxlength="6"
                                pattern="[0-9]{6}"
                                required>
                            <button class="btn btn-outline-secondary resend-btn"
                                type="button"
                                data-type="phone">
                                Resend OTP
                            </button>
                        </div>
                        <small class="text-muted" id="phoneOtpStatus"></small>
                    </div>

                    <!-- Info Alert -->
                    <div class="alert alert-warning" role="alert">
                        <small>
                            <i class="bi bi-info-circle"></i>
                            Enter the 6-digit codes sent to your email and phone.
                            Codes expire in 10 minutes.
                        </small>
                    </div>

                    <!-- Verify Button -->
                    <button type="submit" class="btn btn-primary w-100">
                        Verify & Continue
                    </button>

                    <?= $this->Form->end() ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        let cooldowns = {
            email: false,
            phone: false
        };

        // Format OTP input - only allow numbers
        document.querySelectorAll('input[name$="_otp"]').forEach(input => {
            input.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);
            });
        });

        // Resend OTP handlers
        document.querySelectorAll('.resend-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const type = this.dataset.type;

                if (cooldowns[type]) {
                    alert('Please wait before requesting another OTP');
                    return;
                }

                resendOtp(type, this);
            });
        });

        // Resend OTP function
        function resendOtp(type, button) {
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Sending...';

            fetch('/verifications/resend-otp', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': getCsrfToken()
                    },
                    body: JSON.stringify({
                        type: type
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const statusElement = document.getElementById(type + 'OtpStatus');
                        statusElement.textContent = '✓ New OTP sent successfully';
                        statusElement.classList.remove('text-danger');
                        statusElement.classList.add('text-success');

                        // Clear the input
                        document.getElementById(type + '_otp').value = '';

                        // Start cooldown
                        startCooldown(type, button);
                    } else {
                        alert(data.message || 'Failed to resend OTP');
                        button.disabled = false;
                        button.textContent = 'Resend OTP';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                    button.disabled = false;
                    button.textContent = 'Resend OTP';
                });
        }

        // Cooldown timer
        function startCooldown(type, button) {
            cooldowns[type] = true;
            let seconds = 60;

            const interval = setInterval(() => {
                seconds--;
                button.textContent = `Wait ${seconds}s`;

                if (seconds <= 0) {
                    clearInterval(interval);
                    cooldowns[type] = false;
                    button.disabled = false;
                    button.textContent = 'Resend OTP';
                }
            }, 1000);
        }

        // Get CSRF Token
        function getCsrfToken() {
            const token = document.querySelector('meta[name="csrf-token"]');
            return token ? token.getAttribute('content') : '';
        }

        // Form validation before submit
        document.getElementById('verifyForm').addEventListener('submit', function(e) {
            const emailOtp = document.getElementById('email_otp').value;
            const phoneOtp = document.getElementById('phone_otp').value;

            if (emailOtp.length !== 6 || phoneOtp.length !== 6) {
                e.preventDefault();
                alert('Please enter valid 6-digit OTP codes');
                return false;
            }
        });
    });
</script>
