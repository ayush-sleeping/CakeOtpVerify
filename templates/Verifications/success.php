<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-success">
                <div class="card-body text-center py-5">
                    <!-- Success Icon -->
                    <div class="mb-4">
                        <div class="rounded-circle bg-success d-inline-flex p-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="white" class="bi bi-check-lg" viewBox="0 0 16 16">
                                <path d="M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425a.247.247 0 0 1 .02-.022Z" />
                            </svg>
                        </div>
                    </div>

                    <!-- Success Message -->
                    <h2 class="text-success mb-3">Verification Successful!</h2>
                    <p class="lead mb-4">
                        Your email and phone number have been verified successfully.
                    </p>

                    <!-- Verified Details -->
                    <div class="bg-light rounded p-3 mb-4">
                        <div class="row">
                            <div class="col-12 mb-2">
                                <strong>Verified Email:</strong>
                                <div class="text-muted"><?= h($verification->email) ?></div>
                            </div>
                            <div class="col-12">
                                <strong>Verified Phone:</strong>
                                <div class="text-muted"><?= h($verification->phone) ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-grid gap-2">
                        <a href="/verifications/register" class="btn btn-primary">
                            Start New Verification
                        </a>
                        <a href="/" class="btn btn-outline-secondary">
                            Back to Home
                        </a>
                    </div>
                </div>
            </div>

            <!-- Additional Info -->
            <div class="text-center mt-3">
                <small class="text-muted">
                    Verification completed at <?= $verification->modified->format('F j, Y, g:i a') ?>
                </small>
            </div>
        </div>
    </div>
</div>
