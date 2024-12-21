<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft;

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
            ->andReturn(ModuleSpecialAbilityEnum::CLOAK);

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
            ->andReturn(ModuleSpecialAbilityEnum::RPG);

        $moduleSpecial3 = $this->mock(ModuleSpecialInterface::class);
        $moduleSpecial3->shouldReceive('getSpecialId')
            ->withNoArgs()
            ->andReturn(ModuleSpecialAbilityEnum::TACHYON_SCANNER);

        $collection->add($moduleSpecial2);
        $collection->add($moduleSpecial3);

        $hash = ModuleSpecialAbilityEnum::getHash($collection);

        $this->assertEquals(10, $hash);
    }

    public function testGetHashReturnsHashForThreeElements(): void
    {
        $collection = new ArrayCollection();

        $moduleSpecial2 = $this->mock(ModuleSpecialInterface::class);
        $moduleSpecial2->shouldReceive('getSpecialId')
            ->withNoArgs()
            ->andReturn(ModuleSpecialAbilityEnum::RPG);

        $moduleSpecial4 = $this->mock(ModuleSpecialInterface::class);
        $moduleSpecial4->shouldReceive('getSpecialId')
            ->withNoArgs()
            ->andReturn(ModuleSpecialAbilityEnum::TACHYON_SCANNER);

        $moduleSpecial6 = $this->mock(ModuleSpecialInterface::class);
        $moduleSpecial6->shouldReceive('getSpecialId')
            ->withNoArgs()
            ->andReturn(ModuleSpecialAbilityEnum::ASTRO_LABORATORY);

        $collection->add($moduleSpecial2);
        $collection->add($moduleSpecial4);
        $collection->add($moduleSpecial6);

        $hash = ModuleSpecialAbilityEnum::getHash($collection);

        $this->assertEquals(42, $hash);
    }
}
