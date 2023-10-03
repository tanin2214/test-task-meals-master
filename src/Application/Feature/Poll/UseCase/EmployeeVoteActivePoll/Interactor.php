<?php

namespace Meals\Application\Feature\Poll\UseCase\EmployeeVoteActivePoll;

use Meals\Application\Component\Provider\DishProviderInterface;
use Meals\Application\Component\Provider\EmployeeProviderInterface;
use Meals\Application\Component\Provider\PollProviderInterface;
use Meals\Application\Component\Validator\DishInPollMenuValidator;
use Meals\Application\Component\Validator\PollIsActiveValidator;
use Meals\Application\Component\Validator\UserHasAccessToParticipatePollsValidator;
use Meals\Application\Component\Validator\EmployeeHaveActivePollResultValidator;
use Meals\Application\Component\Validator\UserVoteAtValidTimeValidator;
use Meals\Domain\Poll\PollResult;

class Interactor
{

    /** @var EmployeeProviderInterface */
    private $employeeProvider;

    /** @var PollProviderInterface */
    private $pollProvider;

    /** @var DishProviderInterface */
    private $dishProvider;

    /** @var UserHasAccessToParticipatePollsValidator  */
    private $userHasAccessToParticipatePollsValidator;

    /** @var PollIsActiveValidator */
    private $pollIsActiveValidator;

    /** @var UserVoteAtValidTimeValidator */
    private $userVoteAtValidTimeValidator;

    /** @var EmployeeHaveActivePollResultValidator */
    private $userHaveActivePollResultValidator;

    /** @var DishInPollMenuValidator */
    private $dishInPollMenuValidator;

    /**
     * @param EmployeeProviderInterface $employeeProvider
     * @param PollProviderInterface $pollProvider
     * @param DishProviderInterface $dishProvider
     * @param UserHasAccessToParticipatePollsValidator $userHasAccessToParticipatePollsValidator
     * @param PollIsActiveValidator $pollIsActiveValidator
     * @param EmployeeHaveActivePollResultValidator $userHaveActivePollResultValidator,
     * @param DishInPollMenuValidator $dishInPollMenuValidator
     */
    public function __construct(
        EmployeeProviderInterface                $employeeProvider,
        PollProviderInterface                    $pollProvider,
        DishProviderInterface                    $dishProvider,
        UserHasAccessToParticipatePollsValidator $userHasAccessToParticipatePollsValidator,
        PollIsActiveValidator                    $pollIsActiveValidator,
        EmployeeHaveActivePollResultValidator    $userHaveActivePollResultValidator,
        UserVoteAtValidTimeValidator             $userVoteAtValidTimeValidator,
        DishInPollMenuValidator                  $dishInPollMenuValidator
    ) {
        $this->employeeProvider = $employeeProvider;
        $this->pollProvider = $pollProvider;
        $this->dishProvider = $dishProvider;
        $this->userHasAccessToParticipatePollsValidator = $userHasAccessToParticipatePollsValidator;
        $this->pollIsActiveValidator = $pollIsActiveValidator;
        $this->userHaveActivePollResultValidator = $userHaveActivePollResultValidator;
        $this->userVoteAtValidTimeValidator = $userVoteAtValidTimeValidator;
        $this->dishInPollMenuValidator = $dishInPollMenuValidator;
    }

    public function voteActivePoll(int $employeeId, int $pollId, int $dishId): PollResult
    {
        $employee = $this->employeeProvider->getEmployee($employeeId);
        $this->userHasAccessToParticipatePollsValidator->validate($employee->getUser());

        $poll = $this->pollProvider->getPoll($pollId);
        $this->pollIsActiveValidator->validate($poll);

        $this->userHaveActivePollResultValidator->validate($employeeId, $pollId);
        $this->userVoteAtValidTimeValidator->validate();

        $dish = $this->dishProvider->getDishBy($dishId);

        $this->dishInPollMenuValidator->validate($poll, $dish);

        return new PollResult(
            hexdec(uniqid()),
            $poll,
            $employee,
            $dish,
            $employee->getFloor()
        );
    }
}