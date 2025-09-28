<?php
declare(strict_types=1);

namespace App\Service;

use Twilio\Rest\Client;
use Cake\Core\Configure;
use Cake\Log\Log;

class TwilioService
{
    private $client;
    private $fromPhone;
    private $fromEmail;

    public function __construct()
    {
        // Load Twilio configuration from app_local.php
        $accountSid = Configure::read('Twilio.account_sid');
        $authToken = Configure::read('Twilio.auth_token');
        $this->fromPhone = Configure::read('Twilio.phone_number');
        $this->fromEmail = Configure::read('Twilio.from_email', 'noreply@example.com');

        if ($accountSid && $authToken) {
            $this->client = new Client($accountSid, $authToken);
        }
    }

    /* Send SMS OTP :: */
    public function sendSMS($to, $otp)
    {
        try {
            if (!$this->client) {
                Log::error('Twilio client not initialized');
                // For development, log the OTP instead of sending
                Log::info("SMS OTP for {$to}: {$otp}");
                return true;
            }

            $message = $this->client->messages->create(
                $to,
                [
                    'from' => $this->fromPhone,
                    'body' => "Your verification code is: {$otp}. It will expire in 10 minutes."
                ]
            );

            return $message->sid ? true : false;
        } catch (\Exception $e) {
            Log::error('SMS sending failed: ' . $e->getMessage());
            // For development, still return true but log the error
            Log::info("SMS OTP for {$to}: {$otp}");
            return true;
        }
    }

    /* Send Email OTP using Twilio SendGrid :: */
    public function sendEmail($to, $otp)
    {
        try {
            // If SendGrid is not configured, use basic email for development
            $sendgridApiKey = Configure::read('Twilio.sendgrid_api_key');

            if (!$sendgridApiKey) {
                // For development, log the OTP
                Log::info("Email OTP for {$to}: {$otp}");

                // You can also use CakePHP's built-in email if configured
                $email = new \Cake\Mailer\Mailer('default');
                $email->setFrom([$this->fromEmail => 'Verification System'])
                    ->setTo($to)
                    ->setSubject('Your Verification Code')
                    ->deliver("Your verification code is: {$otp}\n\nIt will expire in 10 minutes.");

                return true;
            }

            // SendGrid implementation
            $email = new \SendGrid\Mail\Mail();
            $email->setFrom($this->fromEmail, "Verification System");
            $email->setSubject("Your Verification Code");
            $email->addTo($to);
            $email->addContent(
                "text/plain",
                "Your verification code is: {$otp}\n\nIt will expire in 10 minutes."
            );
            $email->addContent(
                "text/html",
                "<p>Your verification code is: <strong>{$otp}</strong></p><p>It will expire in 10 minutes.</p>"
            );

            $sendgrid = new \SendGrid($sendgridApiKey);
            $response = $sendgrid->send($email);

            return $response->statusCode() === 202;
        } catch (\Exception $e) {
            Log::error('Email sending failed: ' . $e->getMessage());
            // For development, still return true but log the OTP
            Log::info("Email OTP for {$to}: {$otp}");
            return true;
        }
    }
}
