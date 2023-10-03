<?php

namespace tests\Meals\Functional\Interactor;

use DateTime;
use Meals\Application\Component\Validator\Exception\AccessDeniedException;
use Meals\Application\Component\Validator\Exception\DishNotInPollMenuException;
use Meals\Application\Component\Validator\Exception\NotAllowedVoteDayException;
use Meals\Application\Component\Validator\Exception\NotAllowedVoteTimeException;
use Meals\Application\Component\Validator\Exception\PollIsNotActiveException;
use Meals\Application\Component\Validator\Exception\EmployeeAlreadyVoteException;
use Meals\Application\Feature\Poll\UseCase\EmployeeVoteActivePoll\Interactor;
use Meals\Domain\Dish\Dish;
use Meals\Domain\Dish\DishList;
use Meals\Domain\Employee\Employee;
use Meals\Domain\Menu\Menu;
use Meals\Domain\Poll\Poll;
use Meals\Domain\Poll\PollResult;
use Meals\Domain\User\Permission\Permission;
use Meals\Domain\User\Permission\PermissionList;
use Meals\Domain\User\User;
use tests\Meals\Functional\Fake\Provider\FakeDishProvider;
use tests\Meals\Functional\Fake\Provider\FakeEmployeeProvider;
use tests\Meals\Functional\Fake\Provider\FakePollProvider;
use tests\Meals\Functional\Fake\Provider\FakePollResultProvider;
use tests\Meals\Functional\FunctionalTestCase;

class EmployeeVoteActivePollTest extends FunctionalTestCase
{
    public function testSuccessful()
    {
        timecop_freeze(DateTime::createFromFormat('d-m-Y H:i', '02-10-2023 13:00'));
        $pollResult = $this->performTestMethod(
            $employee = $this->getEmployeeWithPermissions(),
            $poll = $this->getPoll(true, [$this->getDish()]),
            $dish = $this->getDish()
        );
        timecop_return();

        $this->assertEquals($pollResult->getEmployee(), $employee);
        $this->assertEquals($pollResult->getDish(), $dish);
        $this->assertEquals($pollResult->getPoll(), $poll);
        $this->assertEquals($pollResult->getEmployeeFloor(), $employee->getFloor());
    }

    /**
     * @dataProvider dataValidation
     */
    public function testValidation(
        string $expectedException,
        Employee $employee,
        Poll $poll,
        Dish $dish,
        \DateTime $now,
        ?PollResult $pollResult
    )
    {
        timecop_freeze($now);
        $this->expectException($expectedException);
        $this->performTestMethod($employee, $poll, $dish, $pollResult);
        timecop_return();
    }

    public function dataValidation(): array
    {
        return [
            'userWithoutPermission' => [
                'expectedException' => AccessDeniedException::class,
                'employee' => $this->getEmployeeWithNoPermissions(),
                'poll' => $this->getPoll(true, [$this->getDish()]),
                'dish' => $this->getDish(),
                'now' => DateTime::createFromFormat('d-m-Y H:i', '02-10-2023 13:00'),
                'pollResult' => null
            ],
            'userVoteWithNoActivePoll' => [
                'expectedException' => PollIsNotActiveException::class,
                'employee' => $this->getEmployeeWithPermissions(),
                'poll' => $this->getPoll(false, [$this->getDish()]),
                'dish' => $this->getDish(),
                'now' => DateTime::createFromFormat('d-m-Y H:i', '02-10-2023 13:00'),
                'pollResult' => null
            ],
            'UserAlreadyVote' => [
                'expectedException' => EmployeeAlreadyVoteException::class,
                'employee' => $this->getEmployeeWithPermissions(),
                'poll' => $this->getPoll(true, [$this->getDish()]),
                'dish' => $this->getDish(),
                'now' => DateTime::createFromFormat('d-m-Y H:i', '02-10-2023 13:00'),
                'pollResult' => $this->getPollResult($this->getDish())
            ],
            'NotAllowedVoteDay' => [
                'expectedException' => NotAllowedVoteDayException::class,
                'employee' => $this->getEmployeeWithPermissions(),
                'poll' => $this->getPoll(true, [$this->getDish()]),
                'dish' => $this->getDish(),
                'now' => DateTime::createFromFormat('d-m-Y H:i', '03-10-2023 13:00'),
                'pollResult' => null
            ],
            'NotAllowedVoteTimeBefore' => [
                'expectedException' => NotAllowedVoteTimeException::class,
                'employee' => $this->getEmployeeWithPermissions(),
                'poll' => $this->getPoll(true, [$this->getDish()]),
                'dish' => $this->getDish(),
                'now' => DateTime::createFromFormat('d-m-Y H:i:s', '02-10-2023 22:02:00'),
                'pollResult' => null
            ],
            'NotAllowedVoteTimeAfter' => [
                'expectedException' => NotAllowedVoteTimeException::class,
                'employee' => $this->getEmployeeWithPermissions(),
                'poll' => $this->getPoll(true, [$this->getDish()]),
                'dish' => $this->getDish(),
                'now' => DateTime::createFromFormat('d-m-Y H:i:s', '02-10-2023 22:02:00'),
                'pollResult' => null
            ],
            'DishNotInPollMenu' => [
                'expectedException' => DishNotInPollMenuException::class,
                'employee' => $this->getEmployeeWithPermissions(),
                'poll' => $this->getPoll(true),
                'dish' => $this->getDish(),
                'now' => DateTime::createFromFormat('d-m-Y H:i:s', '02-10-2023 13:02:00'),
                'pollResult' => null
            ]
        ];
    }


    /**
     * @return Dish
     */
    private function getDish(): Dish
    {
        return new Dish(
            1,
            'dishTitle',
            'dish'
        );
    }

    /**
     * @return Employee
     */
    private function getEmployeeWithPermissions(): Employee
    {
        return new Employee(
            1,
            $this->getUserWithPermissions(),
            4,
            'Surname'
        );
    }

    private function getUserWithPermissions(): User
    {
        return new User(
            1,
            new PermissionList(
                [
                    new Permission(Permission::VIEW_ACTIVE_POLLS),
                    new Permission(Permission::PARTICIPATION_IN_POLLS),
                ]
            ),
        );
    }

    private function getEmployeeWithNoPermissions(): Employee
    {
        return new Employee(
            1,
            $this->getUserWithNoPermissions(),
            4,
            'Surname'
        );
    }

    private function getUserWithNoPermissions(): User
    {
        return new User(
            1,
            new PermissionList([]),
        );
    }

    /**
     * @param Dish[] $dishes
     */
    private function getPoll(bool $active, array $dishes = []): Poll
    {
        return new Poll(
            1,
            $active,
            new Menu(
                1,
                'title',
                new DishList($dishes),
            )
        );
    }

    private function getPollResult(): PollResult
    {
        return new PollResult(
            1,
            $this->getPoll(true),
            $this->getEmployeeWithPermissions(),
            $this->getDish(),
            6
        );
    }

    private function performTestMethod(Employee $employee, Poll $poll, Dish $dish, ?PollResult $pollResult = null): PollResult
    {
        $this->getContainer()->get(FakeEmployeeProvider::class)->setEmployee($employee);
        $this->getContainer()->get(FakePollProvider::class)->setPoll($poll);
        $this->getContainer()->get(FakeDishProvider::class)->setDish($dish);
        $this->getContainer()->get(FakePollResultProvider::class)->setPollResult($pollResult);

        return $this->getContainer()->get(Interactor::class)->voteActivePoll($employee->getId(), $poll->getId(), $dish->getId());
    }
}