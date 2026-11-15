<?php

declare(strict_types=1);

namespace Stu\Action;

use Doctrine\ORM\EntityManagerInterface;
use request;
use Stu\ActionTestCase;
use Stu\Component\Game\ModuleEnum;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Control\Component\View\ViewControllerContext;
use Stu\Module\Control\StuTime;
use Stu\Module\Ship\Lib\Fleet\LeaveFleetInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftAssimilated;
use Stu\Orm\Entity\User;

final class SpacecraftViewFallbackIntegrationTest extends ActionTestCase
{
    public function testReloadAfterBorgOwnershipTransferShowsOnlyTheFormerOwnersCurrentShipList(): void
    {
        $container = $this->getContainer();
        $em = $container->get(EntityManagerInterface::class);
        $ship = $em->find(Ship::class, 42);
        $cube = $em->find(Ship::class, 77);
        $borg = $em->find(User::class, 19);
        $user = $em->find(User::class, 101);
        self::assertNotNull($ship);
        self::assertNotNull($cube);
        self::assertNotNull($borg);
        self::assertNotNull($user);
        self::assertNotNull(self::$testSession);
        self::$testSession->setUserById(101);
        foreach ([$ship, $cube] as $spacecraft) {
            $container->get(LeaveFleetInterface::class)->leaveFleet($spacecraft);
            $spacecraft->transferToUser($borg);
        }
        foreach ($ship->getCrewAssignments() as $assignment) {
            $assignment->setUser($borg);
            $assignment->getCrew()->transferToUser($borg);
        }
        $now = $container->get(StuTime::class)->time();
        $entry = new SpacecraftAssimilated()->setSpacecraft($ship)->start($cube, $now - 900, 900)->complete($now);
        $em->persist($entry);
        $em->flush();
        $information = new InformationWrapper();
        $variables = [];
        $context = $this->mock(ViewControllerContext::class);
        $context->shouldReceive('getUser')->andReturn($user);
        $context->shouldReceive('getModule')->once()->andReturn(ModuleEnum::SHIP);
        $context->shouldReceive('getInfo')->once()->andReturn($information);
        $context->shouldReceive('appendNavigationPart')->once()->with('ship.php', 'Schiffe');
        $context->shouldReceive('setPageTitle')->once()->with('Schiffe');
        $context->shouldReceive('setViewTemplate')->once()->with(ModuleEnum::SHIP->getTemplate());
        $context->shouldReceive('setTemplateVar')->andReturnUsing(static function (string $key, mixed $value) use (&$variables): void {
            $variables[$key] = $value;
        });
        $context->shouldNotReceive('addExecuteJS', 'getViewContextMetadata');
        request::setMockVars(['id' => 42]);

        $container->get(ShowSpacecraft::class)->handle($context);

        self::assertSame(['Dieses Schiff ist nicht mehr verfügbar oder gehört nicht zu deinem Account'], $information->getInformations());
        self::assertArrayNotHasKey('WRAPPER', $variables);
        self::assertArrayNotHasKey('VISUAL_NAV_PANEL', $variables);
        self::assertSame([], $variables['FLEET_WRAPPERS']);
        self::assertIsIterable($variables['SINGLESHIPWRAPPERS']);
        foreach ($variables['SINGLESHIPWRAPPERS'] as $wrapper) {
            self::assertInstanceOf(SpacecraftWrapperInterface::class, $wrapper);
            self::assertSame(101, $wrapper->get()->getUser()->getId());
            self::assertNotContains($wrapper->get()->getId(), [42, 77]);
        }
        self::assertSame(19, $ship->getUser()->getId());
        self::assertTrue($entry->isComplete());
    }
}
