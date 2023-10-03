<?php

namespace Meals\Application\Component\Validator;

use Meals\Application\Component\Validator\Exception\DishNotInPollMenuException;
use Meals\Domain\Dish\Dish;
use Meals\Domain\Poll\Poll;

class DishInPollMenuValidator
{
    public function validate(Poll $poll, Dish $dish): void
    {
        if (!$poll->getMenu()->getDishes()->hasDish($dish)) {
            throw new DishNotInPollMenuException();
        }
    }
}
