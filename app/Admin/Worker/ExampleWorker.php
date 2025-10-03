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

class ExampleWorker implements IQueueWorker
{
    public function __construct(
        protected EntityManager $em,
    ) {}

    public function process(Queue $queueJob, OutputInterface $output): ?QueueDelay
    {
        $admin_id = $queueJob->getPayload()['admin_id'];
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
