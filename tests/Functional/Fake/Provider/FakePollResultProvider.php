<?php

namespace tests\Meals\Functional\Fake\Provider;

use Meals\Application\Component\Provider\PollResultProviderInterface;
use Meals\Domain\Poll\PollResult;

class FakePollResultProvider implements PollResultProviderInterface
{
    /** @var PollResult|null */
    private $pollResult;

    public function getPollResultBy(int $employeeId, int $pollId): ?PollResult
    {
        return $this->pollResult;
    }

    public function setPollResult(?PollResult $pollResult): void
    {
        $this->pollResult = $pollResult;
    }
}
