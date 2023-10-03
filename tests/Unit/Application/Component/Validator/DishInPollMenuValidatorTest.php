<?php

namespace tests\Meals\Unit\Application\Component\Validator;

use Meals\Application\Component\Validator\DishInPollMenuValidator;
use Meals\Application\Component\Validator\Exception\DishNotInPollMenuException;
use Meals\Domain\Dish\Dish;
use Meals\Domain\Dish\DishList;
use Meals\Domain\Menu\Menu;
use Meals\Domain\Poll\Poll;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class DishInPollMenuValidatorTest extends TestCase
{
    use ProphecyTrait;

    public function testSuccessful()
    {
        $dish = $this->prophesize(Dish::class);

        $dishList = $this->prophesize(DishList::class);
        $dishList->hasDish($dish->reveal())->willReturn(true);

        $menu = $this->prophesize(Menu::class);
        $menu->getDishes()->willReturn($dishList->reveal());

        $poll = $this->prophesize(Poll::class);
        $poll->getMenu()->willReturn($menu->reveal());

        $validator = new DishInPollMenuValidator();
        verify($validator->validate($poll->reveal(), $dish->reveal()))->null();
    }

    public function testFail()
    {
        $this->expectException(DishNotInPollMenuException::class);

        $dish = $this->prophesize(Dish::class);

        $dishList = $this->prophesize(DishList::class);
        $dishList->hasDish($dish->reveal())->willReturn(false);

        $menu = $this->prophesize(Menu::class);
        $menu->getDishes()->willReturn($dishList->reveal());

        $poll = $this->prophesize(Poll::class);
        $poll->getMenu()->willReturn($menu->reveal());

        $validator = new DishInPollMenuValidator();
        $validator->validate($poll->reveal(), $dish->reveal());
    }
}
