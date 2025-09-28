<?php
declare(strict_types=1);

namespace App\Model\Table;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class VerificationsTable extends Table
{

    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('verifications');
        $this->setDisplayField('email');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->email('email')
            ->requirePresence('email', 'create')
            ->notEmptyString('email');

        $validator
            ->scalar('phone')
            ->maxLength('phone', 255)
            ->requirePresence('phone', 'create')
            ->notEmptyString('phone');

        $validator
            ->scalar('email_otp_hash')
            ->maxLength('email_otp_hash', 255)
            ->requirePresence('email_otp_hash', 'create')
            ->notEmptyString('email_otp_hash');

        $validator
            ->scalar('phone_otp_hash')
            ->maxLength('phone_otp_hash', 255)
            ->requirePresence('phone_otp_hash', 'create')
            ->notEmptyString('phone_otp_hash');

        $validator
            ->dateTime('email_otp_expires')
            ->requirePresence('email_otp_expires', 'create')
            ->notEmptyDateTime('email_otp_expires');

        $validator
            ->dateTime('phone_otp_expires')
            ->requirePresence('phone_otp_expires', 'create')
            ->notEmptyDateTime('phone_otp_expires');

        $validator
            ->boolean('email_verified')
            ->notEmptyString('email_verified');

        $validator
            ->boolean('phone_verified')
            ->notEmptyString('phone_verified');

        $validator
            ->dateTime('email_otp_sent_at')
            ->requirePresence('email_otp_sent_at', 'create')
            ->notEmptyDateTime('email_otp_sent_at');

        $validator
            ->dateTime('phone_otp_sent_at')
            ->requirePresence('phone_otp_sent_at', 'create')
            ->notEmptyDateTime('phone_otp_sent_at');

        $validator
            ->integer('email_resend_count')
            ->notEmptyString('email_resend_count');

        $validator
            ->integer('phone_resend_count')
            ->notEmptyString('phone_resend_count');

        return $validator;
    }
}
