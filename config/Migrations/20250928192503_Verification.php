<?php

declare(strict_types=1);

use Migrations\BaseMigration;

class Verification extends BaseMigration
{
    public function change(): void
    {
        $table = $this->table('verifications');
        $table->addColumn('email', 'string');
        $table->addColumn('phone', 'string');
        $table->addColumn('email_otp_hash', 'string', ['default' => null]);
        $table->addColumn('phone_otp_hash', 'string', ['default' => null]);
        $table->addColumn('email_otp_expires', 'datetime', ['default' => null]);
        $table->addColumn('phone_otp_expires', 'datetime', ['default' => null]);
        $table->addColumn('email_verified', 'boolean', ['default' => false]);
        $table->addColumn('phone_verified', 'boolean', ['default' => false]);
        $table->addColumn('email_otp_sent_at', 'datetime', ['default' => null]);
        $table->addColumn('phone_otp_sent_at', 'datetime', ['default' => null]);
        $table->addColumn('email_resend_count', 'integer', ['default' => 0]);
        $table->addColumn('phone_resend_count', 'integer', ['default' => 0]);
        $table->addColumn('created', 'datetime', [
            'default' => 'CURRENT_TIMESTAMP'
        ]);
        $table->addColumn('modified', 'datetime', [
            'default' => 'CURRENT_TIMESTAMP',
            'update' => 'CURRENT_TIMESTAMP'
        ]);
        $table->addIndex(['email'], ['name' => 'idx_email']);
        $table->addIndex(['phone'], ['name' => 'idx_phone']);
        $table->create();
    }
}
