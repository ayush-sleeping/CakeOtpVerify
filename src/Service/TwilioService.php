<?php
declare(strict_types=1);

namespace App\Service;

use Twilio\Rest\Client;
use Cake\Core\Configure;
use Cake\Log\Log;
use Cake\Mailer\Mailer;

class TwilioService
{
    private $client;
    private $fromPhone;
    private $fromEmail;
    private $sendgridApiKey;

    public function __construct()
    {
        // Load Twilio configuration from app_local.php
        $accountSid = Configure::read('Twilio.account_sid');
        $authToken = Configure::read('Twilio.auth_token');
        $this->fromPhone = Configure::read('Twilio.phone_number');
        $this->fromEmail = Configure::read('Twilio.from_email', 'noreply@yourdomain.com');
        $this->sendgridApiKey = Configure::read('Twilio.sendgrid_api_key');

        // Initialize Twilio client if credentials exist
        if ($accountSid && $authToken) {
            $this->client = new Client($accountSid, $authToken);
        }
    }

    /**
     * Send SMS OTP via Twilio
     *
     * @param string $to Phone number
     * @param string $otp OTP code
     * @return bool Success status
     */
    public function sendSMS(string $to, string $otp): bool
    {
        try {
            if (!$this->client) {
                Log::warning('Twilio client not initialized - SMS not sent');
                Log::info("Development Mode - SMS OTP for {$to}: {$otp}");
                return false; // Return false in development to indicate SMS not actually sent
            }

            $message = $this->client->messages->create(
                $to,
                [
                    'from' => $this->fromPhone,
                    'body' => "Your verification code is: {$otp}. Valid for 10 minutes."
                ]
            );

            Log::info("SMS sent successfully to {$to} - SID: {$message->sid}");
            return $message->sid ? true : false;

        } catch (\Exception $e) {
            Log::error('SMS sending failed: ' . $e->getMessage());
            Log::info("Development Mode - SMS OTP for {$to}: {$otp}");
            return false;
        }
    }

    /**
     * Send Email OTP via SendGrid or CakePHP Mailer
     *
     * @param string $to Email address
     * @param string $otp OTP code
     * @return bool Success status
     */
    public function sendEmail(string $to, string $otp): bool
    {
        try {
            // Try SendGrid first if configured
            if ($this->sendgridApiKey) {
                return $this->sendViaSendGrid($to, $otp);
            }

            // Fallback to CakePHP's built-in mailer
            return $this->sendViaCakeMailer($to, $otp);

        } catch (\Exception $e) {
            Log::error('Email sending failed: ' . $e->getMessage());
            Log::info("Development Mode - Email OTP for {$to}: {$otp}");

            // In development, we'll return true so the flow continues
            // In production, you might want to return false
            return Configure::read('debug') ? true : false;
        }
    }

    /**
     * Send email via SendGrid
     */
    private function sendViaSendGrid(string $to, string $otp): bool
    {
        try {
            $email = new \SendGrid\Mail\Mail();
            $email->setFrom($this->fromEmail, "Verification System");
            $email->setSubject("Your Verification Code");
            $email->addTo($to);

            // Plain text version
            $email->addContent(
                "text/plain",
                "Your verification code is: {$otp}\n\nThis code will expire in 10 minutes.\n\nIf you didn't request this code, please ignore this email."
            );

            // HTML version
            $email->addContent(
                "text/html",
                $this->getEmailTemplate($otp)
            );

            $sendgrid = new \SendGrid($this->sendgridApiKey);
            $response = $sendgrid->send($email);

            $status = $response->statusCode();
            $body = $response->body();
            Log::info("SendGrid response status: " . $status);
            Log::info("SendGrid response body: " . $body);

            $success = $status === 202;

            if ($success) {
                Log::info("Email sent via SendGrid to {$to}");
            } else {
                Log::error("SendGrid failed with status: " . $status . ", body: " . $body);
            }

            return $success;

        } catch (\Exception $e) {
            Log::error('SendGrid error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Send email via CakePHP Mailer (fallback)
     */
    private function sendViaCakeMailer(string $to, string $otp): bool
    {
        try {
            $mailer = new Mailer('default');
            $mailer->setFrom([$this->fromEmail => 'Verification System'])
                ->setTo($to)
                ->setSubject('Your Verification Code')
                ->setEmailFormat('html')
                ->deliver($this->getEmailTemplate($otp));

            Log::info("Email sent via CakePHP Mailer to {$to}");
            return true;

        } catch (\Exception $e) {
            Log::error('CakePHP Mailer error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get HTML email template
     */
    private function getEmailTemplate(string $otp): string
    {
        return "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <div style='background-color: #f8f9fa; padding: 20px; border-radius: 10px;'>
                    <h2 style='color: #333;'>Email Verification</h2>
                    <p style='font-size: 16px; color: #555;'>
                        Your verification code is:
                    </p>
                    <div style='background-color: #007bff; color: white; padding: 15px;
                                border-radius: 5px; text-align: center; font-size: 32px;
                                font-weight: bold; letter-spacing: 5px; margin: 20px 0;'>
                        {$otp}
                    </div>
                    <p style='font-size: 14px; color: #666;'>
                        This code will expire in <strong>10 minutes</strong>.
                    </p>
                    <p style='font-size: 12px; color: #999; margin-top: 30px;'>
                        If you didn't request this code, please ignore this email.
                    </p>
                </div>
            </div>
        ";
    }
}
