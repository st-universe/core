<?php

declare(strict_types=1);

namespace Stu\Module\ShipModule;

use Doctrine\Common\Collections\ArrayCollection;
use Stu\Orm\Entity\ModuleSpecialInterface;
use Stu\StuTestCase;

class ModuleSpecialAbilityEnumTest extends StuTestCase
{
    public function testGetHashReturnsNullWhenCollectionEmpty(): void
    {
        $collection = new ArrayCollection();

        $hash = ModuleSpecialAbilityEnum::getHash($collection);

        $this->assertEquals(null, $hash);
    }

    public function testGetHashReturnsHashForSingletonList(): void
    {
        $collection = new ArrayCollection();

        $moduleSpecial = $this->mock(ModuleSpecialInterface::class);
        $moduleSpecial->shouldReceive('getSpecialId')
            ->withNoArgs()
            ->andReturn(1);

        $collection->add($moduleSpecial);

        $hash = ModuleSpecialAbilityEnum::getHash($collection);

        $this->assertEquals(1, $hash);
    }

    public function testGetHashReturnsHashForTwoElements(): void
    {
        $collection = new ArrayCollection();

        $moduleSpecial2 = $this->mock(ModuleSpecialInterface::class);
        $moduleSpecial2->shouldReceive('getSpecialId')
            ->withNoArgs()
            ->andReturn(2);

        $moduleSpecial3 = $this->mock(ModuleSpecialInterface::class);
        $moduleSpecial3->shouldReceive('getSpecialId')
            ->withNoArgs()
            ->andReturn(3);

        $collection->add($moduleSpecial2);
        $collection->add($moduleSpecial3);

        $hash = ModuleSpecialAbilityEnum::getHash($collection);

        $this->assertEquals(6, $hash);
    }

    public function testGetHashReturnsHashForThreeElements(): void
    {
        $collection = new ArrayCollection();

        $moduleSpecial2 = $this->mock(ModuleSpecialInterface::class);
        $moduleSpecial2->shouldReceive('getSpecialId')
            ->withNoArgs()
            ->andReturn(2);

        $moduleSpecial4 = $this->mock(ModuleSpecialInterface::class);
        $moduleSpecial4->shouldReceive('getSpecialId')
            ->withNoArgs()
            ->andReturn(4);

        $moduleSpecial6 = $this->mock(ModuleSpecialInterface::class);
        $moduleSpecial6->shouldReceive('getSpecialId')
            ->withNoArgs()
            ->andReturn(6);

        $collection->add($moduleSpecial2);
        $collection->add($moduleSpecial4);
        $collection->add($moduleSpecial6);

        $hash = ModuleSpecialAbilityEnum::getHash($collection);

        $this->assertEquals(42, $hash);
    }
}
