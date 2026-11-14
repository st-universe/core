<?php

declare(strict_types=1);

namespace Stu\Action;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use request;
use Stu\ActionTestCase;
use Stu\Component\Borg\BorgSphereNavigation;
use Stu\Component\Borg\BorgSpherePhaseEnum;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Admin\Action\SpawnBorgSphere;
use Stu\Module\Admin\View\Scripts\ShowScripts;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Control\StuTime;
use Stu\Orm\Entity\BorgSphereConfiguration;
use Stu\Orm\Entity\BorgSphereEvent;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\NPCQuest;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\User;

final class BorgSpawnCoordinatesIntegrationTest extends ActionTestCase
{
    private EntityManagerInterface $em;
    private BorgSphereConfiguration $configuration;
    private Map $location;

    #[Override]
    public function setUp(): void
    {
        parent::setUp();
        $container = $this->getContainer();
        $this->em = $container->get(EntityManagerInterface::class);
        $now = $container->get(StuTime::class)->time();
        $owner = $this->em->find(User::class, 10);
        $quest = new NPCQuest()->setUser($owner)->setUserId($owner->getId())->setTitle('Borg-Koordinatentest')->setStart($now - 100);
        $this->configuration = new BorgSphereConfiguration()->setId(1)->setColony($this->em->find(Colony::class, 42))->setQuest($quest)->setUser($owner)
            ->setBuildplan($this->em->find(SpacecraftBuildplan::class, 2324))->setEndTime($now + 86400)->setSettings(['assimilation_enabled' => 1, 'tick_seconds' => 3600]);
        $this->location = $this->em->getRepository(Map::class)->findOneBy([]);
        $this->em->persist($quest);
        $this->em->persist($this->configuration);
        $this->em->flush();
    }

    public function testAdminCoordinatesSpawnExactlyOnTheRequestedWorldFieldWithoutTheAutomaticSafetyFilter(): void
    {
        $this->location->getFieldType()->setPassable(false);
        $this->em->flush();
        self::assertFalse($this->getContainer()->get(BorgSphereNavigation::class)->isSafe($this->location));
        request::setMockVars([
            'borg_configuration_id' => '1',
            'borg_spawn_layer' => (string) $this->location->getLayer()->getId(),
            'borg_spawn_x' => (string) $this->location->getX(),
            'borg_spawn_y' => (string) $this->location->getY()
        ]);

        $this->spawn();

        $event = $this->em->getRepository(BorgSphereEvent::class)->findOneBy(['configuration' => $this->configuration]);
        self::assertNotNull($event);
        self::assertSame(BorgSpherePhaseEnum::ROAMING, $event->getPhase());
        $ship = $this->em->find(Ship::class, $event->getShipId());
        self::assertNotNull($ship);
        self::assertSame($this->location, $ship->getLocation());
        self::assertSame('Borg-Kubus', $ship->getName());
    }

    public function testBlankCoordinatesKeepTheAutomaticSystemSpawn(): void
    {
        request::setMockVars(['borg_configuration_id' => '1', 'borg_spawn_layer' => '', 'borg_spawn_x' => '', 'borg_spawn_y' => '']);

        $this->spawn();

        $event = $this->em->getRepository(BorgSphereEvent::class)->findOneBy(['configuration' => $this->configuration]);
        self::assertNotNull($event);
        self::assertSame(BorgSpherePhaseEnum::APPROACH, $event->getPhase());
        $ship = $this->em->find(Ship::class, $event->getShipId());
        self::assertNotNull($ship);
        self::assertSame($this->configuration->getColony()->getSystem(), $ship->getSystem());
        self::assertNotSame($this->configuration->getColony()->getStarsystemMap(), $ship->getLocation());
    }

    /** @return iterable<string, array{string, string, string}> */
    public static function invalidCoordinates(): iterable
    {
        yield 'incomplete' => ['', '80', '47'];
        yield 'zero is not an automatic spawn request' => ['0', '', ''];
        yield 'negative' => ['1', '-80', '47'];
        yield 'unknown layer' => ['99999999', '80', '47'];
        yield 'unknown world field' => ['1', '99999999', '99999999'];
    }

    #[DataProvider('invalidCoordinates')]
    public function testInvalidCoordinatesReturnInformationWithoutCreatingAnyShipOrEvent(string $layer, string $x, string $y): void
    {
        $before = $this->em->getRepository(Ship::class)->count([]);
        request::setMockVars(['borg_configuration_id' => '1', 'borg_spawn_layer' => $layer, 'borg_spawn_x' => $x, 'borg_spawn_y' => $y]);

        $information = $this->spawn();

        self::assertCount(1, $information->getInformations());
        self::assertSame($before, $this->em->getRepository(Ship::class)->count([]));
        self::assertSame(0, $this->em->getRepository(BorgSphereEvent::class)->count([]));
    }

    private function spawn(): InformationWrapper
    {
        $information = new InformationWrapper();
        $context = $this->mock(ActionControllerContext::class);
        $context->shouldReceive('setView')->once()->with(ShowScripts::VIEW_IDENTIFIER);
        $context->shouldReceive('getInfo')->andReturn($information);
        $action = $this->getContainer()->get(SpawnBorgSphere::class);
        $this->em->wrapInTransaction(fn () => $action->handle($context));
        return $information;
    }
}
