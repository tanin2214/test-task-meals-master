<?php

namespace Meals\Application\Component\Provider;

use Meals\Domain\Dish\Dish;

interface DishProviderInterface
{
    public function getDishBy(int $dishId): Dish;
}
