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
