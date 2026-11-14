<?php

declare(strict_types=1);

namespace Stu\Action;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use request;
use Stu\ActionTestCase;
use Stu\Component\Borg\BorgSpherePhaseEnum;
use Stu\Module\Control\Component\Action\ActionContextFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\StuTime;
use Stu\Module\Spacecraft\Action\CollectBorgNanites\CollectBorgNanites;
use Stu\Orm\Entity\BorgSphereConfiguration;
use Stu\Orm\Entity\BorgSphereEvent;
use Stu\Orm\Entity\BorgSphereRaid;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Crew;
use Stu\Orm\Entity\NPCQuest;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\Storage;
use Stu\Orm\Entity\User;

final class BorgCubeRaidAvailabilityIntegrationTest extends ActionTestCase
{
    /** @return iterable<string, array{BorgSpherePhaseEnum, bool}> */
    public static function activeCubeStates(): iterable
    {
        yield 'ordinary active cube' => [BorgSpherePhaseEnum::ROAMING, false];
        yield 'disabled flag without regeneration phase' => [BorgSpherePhaseEnum::ROAMING, true];
        yield 'old regeneration phase after reactivation' => [BorgSpherePhaseEnum::REGENERATING, false];
        yield 'approaching cube' => [BorgSpherePhaseEnum::APPROACH, false];
    }

    #[DataProvider('activeCubeStates')]
    public function testForgedAwayTeamActionCannotChangeCrewCargoOrRaidStateOnAnActiveCube(BorgSpherePhaseEnum $phase, bool $disabled): void
    {
        $container = $this->getContainer();
        $em = $container->get(EntityManagerInterface::class);
        self::$testSession->setUserById(101);
        $source = $em->find(Ship::class, 42);
        $cube = $em->find(Ship::class, 77);
        $colony = $em->find(Colony::class, 42);
        $source->setLocation($colony->getStarsystemMap());
        $cube->setLocation($colony->getStarsystemMap())->setUser($em->find(User::class, 10));
        $cube->getCondition()->setDisabled($disabled)->setIsDestroyed(false);
        $now = $container->get(StuTime::class)->time();
        $quest = new NPCQuest()->setUser($cube->getUser())->setUserId(10)->setTitle('Kubus-Außenmissionstest')->setStart($now - 100);
        $configuration = new BorgSphereConfiguration()->setId(1)->setColony($colony)->setQuest($quest)->setUser($cube->getUser())
            ->setBuildplan($em->find(SpacecraftBuildplan::class, 2324))->setEndTime($now + 86400)->setSettings(['assimilation_enabled' => 1]);
        $event = new BorgSphereEvent()->setConfiguration($configuration)->setShipId($cube->getId())->setPhase($phase);
        $em->persist($quest);
        $em->persist($configuration);
        $em->persist($event);
        $em->flush();
        $crewCount = $source->getCrewCount();
        $totalCrew = $em->getRepository(Crew::class)->count([]);
        $storage = array_map(static fn (Storage $entry): array => [$entry->getCommodityId(), $entry->getAmount()], $source->getStorage()->toArray());
        $raidCount = $em->getRepository(BorgSphereRaid::class)->count([]);
        request::setMockVars([CollectBorgNanites::ACTION_IDENTIFIER => 1, 'id' => $source->getId(), 'target' => $cube->getId()]);
        $action = $container->get(CollectBorgNanites::class);
        $context = $container->get(ActionContextFactoryInterface::class)->createActionContext($container->get(GameControllerInterface::class));

        $em->wrapInTransaction(fn () => $action->handle($context));

        self::assertContains('Eine Außenmission zum Borg-Kubus ist nur möglich, solange er kampfunfähig ist', $context->getInfo()->getInformations());
        self::assertSame($crewCount, $source->getCrewCount());
        self::assertSame($totalCrew, $em->getRepository(Crew::class)->count([]));
        self::assertSame($storage, array_map(static fn (Storage $entry): array => [$entry->getCommodityId(), $entry->getAmount()], $source->getStorage()->toArray()));
        self::assertSame($raidCount, $em->getRepository(BorgSphereRaid::class)->count([]));
    }
}
