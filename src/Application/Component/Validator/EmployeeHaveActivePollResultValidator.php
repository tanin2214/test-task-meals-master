<?php

namespace Meals\Application\Component\Validator;

use Meals\Application\Component\Provider\PollResultProviderInterface;
use Meals\Application\Component\Validator\Exception\EmployeeAlreadyVoteException;

class EmployeeHaveActivePollResultValidator
{
    /** @var PollResultProviderInterface */
    private $pollResultProvider;

    public function __construct(PollResultProviderInterface $pollResultProvider)
    {
        $this->pollResultProvider = $pollResultProvider;
    }

    public function validate(int $employeeId, int $pollId)
    {
        if (null !== $this->pollResultProvider->getPollResultBy($employeeId, $pollId)) {
            throw new EmployeeAlreadyVoteException();
        }
    }


}