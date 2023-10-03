<?php

namespace tests\Meals\Unit\Application\Component\Validator;

use DateTime;
use Meals\Application\Component\Validator\Exception\NotAllowedVoteDayException;
use Meals\Application\Component\Validator\Exception\NotAllowedVoteTimeException;
use Meals\Application\Component\Validator\UserVoteAtValidTimeValidator;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class UserVoteAtValidTimeValidatorTest extends TestCase
{
    use ProphecyTrait;

    public function testSuccessful()
    {
        timecop_freeze(DateTime::createFromFormat('d-m-Y H:i', '02-10-2023 13:00'));
        $validator = new UserVoteAtValidTimeValidator();
        verify($validator->validate())->null();
        timecop_return();
    }

    /**
     * @dataProvider dataFail
     */
    public function testFail(string $expectedException, DateTime $now)
    {
        timecop_freeze($now);
        $this->expectException($expectedException);

        $validator = new UserVoteAtValidTimeValidator();
        $validator->validate();
        timecop_return();
    }

    public function dataFail(): array
    {
        return [
            'NotAllowedVoteDay' => [
                'expectedException' => NotAllowedVoteDayException::class,
                'now' => DateTime::createFromFormat('d-m-Y H:i', '03-10-2023 13:00')
            ],
            'tooEarlyTime' => [
                'expectedException' => NotAllowedVoteTimeException::class,
                'now' => DateTime::createFromFormat('d-m-Y H:i', '02-10-2023 5:00')
            ],
            'tooLateTime' => [
                'expectedException' => NotAllowedVoteTimeException::class,
                'now' => DateTime::createFromFormat('d-m-Y H:i', '02-10-2023 23:00')
            ]
        ];
    }
}
