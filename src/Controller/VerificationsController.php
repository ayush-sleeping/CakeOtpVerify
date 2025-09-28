<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\AppController;
use App\Service\TwilioService;
use Cake\Http\Exception\BadRequestException;

class VerificationsController extends AppController
{
    private $twilioService;

    public function initialize(): void
    {
        parent::initialize();
        $this->twilioService = new TwilioService();
    }

    /* Step 1: Registration form with email and phone input :: */
    // ---------------------------------------------------- ::
    public function register()
    {
        $verification = $this->Verifications->newEmptyEntity();

        if ($this->request->is('post')) {
            $verification = $this->Verifications->patchEntity($verification, $this->request->getData());
            if ($this->Verifications->save($verification)) {
                $this->request->getSession()->write('verification_id', $verification->id);
                return $this->redirect(['action' => 'verify']);
            }
            $this->Flash->error(__('Unable to save verification data. Please try again.'));
        }

        $this->set(compact('verification'));
    }

    /* AJAX endpoint to send OTP :: */
    public function sendOtp()
    {
        $this->request->allowMethod(['post']);
        $this->viewBuilder()->disableAutoLayout();

        $type = $this->request->getData('type');
        $value = $this->request->getData('value');
        $verificationId = $this->request->getData('verification_id');

        if (!in_array($type, ['email', 'phone'])) {
            throw new BadRequestException('Invalid OTP type');
        }

        try {
            // Create or get verification record
            if ($verificationId) {
                $verification = $this->Verifications->get($verificationId);
            } else {
                $verification = $this->Verifications->newEntity([
                    'email' => $type === 'email' ? $value : '',
                    'phone' => $type === 'phone' ? $value : ''
                ]);
                if (!$this->Verifications->save($verification)) {
                    return $this->response->withType('application/json')
                        ->withStringBody(json_encode([
                            'success' => false,
                            'message' => 'Failed to create verification record'
                        ]));
                }
            }

            // Update the field value
            if ($type === 'email') {
                $verification->email = $value;
            } else {
                $verification->phone = $value;
            }
            $this->Verifications->save($verification);

            // Check cooldown
            if (!$this->Verifications->canResendOTP($verification->id, $type, 60)) {
                return $this->response->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => false,
                        'message' => 'Please wait before requesting another OTP',
                        'cooldown' => true
                    ]));
            }

            // Check resend limit
            if ($this->Verifications->hasReachedResendLimit($verification->id, $type, 5)) {
                return $this->response->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => false,
                        'message' => 'Maximum OTP requests reached. Please try again later.'
                    ]));
            }

            // Generate and send OTP
            $otp = $this->Verifications->generateOTP();

            $sent = false;
            if ($type === 'email') {
                $sent = $this->twilioService->sendEmail($value, $otp);
            } else {
                $sent = $this->twilioService->sendSMS($value, $otp);
            }

            if ($sent) {
                // Store OTP in database
                $this->Verifications->storeOTP($verification->id, $type, $otp);
                // Update resend count
                if ($type === 'email') {
                    $verification->email_resend_count++;
                } else {
                    $verification->phone_resend_count++;
                }
                $this->Verifications->save($verification);
                // Store verification ID in session
                $this->request->getSession()->write('verification_id', $verification->id);
                return $this->response->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => true,
                        'message' => 'OTP sent successfully',
                        'verification_id' => $verification->id
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

    /* Step 2: OTP Verification :: */
    // ---------------------------------------------------- ::
    public function verify()
    {
        $verificationId = $this->request->getSession()->read('verification_id');

        if (!$verificationId) {
            $this->Flash->error('Please complete registration first.');
            return $this->redirect(['action' => 'register']);
        }

        $verification = $this->Verifications->get($verificationId);

        if ($this->request->is('post')) {
            $emailOtp = $this->request->getData('email_otp');
            $phoneOtp = $this->request->getData('phone_otp');
            $emailValid = $this->Verifications->verifyOTP($verificationId, 'email', $emailOtp);
            $phoneValid = $this->Verifications->verifyOTP($verificationId, 'phone', $phoneOtp);

            if ($emailValid && $phoneValid) {
                $verification->email_verified = true;
                $verification->phone_verified = true;
                $this->Verifications->save($verification);

                $this->Flash->success('Both email and phone verified successfully!');
                return $this->redirect(['action' => 'success']);
            } else {
                $errors = [];
                if (!$emailValid) $errors[] = 'Invalid or expired email OTP';
                if (!$phoneValid) $errors[] = 'Invalid or expired phone OTP';
                $this->Flash->error(implode('. ', $errors));
            }
        }

        $this->set(compact('verification'));
    }

    /* AJAX endpoint to resend OTP during verification :: */
    public function resendOtp()
    {
        $this->request->allowMethod(['post']);
        $this->viewBuilder()->disableAutoLayout();

        $type = $this->request->getData('type');
        $verificationId = $this->request->getSession()->read('verification_id');

        if (!$verificationId) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'Session expired. Please start over.'
                ]));
        }

        try {
            $verification = $this->Verifications->get($verificationId);

            // Check cooldown
            if (!$this->Verifications->canResendOTP($verificationId, $type, 60)) {
                return $this->response->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => false,
                        'message' => 'Please wait before requesting another OTP',
                        'cooldown' => true
                    ]));
            }

            // Check resend limit
            if ($this->Verifications->hasReachedResendLimit($verificationId, $type, 5)) {
                return $this->response->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => false,
                        'message' => 'Maximum resend attempts reached.'
                    ]));
            }

            // Generate and send new OTP
            $otp = $this->Verifications->generateOTP();
            $value = $type === 'email' ? $verification->email : $verification->phone;
            $sent = $type === 'email'
                ? $this->twilioService->sendEmail($value, $otp)
                : $this->twilioService->sendSMS($value, $otp);

            if ($sent) {
                $this->Verifications->storeOTP($verificationId, $type, $otp);

                // Update resend count
                if ($type === 'email') {
                    $verification->email_resend_count++;
                } else {
                    $verification->phone_resend_count++;
                }
                $this->Verifications->save($verification);

                return $this->response->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => true,
                        'message' => 'New OTP sent successfully'
                    ]));
            }

            return $this->response->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'Failed to send OTP'
                ]));
        } catch (\Exception $e) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'An error occurred'
                ]));
        }
    }

    /* Step 3: Success page :: */
    // ---------------------------------------------------- ::
    public function success()
    {
        $verificationId = $this->request->getSession()->read('verification_id');

        if (!$verificationId) {
            $this->Flash->error('Please complete verification first.');
            return $this->redirect(['action' => 'register']);
        }
        $verification = $this->Verifications->get($verificationId);

        if (!$verification->email_verified || !$verification->phone_verified) {
            $this->Flash->error('Please complete verification first.');
            return $this->redirect(['action' => 'verify']);
        }

        // Clear session
        $this->request->getSession()->delete('verification_id');
        $this->set(compact('verification'));
    }
}
