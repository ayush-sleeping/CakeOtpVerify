<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\AppController;
use App\Service\TwilioService;

class VerificationsController extends AppController
{
    private $twilioService;

    public function initialize(): void
    {
        parent::initialize();
        $this->twilioService = new TwilioService();
    }

    /* Step 1: Registration form with email input : */
    public function register()
    {
        // Just render the registration page
        // CakePHP will automatically render templates/Verifications/register.php
    }

    /* AJAX endpoint to send OTP to email : */
    public function sendOtp()
    {
        $this->request->allowMethod(['post']);
        $this->viewBuilder()->disableAutoLayout();

        try {
            // Get email from request
            $email = $this->request->getData('email');

            // Validate email
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $this->response->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => false,
                        'message' => 'Please provide a valid email address'
                    ]));
            }

            // Generate 6-digit OTP
            $otp = sprintf('%06d', mt_rand(0, 999999));

            // Store OTP and email in session (expires in 10 minutes)
            $session = $this->request->getSession();
            $session->write('email_otp', $otp);
            $session->write('email', $email);
            $session->write('otp_generated_at', time());

            // Send OTP via Twilio email
            $sent = $this->twilioService->sendEmail($email, $otp);

            if ($sent) {
                return $this->response->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => true,
                        'message' => 'OTP sent successfully to your email'
                    ]));
            }

            return $this->response->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'Failed to send OTP. Please try again.'
                ]));
        } catch (\Exception $e) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'An error occurred: ' . $e->getMessage()
                ]));
        }
    }

    /* Step 2: Verify page - User enters OTP : */
    public function verify()
    {
        // Check if OTP was sent (session exists)
        $session = $this->request->getSession();

        if (!$session->check('email_otp') || !$session->check('email')) {
            $this->Flash->error('Please request OTP first');
            return $this->redirect(['action' => 'register']);
        }

        // Pass email to view (masked for display)
        $email = $session->read('email');
        $this->set('email', $email);
    }

    /* AJAX endpoint to verify OTP : */
    public function verifyOtp()
    {
        $this->request->allowMethod(['post']);
        $this->viewBuilder()->disableAutoLayout();

        try {
            $session = $this->request->getSession();

            // Get OTP from request
            $enteredOtp = $this->request->getData('otp');

            // Validate OTP input
            if (empty($enteredOtp) || !preg_match('/^\d{6}$/', $enteredOtp)) {
                return $this->response->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => false,
                        'message' => 'Please enter a valid 6-digit OTP'
                    ]));
            }

            // Check if session has OTP
            if (!$session->check('email_otp')) {
                return $this->response->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => false,
                        'message' => 'Session expired. Please request OTP again.'
                    ]));
            }

            // Get stored OTP and timestamp
            $storedOtp = $session->read('email_otp');
            $otpGeneratedAt = $session->read('otp_generated_at');

            // Check if OTP expired (10 minutes)
            if (time() - $otpGeneratedAt > 600) {
                // Clear expired OTP
                $session->delete('email_otp');
                $session->delete('otp_generated_at');

                return $this->response->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => false,
                        'message' => 'OTP has expired. Please request a new one.'
                    ]));
            }

            // Verify OTP
            if ($enteredOtp === $storedOtp) {
                // Mark as verified
                $session->write('email_verified', true);

                return $this->response->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => true,
                        'message' => 'Email verified successfully!'
                    ]));
            } else {
                return $this->response->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => false,
                        'message' => 'Invalid OTP. Please try again.'
                    ]));
            }
        } catch (\Exception $e) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'An error occurred: ' . $e->getMessage()
                ]));
        }
    }

    /* Step 3: Success page : */
    public function success()
    {
        $session = $this->request->getSession();

        // Check if email was verified
        if (!$session->check('email_verified') || !$session->read('email_verified')) {
            $this->Flash->error('Please complete verification first');
            return $this->redirect(['action' => 'register']);
        }

        // Get verified email
        $email = $session->read('email');

        // Create a simple object to pass to view
        $verification = (object)[
            'email' => $email,
            'modified' => new \DateTime()
        ];

        // Clear session after showing success
        $session->delete('email_otp');
        $session->delete('email');
        $session->delete('otp_generated_at');
        $session->delete('email_verified');

        $this->set(compact('verification'));
    }
}
