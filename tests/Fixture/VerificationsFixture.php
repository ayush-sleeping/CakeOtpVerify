<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * VerificationsFixture
 */
class VerificationsFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'email' => 'Lorem ipsum dolor sit amet',
                'phone' => 'Lorem ipsum dolor sit amet',
                'email_otp_hash' => 'Lorem ipsum dolor sit amet',
                'phone_otp_hash' => 'Lorem ipsum dolor sit amet',
                'email_otp_expires' => '2025-09-28 19:33:39',
                'phone_otp_expires' => '2025-09-28 19:33:39',
                'email_verified' => 1,
                'phone_verified' => 1,
                'email_otp_sent_at' => '2025-09-28 19:33:39',
                'phone_otp_sent_at' => '2025-09-28 19:33:39',
                'email_resend_count' => 1,
                'phone_resend_count' => 1,
                'created' => '2025-09-28 19:33:39',
                'modified' => '2025-09-28 19:33:39',
            ],
        ];
        parent::init();
    }
}
