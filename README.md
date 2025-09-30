# Email & Phone OTP Verification System

A robust dual-channel OTP (One-Time Password) verification system built with CakePHP and Twilio. This system provides secure verification through both email and SMS channels with a strict validation flow.

---

<br>

<br>

## Installation

1. Download [Composer](https://getcomposer.org/doc/00-intro.md) or update `composer self-update`.
2. Run `php composer.phar create-project --prefer-dist cakephp/app [app_name]`.

If Composer is installed globally, run

```bash
composer create-project --prefer-dist cakephp/app
```

In case you want to use a custom app dir name (e.g. `/myapp/`):

```bash
composer create-project --prefer-dist cakephp/app myapp
```

You can now either use your machine's webserver to view the default home page, or start
up the built-in webserver with:

```bash
bin/cake server -p 8765
```

Then visit `http://localhost:8765` to see the welcome page.

---

<br>

<br>


[![Output Screen Shot](outputscreenshots/output%201.png)](https://example.com)
[![Output Screen Shot](outputscreenshots/output%202.png)](https://example.com)
[![Output Screen Shot](outputscreenshots/output%203.png)](https://example.com)
[![Output Screen Shot](outputscreenshots/output%204.png)](https://example.com)
[![Output Screen Shot](outputscreenshots/output%205.png)](https://example.com)

<br>

<br>

## Table of Contents

- [Features](#features)
- [Tech Stack](#tech-stack)
- [System Flow](#system-flow)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [API Endpoints](#api-endpoints)
- [Validation Rules](#validation-rules)
- [Error Handling](#error-handling)
- [Testing](#testing)
- [Troubleshooting](#troubleshooting)
- [Lets Understand the codebase](#lets-understand-the-codebase)

---

<br>

<br>

## Features

- ✅ **Dual-Channel Verification**: Email + Phone Number OTP
- ✅ **Strict Flow Control**: Both OTPs must be sent before proceeding
- ✅ **Real-time Validation**: Instant input validation with visual feedback
- ✅ **Session Management**: Secure OTP storage with 10-minute expiry
- ✅ **Smart Error Messages**: Specific feedback for each validation failure
- ✅ **International Phone Support**: Accepts phone numbers with country codes
- ✅ **Responsive UI**: Mobile-friendly interface with Bootstrap 5
- ✅ **AJAX-Powered**: Seamless user experience without page reloads
- ✅ **Security**: CSRF protection and session-based verification

---

<br>

<br>

## Tech Stack

- **Backend**: CakePHP 4.x/5.x (PHP)
- **Frontend**: HTML5, JavaScript (jQuery), Bootstrap 5
- **SMS Service**: Twilio
- **Email Service**: Twilio SendGrid / CakePHP Mailer
- **Notifications**: Toastr.js

---

<br>

<br>

## System Flow

### **Step 1: Registration (`/register`)**

1. User enters **email address**
   - Real-time validation
   - "Get OTP" button appears when valid
2. User clicks **"Get OTP"** for email
   - 6-digit OTP generated
   - OTP sent via email
   - Success status displayed
3. User enters **phone number** (with country code)
   - Real-time validation
   - "Get OTP" button appears when valid
4. User clicks **"Get OTP"** for phone
   - 6-digit OTP generated
   - OTP sent via SMS
   - Success status displayed
5. **"Proceed"** button enables only when **BOTH** OTPs are sent
6. User clicks "Proceed" → Redirected to verification page

### **Step 2: Verification (`/verify`)**

1. Displays user's email and phone (readonly)
2. Shows two OTP input fields:
   - Email OTP (6 digits)
   - Phone OTP (6 digits)
3. User enters both OTPs
4. User clicks **"Verify & Continue"**
5. System validates both OTPs:
   - ✅ **Both correct** → Redirect to success page
   - ❌ **One/both incorrect** → Show specific error:
     - Which OTP is wrong
     - Option to retry
     - Option to go back to `/register`
6. **10-minute expiry timer** displayed

### **Step 3: Success (`/success`)**

1. Displays success message with checkmark animation
2. Shows verified email and phone number
3. Displays verification timestamp
4. Options:
   - Start new verification
   - Return to home
5. Session automatically cleared

---

<br>

<br>

## Installation

### Prerequisites

- PHP 7.4 or higher
- Composer
- CakePHP 4.x/5.x
- Twilio Account (for SMS)
- SendGrid Account (optional, for email)

### Setup Steps

1. **Clone the repository**
   ```bash
   git clone <your-repo-url>
   cd <project-folder>
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Install Twilio SDK**
   ```bash
   composer require twilio/sdk
   ```

4. **Configure database** (if needed)
   ```bash
   cp config/app_local.example.php config/app_local.php
   ```

5. **Set up environment variables** (see Configuration below)

---

<br>

<br>

## Configuration

### 1. Twilio Configuration

Edit `config/app_local.php` and add your Twilio credentials:

```php
return [
    // ... other config

    'Twilio' => [
        // Twilio Account Credentials
        'account_sid' => env('TWILIO_ACCOUNT_SID', 'your_account_sid_here'),
        'auth_token' => env('TWILIO_AUTH_TOKEN', 'your_auth_token_here'),

        // Twilio Phone Number (for SMS)
        'phone_number' => env('TWILIO_PHONE_NUMBER', '+1234567890'),

        // SendGrid API Key (for Email)
        'sendgrid_api_key' => env('SENDGRID_API_KEY', 'your_sendgrid_key_here'),

        // From Email Address
        'from_email' => env('TWILIO_FROM_EMAIL', 'noreply@yourdomain.com')
    ]
];
```

### 2. Environment Variables (Optional)

Create `.env` file in project root:

```env
TWILIO_ACCOUNT_SID=your_twilio_account_sid
TWILIO_AUTH_TOKEN=your_twilio_auth_token
TWILIO_PHONE_NUMBER=+1234567890
SENDGRID_API_KEY=your_sendgrid_api_key
TWILIO_FROM_EMAIL=noreply@yourdomain.com
```

### 3. Routes Configuration

Add routes to `config/routes.php`:

```php
// OTP Verification Routes
$builder->connect('/register',
    ['controller' => 'Verifications', 'action' => 'register']);

$builder->connect('/verifications/send-otp',
    ['controller' => 'Verifications', 'action' => 'sendOtp'])
    ->setMethods(['POST']);

$builder->connect('/verifications/send-phone-otp',
    ['controller' => 'Verifications', 'action' => 'sendPhoneOtp'])
    ->setMethods(['POST']);

$builder->connect('/verify',
    ['controller' => 'Verifications', 'action' => 'verify']);

$builder->connect('/verifications/verify-otp',
    ['controller' => 'Verifications', 'action' => 'verifyOtp'])
    ->setMethods(['POST']);

$builder->connect('/success',
    ['controller' => 'Verifications', 'action' => 'success']);
```

---

<br>

<br>

## Usage

### Starting the Application

```bash
bin/cake server
```

Navigate to: `http://localhost:8765/register`

### User Journey

1. **Enter email** → Click "Get OTP" → Check email inbox
2. **Enter phone** (with country code, e.g., +1234567890) → Click "Get OTP" → Check SMS
3. **Click "Proceed"** (available only after both OTPs sent)
4. **Enter both OTPs** on verification page
5. **Click "Verify & Continue"**
6. **View success page** with verified details

---

<br>

<br>

## API Endpoints

### 1. Send Email OTP

**Endpoint**: `POST /verifications/send-otp`

**Request Body**:
```json
{
  "email": "user@example.com"
}
```

**Response**:
```json
{
  "success": true,
  "message": "OTP sent successfully to your email"
}
```

---

<br>

<br>

### 2. Send Phone OTP

**Endpoint**: `POST /verifications/send-phone-otp`

**Request Body**:
```json
{
  "phone": "+1234567890"
}
```

**Response**:
```json
{
  "success": true,
  "message": "OTP sent successfully to your phone"
}
```

---

<br>

<br>

### 3. Verify Both OTPs

**Endpoint**: `POST /verifications/verify-otp`

**Request Body**:
```json
{
  "email_otp": "123456",
  "phone_otp": "654321"
}
```

**Success Response**:
```json
{
  "success": true,
  "message": "Email and phone verified successfully!"
}
```

**Error Response**:
```json
{
  "success": false,
  "message": "Email OTP is incorrect. Please try again or go back to /register to start over."
}
```

---

<br>

<br>

## Validation Rules

### Email Validation
- **Format**: Standard email format
- **Regex**: `/^[^\s@]+@[^\s@]+\.[^\s@]+$/`
- **Example**: `user@example.com`

### Phone Validation
- **Format**: International format with country code
- **Regex**: `/^\+[1-9]\d{9,14}$/`
- **Examples**:
  - US: `+12125551234`
  - India: `+919876543210`
  - UK: `+447700900123`

### OTP Validation
- **Length**: Exactly 6 digits
- **Format**: Numeric only
- **Expiry**: 10 minutes from generation
- **Regex**: `/^\d{6}$/`

---

<br>

<br>

## Security Features

### Session Management
```php
// Email OTP Storage
'email' => 'user@example.com'
'email_otp' => '123456'
'email_otp_generated_at' => timestamp
'email_verified' => true

// Phone OTP Storage
'phone' => '+1234567890'
'phone_otp' => '654321'
'phone_otp_generated_at' => timestamp
'phone_verified' => true
```

### Security Measures
- ✅ CSRF Token validation on all POST requests
- ✅ Session-based OTP storage (not in database)
- ✅ 10-minute OTP expiry
- ✅ OTP cleared after successful verification
- ✅ Session cleared after success page
- ✅ Cannot skip steps (enforced redirects)
- ✅ Cannot access verify/success pages without valid session

---

<br>

<br>

## Error Handling

### Common Error Messages

| Scenario | Error Message | Action |
|----------|--------------|--------|
| Invalid email format | "Please enter a valid email" | Fix email format |
| Invalid phone format | "Please enter a valid phone number with country code" | Add country code |
| Email OTP not sent | "Please send OTP first" | Click "Get OTP" for email |
| Phone OTP not sent | "Please send OTP first" | Click "Get OTP" for phone |
| Session expired | "Session expired. Please go back to /register" | Restart process |
| OTP expired | "OTP has expired. Please request a new one" | Go back to /register |
| Wrong email OTP | "Email OTP is incorrect. Please try again..." | Re-enter or restart |
| Wrong phone OTP | "Phone OTP is incorrect. Please try again..." | Re-enter or restart |
| Both OTPs wrong | "Both email and phone OTPs are incorrect..." | Re-enter or restart |
| SMS send failed | "Failed to send SMS. Please check your phone number..." | Verify phone format |

---

<br>

<br>

## Testing

### Manual Testing Checklist

- [ ] Email validation works (valid/invalid formats)
- [ ] Phone validation works (with/without country code)
- [ ] "Get OTP" buttons appear only when input is valid
- [ ] Email OTP is sent successfully
- [ ] Phone OTP/SMS is sent successfully
- [ ] "Proceed" button only enables when BOTH OTPs sent
- [ ] Cannot proceed without both OTPs
- [ ] Verify page displays both email and phone
- [ ] Can enter OTPs in both fields
- [ ] Verification fails if email OTP is wrong
- [ ] Verification fails if phone OTP is wrong
- [ ] Verification fails if both OTPs are wrong
- [ ] Verification succeeds only when both OTPs are correct
- [ ] OTP expires after 10 minutes
- [ ] Timer counts down correctly
- [ ] Success page shows both verified email and phone
- [ ] Session clears after success
- [ ] Cannot access `/verify` without sending OTPs
- [ ] Cannot access `/success` without verifying OTPs
- [ ] CSRF protection works

### Test Credentials

For development/testing, check your logs at `logs/error.log` and `logs/debug.log` where OTPs are logged when Twilio is not configured.

---

<br>

<br>

## Troubleshooting

### SMS Not Sending

**Problem**: SMS OTP not received

**Solutions**:
1. Check Twilio credentials in `config/app_local.php`
2. Verify Twilio account has SMS capabilities
3. Check Twilio phone number is SMS-enabled
4. Ensure phone number includes country code (`+`)
5. Check Twilio Console logs: https://console.twilio.com/logs
6. Verify phone number is not in "Do Not Disturb" list
7. Check CakePHP logs: `logs/error.log`

---

<br>

<br>

### Email Not Sending

**Problem**: Email OTP not received

**Solutions**:
1. Check SendGrid API key in config
2. Verify sender email is authenticated in SendGrid
3. Check spam/junk folder
4. Look at SendGrid activity logs
5. Check CakePHP logs: `logs/error.log`
6. Try fallback CakePHP mailer (auto-attempts if SendGrid fails)

---

<br>

<br>

### Phone Validation Failing

**Problem**: "Invalid phone number" error

**Solutions**:
1. Ensure phone starts with `+`
2. Include country code (e.g., `+1` for US, `+91` for India)
3. Remove spaces, dashes, or parentheses
4. Total length: 11-16 characters (+ and 10-15 digits)

**Valid Examples**:
- ✅ `+12125551234` (US)
- ✅ `+919876543210` (India)
- ✅ `+447700900123` (UK)

**Invalid Examples**:
- ❌ `2125551234` (missing +)
- ❌ `+1 (212) 555-1234` (contains spaces/symbols)
- ❌ `1234567890` (missing + and country code)

---

<br>

<br>

### Session Issues

**Problem**: "Session expired" errors

**Solutions**:
1. Check session configuration in `config/app.php`
2. Ensure cookies are enabled in browser
3. Check session timeout settings
4. Clear browser cache and cookies
5. Restart the application

---

<br>

<br>

### OTP Expired

**Problem**: "OTP has expired" message

**Solution**:
- OTPs are valid for 10 minutes only
- Go back to `/register` and request new OTPs
- Don't delay entering OTPs on verify page

---

<br>

<br>

## Session Variables Reference

```php
// Email Verification
$session->write('email', 'user@example.com');
$session->write('email_otp', '123456');
$session->write('email_otp_generated_at', time());
$session->write('email_verified', true);

// Phone Verification
$session->write('phone', '+1234567890');
$session->write('phone_otp', '654321');
$session->write('phone_otp_generated_at', time());
$session->write('phone_verified', true);
```

---

<br>

<br>

## Lets Understand the codebase

```
┌─────────────────────────────────────────────────────────────┐
│                    /register PAGE                            │
│                                                              │
│  Email Input → Validation → Get OTP Button → AJAX           │
│      ↓                                           ↓           │
│  Phone Input → Validation → Get OTP Button → AJAX           │
│                                                 ↓            │
│              Backend: Generate OTP                           │
│              Session: Store OTP + timestamp                  │
│              Service: Send Email/SMS                         │
│                      ↓                                       │
│              Proceed Button Enabled                          │
│                      ↓                                       │
└──────────────────────┼──────────────────────────────────────┘
                       ↓
┌──────────────────────┼──────────────────────────────────────┐
│                    /verify PAGE                              │
│                                                              │
│  Display: Email (readonly), Phone (readonly)                 │
│  Input: Email OTP (6 digits)                                 │
│  Input: Phone OTP (6 digits)                                 │
│         ↓                                                    │
│  User enters OTPs → Verify Button → AJAX                     │
│                                        ↓                     │
│              Backend: Compare with session                   │
│              Check: Expiry (10 minutes)                      │
│              Valid? Mark as verified                         │
│                      ↓                                       │
└──────────────────────┼──────────────────────────────────────┘
                       ↓
┌──────────────────────┼──────────────────────────────────────┐
│                   /success PAGE                              │
│                                                              │
│  Display: Checkmark, Email, Phone, Timestamp                 │
│  Clear: All session data                                     │
│  Links: Start New / Back to Home                             │
└─────────────────────────────────────────────────────────────┘
```

<Details>
<summary>app_local.php</summary>

```php
    'Twilio' => [
        'account_sid' => env('TWILIO_ACCOUNT_SID', 'ACXXXXXXXXXXXXXXXX'),
        'auth_token' => env('TWILIO_AUTH_TOKEN', 'your_auth_token_here'),
        'phone_number' => env('TWILIO_PHONE_NUMBER', '+1234567890'), // Twilio phone number for sending SMS
        'from_email' => env('TWILIO_FROM_EMAIL', ''), // if using email as sender
        'sendgrid_api_key' => env('SENDGRID_API_KEY', ''), // Optional, if using SendGrid for email
    ],
```
</Details>


<Details>
<summary>routes.php</summary>

```php
    // Home
    $builder->connect('/', ['controller' => 'Pages', 'action' => 'display', 'home']);

    // Register page
    $builder->connect('/register', ['controller' => 'Verifications', 'action' => 'register']);
    $builder->connect('/verifications/send-otp', ['controller' => 'Verifications', 'action' => 'sendOtp'], ['_method' => 'POST']);
    $builder->connect('/verifications/send-phone-otp', ['controller' => 'Verifications', 'action' => 'sendPhoneOtp'], ['_method' => 'POST']);

    // Verify page
    $builder->connect('/verify', ['controller' => 'Verifications', 'action' => 'verify']);
    $builder->connect('/verifications/verify-otp', ['controller' => 'Verifications', 'action' => 'verifyOtp'], ['_method' => 'POST']);

    // Success page
    $builder->connect('/success', ['controller' => 'Verifications', 'action' => 'success']);
```
</Details>



<Details>
<summary>default.php</summary>

```html
<?php
$cakeDescription = 'CakePHP: the rapid development php framework';
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="csrf-token" content="<?= $this->request->getAttribute('csrfToken') ?>">
</head>
<body>
    <!-- jquery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

</body>
</html>

```
</Details>


<Details>
<summary>TwilioService.php</summary>

```
composer require sendgrid/sendgrid
composer require twilio/sdk
```

```php
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
    private $sendgridApiKey;

    public function __construct()
    {
        $accountSid = Configure::read('Twilio.account_sid');
        $authToken = Configure::read('Twilio.auth_token');
        $this->fromPhone = Configure::read('Twilio.phone_number');
        $this->fromEmail = Configure::read('Twilio.from_email');
        $this->sendgridApiKey = Configure::read('Twilio.sendgrid_api_key');

        if ($accountSid && $authToken) {
            $this->client = new Client($accountSid, $authToken);
        }
    }

    public function sendSMS(string $to, string $otp): bool
    {
        try {
            if (!$this->client) {
                Log::info("SMS OTP for {$to}: {$otp}");
                return false;
            }

            $message = $this->client->messages->create($to, [
                'from' => $this->fromPhone,
                'body' => "Your verification code is: {$otp}. Valid for 10 minutes."
            ]);

            return $message->sid ? true : false;

        } catch (\Exception $e) {
            Log::error('SMS failed: ' . $e->getMessage());
            Log::info("SMS OTP for {$to}: {$otp}");
            return false;
        }
    }

    public function sendEmail(string $to, string $otp): bool
    {
        try {
            if ($this->sendgridApiKey) {
                return $this->sendViaSendGrid($to, $otp);
            }

            // Fallback: just log (or use CakePHP Mailer)
            Log::info("Email OTP for {$to}: {$otp}");
            return true;

        } catch (\Exception $e) {
            Log::error('Email failed: ' . $e->getMessage());
            Log::info("Email OTP for {$to}: {$otp}");
            return Configure::read('debug') ? true : false;
        }
    }

    private function sendViaSendGrid(string $to, string $otp): bool
    {
        try {
            $email = new \SendGrid\Mail\Mail();
            $email->setFrom($this->fromEmail, "Verification System");
            $email->setSubject("Your Verification Code");
            $email->addTo($to);
            $email->addContent("text/plain", "Your verification code is: {$otp}\n\nValid for 10 minutes.");
            $email->addContent("text/html", $this->getEmailTemplate($otp));

            $sendgrid = new \SendGrid($this->sendgridApiKey);
            $response = $sendgrid->send($email);

            return $response->statusCode() === 202;

        } catch (\Exception $e) {
            Log::error('SendGrid error: ' . $e->getMessage());
            throw $e;
        }
    }

    private function getEmailTemplate(string $otp): string
    {
        return "
            <div>
                <h2>Email Verification</h2>
                <p>Your verification code is: <strong>{$otp}</strong></p>
                <p>This code will expire in 10 minutes.</p>
            </div>
        ";
    }
}

```
</Details>


<Details>
<summary>VerificationController.php</summary>

```
bin/cake bake controller Verifications
```

```php
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

    // Render registration page
    public function register()
    {
        // Just render the view
    }

    // Send email OTP
    public function sendOtp()
    {
        $this->request->allowMethod(['post']);
        $this->viewBuilder()->disableAutoLayout();

        try {
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

            // Store in session
            $session = $this->request->getSession();
            $session->write('email_otp', $otp);
            $session->write('email', $email);
            $session->write('email_otp_generated_at', time());

            // Send OTP
            $sent = $this->twilioService->sendEmail($email, $otp);

            if ($sent) {
                return $this->response->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => true,
                        'message' => 'OTP sent to your email'
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
                    'message' => 'Error: ' . $e->getMessage()
                ]));
        }
    }

    // Send phone OTP
    public function sendPhoneOtp()
    {
        $this->request->allowMethod(['post']);
        $this->viewBuilder()->disableAutoLayout();

        try {
            $phone = $this->request->getData('phone');

            // Validate phone
            if (empty($phone) || !preg_match('/^\+[1-9]\d{9,14}$/', $phone)) {
                return $this->response->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => false,
                        'message' => 'Please provide valid phone with country code'
                    ]));
            }

            // Generate 6-digit OTP
            $otp = sprintf('%06d', mt_rand(0, 999999));

            // Store in session
            $session = $this->request->getSession();
            $session->write('phone_otp', $otp);
            $session->write('phone', $phone);
            $session->write('phone_otp_generated_at', time());

            // Send OTP
            $sent = $this->twilioService->sendSMS($phone, $otp);

            if ($sent) {
                return $this->response->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => true,
                        'message' => 'OTP sent to your phone'
                    ]));
            }

            return $this->response->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'Failed to send SMS'
                ]));

        } catch (\Exception $e) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ]));
        }
    }

    // Render verify page
    public function verify()
    {
        $session = $this->request->getSession();

        // Check if OTPs were sent
        if (!$session->check('email_otp') || !$session->check('email')) {
            $this->Flash->error('Please request email OTP first');
            return $this->redirect(['action' => 'register']);
        }

        if (!$session->check('phone_otp') || !$session->check('phone')) {
            $this->Flash->error('Please request phone OTP first');
            return $this->redirect(['action' => 'register']);
        }

        // Pass data to view
        $email = $session->read('email');
        $phone = $session->read('phone');
        $this->set(compact('email', 'phone'));
    }

    // Verify OTPs
    public function verifyOtp()
    {
        $this->request->allowMethod(['post']);
        $this->viewBuilder()->disableAutoLayout();

        try {
            $session = $this->request->getSession();

            $enteredEmailOtp = $this->request->getData('email_otp');
            $enteredPhoneOtp = $this->request->getData('phone_otp');

            // Validate format
            if (empty($enteredEmailOtp) || !preg_match('/^\d{6}$/', $enteredEmailOtp)) {
                return $this->response->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => false,
                        'message' => 'Please enter valid 6-digit email OTP'
                    ]));
            }

            if (empty($enteredPhoneOtp) || !preg_match('/^\d{6}$/', $enteredPhoneOtp)) {
                return $this->response->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => false,
                        'message' => 'Please enter valid 6-digit phone OTP'
                    ]));
            }

            // Check session
            if (!$session->check('email_otp') || !$session->check('phone_otp')) {
                return $this->response->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => false,
                        'message' => 'Session expired. Please go back to /register'
                    ]));
            }

            // Get stored OTPs
            $storedEmailOtp = $session->read('email_otp');
            $storedPhoneOtp = $session->read('phone_otp');
            $emailTime = $session->read('email_otp_generated_at');
            $phoneTime = $session->read('phone_otp_generated_at');

            // Check expiry (10 minutes = 600 seconds)
            $currentTime = time();
            $emailExpired = ($currentTime - $emailTime) > 600;
            $phoneExpired = ($currentTime - $phoneTime) > 600;

            if ($emailExpired || $phoneExpired) {
                return $this->response->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => false,
                        'message' => 'OTP expired. Please go back to /register'
                    ]));
            }

            // Verify both OTPs
            $emailValid = ($enteredEmailOtp === $storedEmailOtp);
            $phoneValid = ($enteredPhoneOtp === $storedPhoneOtp);

            if ($emailValid && $phoneValid) {
                $session->write('email_verified', true);
                $session->write('phone_verified', true);

                return $this->response->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => true,
                        'message' => 'Verification successful!'
                    ]));
            } else {
                // Specific error message
                if (!$emailValid && !$phoneValid) {
                    $msg = 'Both email and phone OTPs are incorrect';
                } elseif (!$emailValid) {
                    $msg = 'Email OTP is incorrect';
                } else {
                    $msg = 'Phone OTP is incorrect';
                }

                return $this->response->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => false,
                        'message' => $msg . '. Please try again or go back to /register'
                    ]));
            }

        } catch (\Exception $e) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ]));
        }
    }

    // Success page
    public function success()
    {
        $session = $this->request->getSession();

        $email = $session->read('email');
        $phone = $session->read('phone');

        // Create verification object
        $verification = (object)[
            'email' => $email,
            'phone' => $phone,
            'verified_at' => new \DateTime()
        ];

        // Clear session
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

```
</Details>


<Details>
<summary>register.php</summary>

```php
<h3>Register</h3>
<form id="mainForm">
    <!-- Email Section -->
    <div>
        <label for="email">Email Address</label>
        <div>
            <input type="email" id="email" name="email" placeholder="Enter your email" required>
            <button type="button" id="emailOtpBtn" style="display: none;">Get OTP</button>
        </div>
        <small id="emailStatus"></small>
    </div>

    <!-- Phone Section -->
    <div>
        <label for="phone">Phone Number</label>
        <div>
            <input type="tel" id="phone" name="phone" placeholder="Enter phone (e.g., +1234567890)" required>
            <button type="button" id="phoneOtpBtn" style="display: none;">Get OTP</button>
        </div>
        <small id="phoneStatus"></small>
        <div>Include country code (e.g., +1 for US, +91 for India)</div>
    </div>

    <!-- Proceed Button -->
    <button type="submit" id="proceedBtn" disabled>
        Proceed to Verification
    </button>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        let emailOtpSent = false;
        let phoneOtpSent = false;

        const mainForm = document.getElementById('mainForm');

        const emailInput = document.getElementById('email');
        const emailOtpBtn = document.getElementById('emailOtpBtn');
        const emailStatus = document.getElementById('emailStatus');

        const phoneInput = document.getElementById('phone');
        const phoneOtpBtn = document.getElementById('phoneOtpBtn');
        const phoneStatus = document.getElementById('phoneStatus');

        const proceedBtn = document.getElementById('proceedBtn');

        // Email validation
        emailInput.addEventListener('input', function() {
            const email = this.value.trim();
            if (validateEmail(email)) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
                if (!emailOtpSent) emailOtpBtn.style.display = 'block';
            } else {
                this.classList.remove('is-valid');
                this.classList.add('is-invalid');
                emailOtpBtn.style.display = 'none';
            }
        });

        function validateEmail(email) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        }

        // Phone validation
        phoneInput.addEventListener('input', function() {
            const phone = this.value.trim();
            if (validatePhone(phone)) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
                if (!phoneOtpSent) phoneOtpBtn.style.display = 'block';
            } else {
                this.classList.remove('is-valid');
                this.classList.add('is-invalid');
                phoneOtpBtn.style.display = 'none';
            }
        });

        function validatePhone(phone) {
            return /^\+[1-9]\d{9,14}$/.test(phone);
        }

        // Send Email OTP
        emailOtpBtn.addEventListener('click', function() {
            const email = emailInput.value.trim();
            if (!validateEmail(email)) {
                toastr.error('Please enter a valid email');
                return;
            }

            emailOtpBtn.disabled = true;
            emailOtpBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Sending...';

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
                        emailOtpSent = true;
                        emailStatus.textContent = '✓ OTP sent to your email';
                        emailStatus.className = 'text-success';
                        emailOtpBtn.style.display = 'none';
                        updateProceedButton();
                        toastr.success(data.message);
                    } else {
                        emailOtpBtn.disabled = false;
                        emailOtpBtn.textContent = 'Get OTP';
                        toastr.error(data.message);
                    }
                },
                error: function() {
                    emailOtpBtn.disabled = false;
                    emailOtpBtn.textContent = 'Get OTP';
                    toastr.error('An error occurred');
                }
            });
        });

        // Send Phone OTP
        phoneOtpBtn.addEventListener('click', function() {
            const phone = phoneInput.value.trim();
            if (!validatePhone(phone)) {
                toastr.error('Please enter valid phone with country code');
                return;
            }

            phoneOtpBtn.disabled = true;
            phoneOtpBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Sending...';

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
                        phoneOtpSent = true;
                        phoneStatus.textContent = '✓ OTP sent to your phone';
                        phoneStatus.className = 'text-success';
                        phoneOtpBtn.style.display = 'none';
                        updateProceedButton();
                        toastr.success(data.message);
                    } else {
                        phoneOtpBtn.disabled = false;
                        phoneOtpBtn.textContent = 'Get OTP';
                        toastr.error(data.message);
                    }
                },
                error: function() {
                    phoneOtpBtn.disabled = false;
                    phoneOtpBtn.textContent = 'Get OTP';
                    toastr.error('An error occurred');
                }
            });
        });

        function updateProceedButton() {
            if (emailOtpSent && phoneOtpSent) {
                proceedBtn.disabled = false;
                proceedBtn.classList.remove('btn-secondary');
                proceedBtn.classList.add('btn-success');
                proceedBtn.textContent = '✓ Proceed to Verification';
            }
        }

        mainForm.addEventListener('submit', function(e) {
            e.preventDefault();
            if (!emailOtpSent || !phoneOtpSent) {
                toastr.error('Please send OTP to both email and phone');
                return;
            }
            proceedBtn.disabled = true;
            proceedBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Proceeding...';
            window.location.href = '/verify';
        });
    });
</script>

```
</Details>


<Details>
<summary>verify.php</summary>

```php
<h3>Verify Your Details</h3>

<form id="verifyOtpForm" action="/verifications/verify-otp">
    <?= $this->Form->secure() ?>

    <!-- Email OTP -->
    <div>
        <label for="email_otp">Email Verification</label>
        <small><?= h($email) ?></small>
        <input type="text" id="email_otp" name="email_otp" placeholder="000000" maxlength="6" inputmode="numeric" required>
        <div>Enter 6-digit code sent to your email</div>
    </div>

    <!-- Phone OTP -->
    <div>
        <label for="phone_otp">Phone Verification</label>
        <small><?= h($phone) ?></small>
        <input type="text" id="phone_otp" name="phone_otp" placeholder="000000" maxlength="6" inputmode="numeric" required>
        <div>Enter 6-digit code sent to your phone</div>
    </div>

    <button type="submit" id="verifyBtn">
        Verify and Continue
    </button>
</form>

<div>
    <small>
        Didn't receive codes?
        <a href="/register">Go back to Register</a>
    </small>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const emailOtpInput = document.getElementById('email_otp');
        const phoneOtpInput = document.getElementById('phone_otp');
        const verifyBtn = document.getElementById('verifyBtn');
        const verifyForm = document.getElementById('verifyOtpForm');

        emailOtpInput.focus();

        // Only allow numbers
        emailOtpInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value.length === 6) phoneOtpInput.focus();
        });

        phoneOtpInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        // AJAX submit
        $(document).ready(function() {
            $('#verifyOtpForm').submit(function(e) {
                e.preventDefault();

                const emailOtp = $('#email_otp').val().trim();
                const phoneOtp = $('#phone_otp').val().trim();

                if (emailOtp.length !== 6 || !/^\d{6}$/.test(emailOtp)) {
                    toastr.error('Please enter valid 6-digit email OTP');
                    $('#email_otp').focus();
                    return;
                }

                if (phoneOtp.length !== 6 || !/^\d{6}$/.test(phoneOtp)) {
                    toastr.error('Please enter valid 6-digit phone OTP');
                    $('#phone_otp').focus();
                    return;
                }

                $('#verifyBtn').prop('disabled', true);
                $('#verifyBtn').html('<span class="spinner-border spinner-border-sm"></span> Verifying...');

                $.ajax({
                    type: "POST",
                    url: $(this).attr('action'),
                    data: new FormData(this),
                    contentType: false,
                    cache: false,
                    processData: false,
                    headers: {
                        'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(data) {
                        if (data.success) {
                            toastr.success(data.message);
                            setTimeout(function() {
                                window.location.href = '/success';
                            }, 1500);
                        } else {
                            $('#verifyBtn').prop('disabled', false);
                            $('#verifyBtn').text('Verify & Continue');
                            $('#email_otp').val('');
                            $('#phone_otp').val('');
                            $('#email_otp').focus();
                            toastr.error(data.message);
                        }
                    },
                    error: function() {
                        $('#verifyBtn').prop('disabled', false);
                        $('#verifyBtn').text('Verify & Continue');
                        toastr.error('An error occurred');
                    }
                });
            });
        });
    });
</script>

```
</Details>


<Details>
<summary>success.php</summary>

```php
<h3>Verification Successful!</h3>
<div>
    <p><strong>Email:</strong> <?= h($verification->email) ?></p>
    <p><strong>Phone:</strong> <?= h($verification->phone) ?></p>
    <p>
        <strong>Verified at:</strong>
        <?= $verification->verified_at->format('Y-m-d H:i:s') ?>
    </p>
</div>
<div>
    <a href="/register">Start New Verification</a>
    <a href="/">Back to Home</a>
</div>

```
</Details>


---

<br>

<br>

## Development Notes

### In Development Mode

When Twilio credentials are not configured, the system will:
- Log OTPs to `logs/error.log` and `logs/debug.log`
- Return success for email (so you can test the flow)
- Return false for SMS (indicating no real SMS sent)
- Allow you to copy OTP from logs for testing

### In Production Mode

- Ensure all Twilio credentials are properly configured
- OTPs will be sent via actual SMS and email
- Remove debug logging for security
- Enable proper error tracking

---

<br>

<br>

## Acknowledgments

- [CakePHP](https://cakephp.org/) - The PHP Framework
- [Twilio](https://www.twilio.com/) - SMS and Email Services
- [Bootstrap](https://getbootstrap.com/) - UI Framework
- [Toastr](https://github.com/CodeSeven/toastr) - Notification Library
