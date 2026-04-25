<?php

namespace App\Agent\Tool;

use Symfony\AI\Agent\Toolbox\Attribute\AsTool;
use Symfony\Component\Clock\ClockInterface;

#[AsTool('clock', 'Returns the current date (including day of the week) and time.')]
final class Clock
{
    public function __construct(
        private readonly ClockInterface $clock,
    ) {
    }

    public function __invoke(): string
    {
        $now = $this->clock->now();

        return \sprintf(
            'Today is %s, %s %s %d. Current time is %s (%s). ISO 8601: %s.',
            $now->format('l'),
            $now->format('j'),
            $now->format('F'),
            (int) $now->format('Y'),
            $now->format('H:i'),
            $now->format('T'),
            $now->format(\DateTimeInterface::ATOM),
        );
    }
}
