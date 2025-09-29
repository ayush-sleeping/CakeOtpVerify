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

    /* Step 1: Registration form with email and phone input */
    public function register()
    {
        // Just render the registration page
        // CakePHP will automatically render templates/Verifications/register.php
    }

    /* AJAX endpoint to send OTP to email */
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
            $session->write('email_otp_generated_at', time());

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

    /* AJAX endpoint to send OTP to phone */
    public function sendPhoneOtp()
    {
        $this->request->allowMethod(['post']);
        $this->viewBuilder()->disableAutoLayout();

        try {
            // Get phone from request
            $phone = $this->request->getData('phone');

            // Validate phone (must start with + and have 10-15 digits)
            if (empty($phone) || !preg_match('/^\+[1-9]\d{9,14}$/', $phone)) {
                return $this->response->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => false,
                        'message' => 'Please provide a valid phone number with country code (e.g., +1234567890)'
                    ]));
            }

            // Generate 6-digit OTP
            $otp = sprintf('%06d', mt_rand(0, 999999));

            // Store OTP and phone in session (expires in 10 minutes)
            $session = $this->request->getSession();
            $session->write('phone_otp', $otp);
            $session->write('phone', $phone);
            $session->write('phone_otp_generated_at', time());

            // Send OTP via Twilio SMS
            $sent = $this->twilioService->sendSMS($phone, $otp);

            if ($sent) {
                return $this->response->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => true,
                        'message' => 'OTP sent successfully to your phone'
                    ]));
            }

            return $this->response->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'Failed to send SMS. Please check your phone number and try again.'
                ]));
        } catch (\Exception $e) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'An error occurred: ' . $e->getMessage()
                ]));
        }
    }

    /* Step 2: Verify page - User enters both OTPs */
    public function verify()
    {
        // Check if both OTPs were sent (session exists)
        $session = $this->request->getSession();

        if (!$session->check('email_otp') || !$session->check('email')) {
            $this->Flash->error('Please request email OTP first');
            return $this->redirect(['action' => 'register']);
        }

        if (!$session->check('phone_otp') || !$session->check('phone')) {
            $this->Flash->error('Please request phone OTP first');
            return $this->redirect(['action' => 'register']);
        }

        // Pass email and phone to view
        $email = $session->read('email');
        $phone = $session->read('phone');

        $this->set(compact('email', 'phone'));
    }

    /* AJAX endpoint to verify both OTPs */
    public function verifyOtp()
    {
        $this->request->allowMethod(['post']);
        $this->viewBuilder()->disableAutoLayout();

        try {
            $session = $this->request->getSession();

            // Get OTPs from request
            $enteredEmailOtp = $this->request->getData('email_otp');
            $enteredPhoneOtp = $this->request->getData('phone_otp');

            // Validate OTP inputs
            if (empty($enteredEmailOtp) || !preg_match('/^\d{6}$/', $enteredEmailOtp)) {
                return $this->response->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => false,
                        'message' => 'Please enter a valid 6-digit email OTP'
                    ]));
            }

            if (empty($enteredPhoneOtp) || !preg_match('/^\d{6}$/', $enteredPhoneOtp)) {
                return $this->response->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => false,
                        'message' => 'Please enter a valid 6-digit phone OTP'
                    ]));
            }

            // Check if session has OTPs
            if (!$session->check('email_otp') || !$session->check('phone_otp')) {
                return $this->response->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => false,
                        'message' => 'Session expired. Please go back to /register and request OTPs again.'
                    ]));
            }

            // Get stored OTPs and timestamps
            $storedEmailOtp = $session->read('email_otp');
            $storedPhoneOtp = $session->read('phone_otp');
            $emailOtpGeneratedAt = $session->read('email_otp_generated_at');
            $phoneOtpGeneratedAt = $session->read('phone_otp_generated_at');

            // Check if OTPs expired (10 minutes)
            $currentTime = time();
            $emailExpired = ($currentTime - $emailOtpGeneratedAt) > 600;
            $phoneExpired = ($currentTime - $phoneOtpGeneratedAt) > 600;

            if ($emailExpired || $phoneExpired) {
                // Clear expired OTPs
                $session->delete('email_otp');
                $session->delete('phone_otp');
                $session->delete('email_otp_generated_at');
                $session->delete('phone_otp_generated_at');

                return $this->response->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => false,
                        'message' => 'One or both OTPs have expired. Please go back to /register and request new OTPs.'
                    ]));
            }

            // Verify both OTPs
            $emailValid = ($enteredEmailOtp === $storedEmailOtp);
            $phoneValid = ($enteredPhoneOtp === $storedPhoneOtp);

            if ($emailValid && $phoneValid) {
                // Mark both as verified
                $session->write('email_verified', true);
                $session->write('phone_verified', true);

                return $this->response->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => true,
                        'message' => 'Email and phone verified successfully!'
                    ]));
            } else {
                // Build specific error message
                $errorMsg = 'Invalid OTP(s). ';
                if (!$emailValid && !$phoneValid) {
                    $errorMsg .= 'Both email and phone OTPs are incorrect.';
                } elseif (!$emailValid) {
                    $errorMsg .= 'Email OTP is incorrect.';
                } else {
                    $errorMsg .= 'Phone OTP is incorrect.';
                }
                $errorMsg .= ' Please try again or go back to /register to start over.';

                return $this->response->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => false,
                        'message' => $errorMsg
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

    /* Step 3: Success page */
    public function success()
    {
        $session = $this->request->getSession();

        // Check if both email and phone were verified
        if (!$session->check('email_verified') || !$session->read('email_verified')) {
            $this->Flash->error('Please complete email verification first');
            return $this->redirect(['action' => 'register']);
        }

        if (!$session->check('phone_verified') || !$session->read('phone_verified')) {
            $this->Flash->error('Please complete phone verification first');
            return $this->redirect(['action' => 'register']);
        }

        // Get verified email and phone
        $email = $session->read('email');
        $phone = $session->read('phone');

        // Create a simple object to pass to view
        $verification = (object)[
            'email' => $email,
            'phone' => $phone,
            'modified' => new \DateTime()
        ];

        // Clear session after showing success
        $session->delete('email_otp');
        $session->delete('phone_otp');
        $session->delete('email');
        $session->delete('phone');
        $session->delete('email_otp_generated_at');
        $session->delete('phone_otp_generated_at');
        $session->delete('email_verified');
        $session->delete('phone_verified');

        $this->set(compact('verification'));
    }
}
