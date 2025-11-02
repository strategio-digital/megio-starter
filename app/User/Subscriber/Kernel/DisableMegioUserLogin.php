<?php
declare(strict_types=1);

namespace App\User\Subscriber\Kernel;

use App\User\Database\Entity\User;
use Exception;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouteCollection;

use function assert;
use function is_array;
use function is_string;
use function json_decode;

readonly class DisableMegioUserLogin implements EventSubscriberInterface
{
    public function __construct(
        private RouteCollection $routeCollection,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest'],
        ];
    }

    /**
     * @throws Exception
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $routeName = $request->attributes->get('_route');

        if (is_string($routeName) === false) {
            return;
        }

        $targetRoute = $this->routeCollection->get('megio.auth.email');

        if ($targetRoute === null) {
            throw new Exception('Target route "megio.auth.email" not found');
        }

        $currentRoute = $this->routeCollection->get($routeName);

        if ($currentRoute === $targetRoute) {
            $content = $request->getContent();
            $data = json_decode($content, true);
            assert(is_array($data) === true);
            $source = $data['source'] ?? null;

            if ($source === User::TABLE_NAME) {
                $event->setResponse(new JsonResponse(['errors' => ['megio.user.login.disabled']], 403));
                $event->stopPropagation();
            }
        }
    }
}
