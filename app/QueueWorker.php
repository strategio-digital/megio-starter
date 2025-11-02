<?php
declare(strict_types=1);

namespace App;

use App\User\Worker\UserPasswordResetMailWorker;
use App\User\Worker\UserRegistrationMailWorker;
use Megio\Queue\IQueueWorkerEnum;

enum QueueWorker: string implements IQueueWorkerEnum
{
    case USER_REGISTRATION_MAIL_WORKER = 'user.registration.mail.worker';
    case USER_PASSWORD_RESET_MAIL_WORKER = 'user.password.reset.mail.worker';

    public function className(): string
    {
        return match ($this) {
            self::USER_REGISTRATION_MAIL_WORKER => UserRegistrationMailWorker::class,
            self::USER_PASSWORD_RESET_MAIL_WORKER => UserPasswordResetMailWorker::class,
        };
    }
}
