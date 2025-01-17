<?php
declare(strict_types=1);

namespace App\Worker;

use Megio\Queue\IQueueWorkerEnum;

enum QueueWorker: string implements IQueueWorkerEnum
{
    case EXAMPLE_WORKER = 'example.worker';
    
    public function className(): string
    {
        return match ($this) {
            self::EXAMPLE_WORKER => ExampleWorker::class,
        };
    }
}