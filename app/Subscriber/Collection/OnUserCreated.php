<?php
declare(strict_types=1);

namespace App\Subscriber\Collection;

use Megio\Event\Collection\Events;
use Megio\Event\Collection\OnFinishEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OnUserCreated implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            Events::ON_FINISH->value => ['handle'],
        ];
    }
    
    public function handle(OnFinishEvent $event): void
    {
        /*if ($event->getEventType() === EventType::CREATE
            && $event->getRecipe()::class === UserRecipe::class) {
                dumpe($event->getData());
        }*/
    }
}