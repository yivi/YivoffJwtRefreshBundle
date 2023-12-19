<?php

declare(strict_types=1);

namespace Yivoff\JwtRefreshBundle\Test\Unit;

use InvalidArgumentException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

use function array_key_exists;

class EventDispatcherSpy implements EventDispatcherInterface
{
    /** @var object[] */
    private array $events = [];

    public function dispatch(object $event, string $eventName = null): object
    {
        $this->events[$eventName ?? $event::class] = $event;

        return $event;
    }

    public function getEventByName(string $name): object
    {
        if (array_key_exists($name, $this->events)) {
            return $this->events[$name];
        }

        throw new InvalidArgumentException('Event does not exist');
    }
}
