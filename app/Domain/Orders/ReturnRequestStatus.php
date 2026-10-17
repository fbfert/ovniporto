<?php

namespace App\Domain\Orders;

/** received → in_progress → completed or declined; received may also close straight away. */
enum ReturnRequestStatus: string
{
    case Received = 'received';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Declined = 'declined';

    /** @return list<self> */
    public function next(): array
    {
        return match ($this) {
            self::Received => [self::InProgress, self::Completed, self::Declined],
            self::InProgress => [self::Completed, self::Declined],
            self::Completed, self::Declined => [],
        };
    }

    public function assertCanMoveTo(self $to): void
    {
        if (! in_array($to, $this->next(), true)) {
            throw new InvalidReturnTransition($this, $to);
        }
    }
}
