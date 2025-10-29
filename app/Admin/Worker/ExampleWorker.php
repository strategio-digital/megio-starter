<?php
declare(strict_types=1);

namespace App\Admin\Worker;

use App\EntityManager;
use DateTime;
use Exception;
use Megio\Database\Entity\Queue;
use Megio\Queue\IQueueWorker;
use Megio\Queue\QueueDelay;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Uid\Uuid;

class ExampleWorker implements IQueueWorker
{
    public function __construct(
        protected EntityManager $em,
    ) {}

    /**
     * @throws Exception
     */
    public function process(
        Queue $queueJob,
        OutputInterface $output,
    ): ?QueueDelay {
        $admin_id = $queueJob->getPayload()['admin_id'];

        if (is_string($admin_id) === false) {
            throw new Exception('Admin ID must be a string.');
        }

        if (Uuid::isValid($admin_id) === false) {
            throw new Exception('Invalid admin ID.');
        }

        $admin = $this->em->getAdminRepo()->findOneBy(['id' => $admin_id]);

        if ($admin === null) {
            throw new Exception('Admin not found.');
        }

        if ($admin->getEmail() === 'jz@strategio.dev') {
            // Return null to indicate that the job is done
            return null;
        }

        // Do some heavy stuff here
        $output->writeln('Sleeping for 10 seconds...');
        sleep(10);
        // ...
        // ...

        // Reschedule this job by QueueDelay in 24 hours
        return new QueueDelay(new DateTime('+24 hours'), 'This job has been rescheduled.');
    }
}
