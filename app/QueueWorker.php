<?php
declare(strict_types=1);

namespace App;

use App\Admin\Worker\ExampleWorker;
use Megio\Queue\IQueueWorkerEnum;

enum QueueWorker: string implements IQueueWorkerEnum
{
    case TIME_ENTRY_SYNC_WORKER = 'example.worker';

    public function className(): string
    {
        return match ($this) {
            self::TIME_ENTRY_SYNC_WORKER => ExampleWorker::class,
        };
    }
}
