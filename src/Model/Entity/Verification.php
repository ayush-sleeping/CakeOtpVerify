<?php
declare(strict_types=1);

namespace App\Model\Entity;
use Cake\ORM\Entity;

class Verification extends Entity
{
    protected array $_accessible = [
        'email' => true,
        'phone' => true,
        'email_otp_hash' => true,
        'phone_otp_hash' => true,
        'email_otp_expires' => true,
        'phone_otp_expires' => true,
        'email_verified' => true,
        'phone_verified' => true,
        'email_otp_sent_at' => true,
        'phone_otp_sent_at' => true,
        'email_resend_count' => true,
        'phone_resend_count' => true,
        'created' => true,
        'modified' => true,
    ];

    /* Fields that are excluded from JSON versions of the entity : */
    protected array $_hidden = [
        'email_otp_hash',
        'phone_otp_hash',
    ];
}
