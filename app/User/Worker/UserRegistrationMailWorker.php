<?php
declare(strict_types=1);

namespace App\User\Worker;

use App\EntityManager;
use App\User\Mail\UserRegistrationMailer;
use Exception;
use Megio\Database\Entity\Queue;
use Megio\Queue\IQueueWorker;
use Megio\Queue\QueueDelay;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Uid\Uuid;

use function is_string;

final readonly class UserRegistrationMailWorker implements IQueueWorker
{
    public function __construct(
        private EntityManager $em,
        private UserRegistrationMailer $userRegistrationMailer,
    ) {}

    /**
     * @throws Exception
     */
    public function process(
        Queue $queueJob,
        OutputInterface $output,
    ): ?QueueDelay {
        $user_id = $queueJob->getPayload()['user_id'];

        if (is_string($user_id) === false) {
            throw new Exception('User ID must be a string.');
        }

        if (Uuid::isValid($user_id) === false) {
            throw new Exception('Invalid user ID.');
        }

        $user = $this->em->getUserRepo()->findOneBy(['id' => $user_id]);

        if ($user === null) {
            throw new Exception('User not found.');
        }

        $this->userRegistrationMailer->send($user);

        return null;
    }
}
