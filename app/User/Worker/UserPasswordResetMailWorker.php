<?php
declare(strict_types=1);

namespace App\User\Worker;

use App\EntityManager;
use App\User\Mail\PasswordResetMailer;
use Exception;
use Megio\Database\Entity\Queue;
use Megio\Queue\IQueueWorker;
use Megio\Queue\QueueDelay;
use Megio\Translation\Translator;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Uid\Uuid;

use function is_string;

final readonly class UserPasswordResetMailWorker implements IQueueWorker
{
    public function __construct(
        private EntityManager $em,
        private PasswordResetMailer $passwordResetMailer,
        private Translator $translator,
    ) {}

    /**
     * @throws Exception
     */
    public function process(
        Queue $queueJob,
        OutputInterface $output,
    ): ?QueueDelay {
        $payload = $queueJob->getPayload();
        $user_id = $payload['user_id'] ?? null;
        $posix = $payload['posix'] ?? null;

        if (is_string($posix) === false) {
            throw new Exception('Posix locale is missing in the job payload.');
        }

        $this->translator->setPosix($posix);

        if (is_string($user_id) === false) {
            throw new Exception($this->translator->translate('user.error.user_id_invalid_type'));
        }

        if (Uuid::isValid($user_id) === false) {
            throw new Exception($this->translator->translate('user.error.user_id_invalid'));
        }

        $user = $this->em->getUserRepo()->findOneBy(['id' => $user_id]);

        if ($user === null) {
            throw new Exception($this->translator->translate('user.error.user_not_found'));
        }

        $this->passwordResetMailer->send($user);

        return null;
    }
}
