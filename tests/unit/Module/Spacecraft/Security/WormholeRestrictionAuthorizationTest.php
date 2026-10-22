<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Security;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\Attributes\DataProvider;
use request;
use Stu\Component\Ship\Wormhole\WormholeEntryModeEnum;
use Stu\Component\Ship\Wormhole\WormholeEntryTypeEnum;
use Stu\Exception\AccessViolationException;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Control\Component\View\ViewControllerContext;
use Stu\Module\Control\GameUserRoleCheckerInterface;
use Stu\Module\Spacecraft\Action\AddWormholeRestriction\AddWormholeRestriction;
use Stu\Module\Spacecraft\Action\DeleteWormholeRestriction\DeleteWormholeRestriction;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftUiFactoryInterface;
use Stu\Module\Spacecraft\View\ShowWormholeRestrictions\ShowWormholeRestrictions;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\WormholeEntry;
use Stu\Orm\Entity\WormholeRestriction;
use Stu\Orm\Repository\AllianceRepositoryInterface;
use Stu\Orm\Repository\FactionRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\Orm\Repository\WormholeEntryRepositoryInterface;
use Stu\Orm\Repository\WormholeRestrictionRepositoryInterface;
use Stu\StuTestCase;

final class WormholeRestrictionAuthorizationTest extends StuTestCase
{
    #[DataProvider('entryPoints')]
    public function testNormalPlayerCannotUseManagementEndpoint(string $entryPoint): void
    {
        $roleChecker = $this->mock(GameUserRoleCheckerInterface::class);
        $roleChecker->shouldReceive('isAdmin')->once()->andReturn(false);
        $entries = $this->mock(WormholeEntryRepositoryInterface::class);
        $restrictions = $this->mock(WormholeRestrictionRepositoryInterface::class);
        $this->expectException(AccessViolationException::class);

        match ($entryPoint) {
            'add' => new AddWormholeRestriction(
                $this->mock(FactionRepositoryInterface::class),
                $restrictions,
                $entries,
                $this->mock(AllianceRepositoryInterface::class),
                $this->mock(UserRepositoryInterface::class),
                $roleChecker
            )->handle($this->mock(ActionControllerContext::class)),
            'delete' => new DeleteWormholeRestriction($restrictions, $entries, $roleChecker)
                ->handle($this->mock(ActionControllerContext::class)),
            'view' => new ShowWormholeRestrictions(
                $entries,
                $this->mock(SpacecraftLoaderInterface::class),
                $this->mock(SpacecraftUiFactoryInterface::class),
                $roleChecker
            )->handle($this->mock(ViewControllerContext::class))
        };
    }

    public static function entryPoints(): iterable
    {
        yield ['add'];
        yield ['delete'];
        yield ['view'];
    }

    public function testAdministratorCanAddRestriction(): void
    {
        request::setMockVars(['entryId' => 7, 'target' => 42, 'type' => 1, 'mode' => WormholeEntryModeEnum::ALLOW->value]);
        $checker = $this->mock(GameUserRoleCheckerInterface::class);
        $checker->shouldReceive('isAdmin')->once()->andReturn(true);
        $entry = $this->mock(WormholeEntry::class);
        $entries = $this->mock(WormholeEntryRepositoryInterface::class);
        $entries->shouldReceive('find')->with(7)->once()->andReturn($entry);
        $restrictions = $this->mock(WormholeRestrictionRepositoryInterface::class);
        $restrictions->shouldReceive('existsForTargetAndTypeAndEntry')->with(42, WormholeEntryTypeEnum::USER, $entry)->once()->andReturn(false);
        $restriction = new WormholeRestriction();
        $restrictions->shouldReceive('prototype')->once()->andReturn($restriction);
        $restrictions->shouldReceive('save')->with($restriction)->once();
        $users = $this->mock(UserRepositoryInterface::class);
        $users->shouldReceive('find')->with(42)->once()->andReturn(new User());
        $context = $this->mock(ActionControllerContext::class);
        $context->shouldReceive('setView')->with(ShowWormholeRestrictions::VIEW_IDENTIFIER)->once();

        new AddWormholeRestriction(
            $this->mock(FactionRepositoryInterface::class),
            $restrictions,
            $entries,
            $this->mock(AllianceRepositoryInterface::class),
            $users,
            $checker
        )->handle($context);

        self::assertSame($entry, $restriction->getWormholeEntry());
        self::assertSame(42, $restriction->getTargetId());
    }

    public function testAdministratorCanDeleteAssociatedRestriction(): void
    {
        request::setMockVars(['entryId' => 7, 'restrictionId' => 8]);
        $checker = $this->mock(GameUserRoleCheckerInterface::class);
        $checker->shouldReceive('isAdmin')->once()->andReturn(true);
        $entry = $this->mock(WormholeEntry::class);
        $entry->shouldReceive('getId')->twice()->andReturn(7);
        $entries = $this->mock(WormholeEntryRepositoryInterface::class);
        $entries->shouldReceive('find')->with(7)->once()->andReturn($entry);
        $restriction = new WormholeRestriction()->setWormholeEntry($entry);
        $restrictions = $this->mock(WormholeRestrictionRepositoryInterface::class);
        $restrictions->shouldReceive('find')->with(8)->once()->andReturn($restriction);
        $restrictions->shouldReceive('delete')->with($restriction)->once();
        $context = $this->mock(ActionControllerContext::class);
        $context->shouldReceive('setView')->with(ShowWormholeRestrictions::VIEW_IDENTIFIER)->once();

        new DeleteWormholeRestriction($restrictions, $entries, $checker)->handle($context);
    }

    public function testAdministratorCanViewRulesWithOwnedShip(): void
    {
        request::setMockVars(['entryId' => 7, 'id' => 5]);
        $checker = $this->mock(GameUserRoleCheckerInterface::class);
        $checker->shouldReceive('isAdmin')->once()->andReturn(true);
        $entry = $this->mock(WormholeEntry::class);
        $entry->shouldReceive('getRestrictions')->once()->andReturn(new ArrayCollection());
        $entries = $this->mock(WormholeEntryRepositoryInterface::class);
        $entries->shouldReceive('find')->with(7)->once()->andReturn($entry);
        $loader = $this->mock(SpacecraftLoaderInterface::class);
        $ship = new Ship();
        $loader->shouldReceive('getByIdAndUser')->with(5, 42, false, false)->once()->andReturn($ship);
        $context = $this->mock(ViewControllerContext::class);
        $context->shouldReceive('getUser->getId')->once()->andReturn(42);
        $context->shouldReceive('showMacro')->with('html/spacecraft/wormholePrivileges.twig')->once();
        foreach (['SHIP' => $ship, 'ENTRY' => $entry, 'ENTRY_ID' => 7, 'RESTRICTIONS' => []] as $key => $value) {
            $context->shouldReceive('setTemplateVar')->with($key, $value)->once();
        }

        new ShowWormholeRestrictions($entries, $loader, $this->mock(SpacecraftUiFactoryInterface::class), $checker)->handle($context);
    }
}
