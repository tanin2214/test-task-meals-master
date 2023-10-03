<?php

namespace tests\Meals\Unit\Application\Component\Validator;

use Meals\Application\Component\Provider\PollResultProviderInterface;
use Meals\Application\Component\Validator\Exception\EmployeeAlreadyVoteException;
use Meals\Application\Component\Validator\EmployeeHaveActivePollResultValidator;
use Meals\Domain\Poll\PollResult;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class UserHaveActivePollResultValidatorTest extends TestCase
{
    use ProphecyTrait;

    public function testSuccessful()
    {
        $pollResultProvider = $this->prophesize(PollResultProviderInterface::class);
        $pollResultProvider->getPollResultBy(
          $employeeId = 1,
          $pollId = 2
        )->willReturn(null);

        $validator = new EmployeeHaveActivePollResultValidator($pollResultProvider->reveal());
        verify($validator->validate($employeeId, $pollId))->null();
    }

    public function testFail()
    {
        $this->expectException(EmployeeAlreadyVoteException::class);

        $pollResult = $this->prophesize(PollResult::class);
        $pollResultProvider = $this->prophesize(PollResultProviderInterface::class);
        $pollResultProvider->getPollResultBy(
            $employeeId = 1,
            $pollId = 2
        )->willReturn($pollResult->reveal());

        $validator = new EmployeeHaveActivePollResultValidator($pollResultProvider->reveal());
        $validator->validate($employeeId, $pollId);
    }
}
