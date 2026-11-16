<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionProperty;
use Stu\Component\Crew\CrewTypeEnum;
use Stu\Module\Crew\Lib\CrewCreatorInterface;
use Stu\Orm\Entity\Crew;
use Stu\Orm\Entity\CrewAssignment;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\User;
use Stu\StuTestCase;

final class BorgSphereCrewTest extends StuTestCase
{
    /** @return iterable<string, array{?int, int}> */
    public static function sufficientCrew(): iterable
    {
        yield 'missing buildplan' => [null, 3];
        yield 'no crew required' => [0, 0];
        yield 'zero minimum preserves existing crew' => [0, 2];
        yield 'minimum already reached' => [3, 3];
        yield 'surplus remains aboard' => [3, 5];
    }

    #[DataProvider('sufficientCrew')]
    public function testSufficientCrewOrMissingBuildplanDoesNotGenerateOrRemoveAnyone(?int $minimum, int $existing): void
    {
        $ship = $this->ship($minimum, $existing);
        $before = $ship->getCrewAssignments()->toArray();
        $creator = $this->mock(CrewCreatorInterface::class);
        $creator->shouldNotReceive('create');

        new BorgSphereCrew($creator)->refill($ship);

        self::assertSame($existing, $ship->getCrewCount());
        self::assertSame($before, $ship->getCrewAssignments()->toArray());
    }

    public function testOnlyMissingCrewIsCreatedForTheOwnerAndExistingOfficersRemainUntouched(): void
    {
        $ship = $this->ship(5, 2);
        $existing = $ship->getCrewAssignments()->toArray();
        $created = [];
        $creator = $this->mock(CrewCreatorInterface::class);
        $creator->shouldReceive('create')->times(3)->with(19)->andReturnUsing(static function () use ($ship, &$created): CrewAssignment {
            $assignment = new CrewAssignment()->setUser($ship->getUser())->setCrew(new Crew()->setUser($ship->getUser())->setName('Neue Crew'));
            $created[] = $assignment;
            return $assignment;
        });
        $subject = new BorgSphereCrew($creator);

        $subject->refill($ship);
        $subject->refill($ship);

        self::assertSame(5, $ship->getCrewCount());
        self::assertTrue($ship->hasEnoughCrew());
        self::assertCount(3, $created);
        foreach ($created as $assignment) {
            self::assertSame($ship, $assignment->getSpacecraft());
            self::assertSame($ship->getUser(), $assignment->getUser());
            self::assertSame($ship->getUser(), $assignment->getCrew()->getUser());
            self::assertSame(CrewTypeEnum::CREWMAN, $assignment->getSlot());
            self::assertSame(CrewTypeEnum::CREWMAN, $assignment->getCrew()->getType());
            self::assertTrue($ship->getCrewAssignments()->contains($assignment));
        }
        self::assertSame($existing[0], $ship->getCrewAssignments()->get(0));
        self::assertSame(CrewTypeEnum::CAPTAIN, $existing[0]->getSlot());
        self::assertSame('Vorhandene Crew 0', $existing[0]->getCrew()->getName());
        self::assertSame($existing[1], $ship->getCrewAssignments()->get(1));
    }

    public function testAnotherCrewLossOnlyAddsTheNewlyMissingCrewWithoutRebuildingTheCrew(): void
    {
        $ship = $this->ship(3, 1);
        $captain = $ship->getCrewAssignments()->first();
        $created = [];
        $creator = $this->mock(CrewCreatorInterface::class);
        $creator->shouldReceive('create')->times(3)->with(19)->andReturnUsing(static function () use ($ship, &$created): CrewAssignment {
            $assignment = new CrewAssignment()->setUser($ship->getUser())->setCrew(new Crew()->setUser($ship->getUser()));
            $created[] = $assignment;
            return $assignment;
        });
        $subject = new BorgSphereCrew($creator);
        $subject->refill($ship);
        $lost = $created[0];
        $ship->getCrewAssignments()->removeElement($lost);

        $subject->refill($ship);
        $subject->refill($ship);

        self::assertSame(3, $ship->getCrewCount());
        self::assertTrue($ship->hasEnoughCrew());
        self::assertTrue($ship->getCrewAssignments()->contains($captain));
        self::assertFalse($ship->getCrewAssignments()->contains($lost));
        self::assertTrue($ship->getCrewAssignments()->contains($created[1]));
        self::assertTrue($ship->getCrewAssignments()->contains($created[2]));
    }

    private function ship(?int $minimum, int $existing): Ship
    {
        $owner = new User();
        new ReflectionProperty(User::class, 'id')->setValue($owner, 19);
        $ship = new Ship()->setUser($owner);
        if ($minimum !== null) {
            $ship->setBuildplan(new SpacecraftBuildplan()->setCrew($minimum));
        }
        for ($index = 0; $index < $existing; $index++) {
            $type = $index === 0 ? CrewTypeEnum::CAPTAIN : CrewTypeEnum::CREWMAN;
            $assignment = new CrewAssignment()->setUser($owner)->setSpacecraft($ship)->setSlot($type)
                ->setCrew(new Crew()->setUser($owner)->setType($type)->setName('Vorhandene Crew ' . $index));
            $ship->getCrewAssignments()->add($assignment);
        }
        return $ship;
    }
}
