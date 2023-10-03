<?php

namespace Meals\Application\Component\Validator;

use Meals\Application\Component\Validator\Exception\NotAllowedVoteDayException;
use Meals\Application\Component\Validator\Exception\NotAllowedVoteTimeException;

class UserVoteAtValidTimeValidator
{
    private const ALLOWED_VOTE_DAY_OF_WEEK = 'Monday';

    public function validate(): void
    {
        $now = new \DateTime();


        if ($now->format('l') !== self::ALLOWED_VOTE_DAY_OF_WEEK) {
            throw new NotAllowedVoteDayException();
        }

        $startTime = $this->startVoteTime();
        $endTime = $this->endVoteTime();

        if ($startTime > $now || $now > $endTime) {
            throw new NotAllowedVoteTimeException();
        }
    }

    private function endVoteTime(): \DateTime
    {
        return (new \DateTime())->setTime(22, 00);
    }

    private function startVoteTime(): \DateTime
    {
        return (new \DateTime())->setTime(6, 00);
    }

}