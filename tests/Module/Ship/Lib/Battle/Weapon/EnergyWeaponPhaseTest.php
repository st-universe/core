<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle\Weapon;

use Mockery;
use Mockery\MockInterface;
use Stu\Component\Building\BuildingManagerInterface;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Control\StuRandom;
use Stu\Module\History\Lib\EntryCreatorInterface;
use Stu\Module\Ship\Lib\Battle\Party\BattlePartyInterface;
use Stu\Module\Ship\Lib\Battle\Provider\EnergyAttackerInterface;
use Stu\Module\Ship\Lib\Battle\ShipAttackCauseEnum;
use Stu\Module\Ship\Lib\Battle\Weapon\EnergyWeaponPhase;
use Stu\Module\Ship\Lib\Damage\ApplyDamageInterface;
use Stu\Module\Ship\Lib\Destruction\ShipDestructionCauseEnum;
use Stu\Module\Ship\Lib\Destruction\ShipDestructionInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipRumpInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Entity\WeaponInterface;
use Stu\Orm\Repository\WeaponRepositoryInterface;
use Stu\StuTestCase;

class EnergyWeaponPhaseTest extends StuTestCase
{
    /** @var MockInterface|WeaponRepositoryInterface */
    protected MockInterface $weaponRepository;

    /** @var MockInterface|EntryCreatorInterface */
    protected MockInterface $entryCreator;

    /** @var MockInterface|ApplyDamageInterface */
    protected MockInterface $applyDamage;

    /** @var MockInterface|BuildingManagerInterface */
    protected MockInterface $buildingManager;

    /** @var MockInterface|ShipDestructionInterface */
    private MockInterface $shipDestruction;

    /** @var MockInterface|StuRandom */
    private MockInterface $stuRandom;

    private EnergyWeaponPhaseInterface $subject;

    public function setUp(): void
    {
        $this->weaponRepository = $this->mock(WeaponRepositoryInterface::class);
        $this->entryCreator = $this->mock(EntryCreatorInterface::class);
        $this->applyDamage = $this->mock(ApplyDamageInterface::class);
        $this->buildingManager = $this->mock(BuildingManagerInterface::class);
        $this->stuRandom = $this->mock(StuRandom::class);
        $this->shipDestruction = $this->mock(ShipDestructionInterface::class);

        $this->subject = new EnergyWeaponPhase(
            $this->entryCreator,
            $this->applyDamage,
            $this->buildingManager,
            $this->stuRandom,
            $this->shipDestruction,
            $this->initLoggerUtil()
        );
    }

    public function testFireExpectNoSecondShotIfTargetDestroyedOnFirst(): void
    {
        $attacker = $this->mock(EnergyAttackerInterface::class);
        $target = $this->mock(ShipInterface::class);
        $targetWrapper = $this->mock(ShipWrapperInterface::class);
        $weapon = $this->mock(WeaponInterface::class);
        $user = $this->mock(UserInterface::class);
        $targetUser = $this->mock(UserInterface::class);
        $targetRump = $this->mock(ShipRumpInterface::class);
        $targetPool = $this->mock(BattlePartyInterface::class);

        $targetId = 42;

        $informations = new InformationWrapper();

        $targetPool->shouldReceive('getRandomActiveMember')
            ->withNoArgs()
            ->twice()
            ->andReturn($targetWrapper);
        $targetPool->shouldReceive('isDefeated')
            ->withNoArgs()
            ->twice()
            ->andReturn(false, true);

        $attacker->shouldReceive('getPhaserVolleys')
            ->withNoArgs()
            ->once()
            ->andReturn(2);
        $attacker->shouldReceive('getPhaserState')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $attacker->shouldReceive('hasSufficientEnergy')
            ->with(1)
            ->andReturn(true);
        $attacker->shouldReceive('getWeapon')
            ->withNoArgs()
            ->andReturn($weapon);
        $attacker->shouldReceive('getFiringMode')
            ->withNoArgs()
            ->andReturn(1);
        $attacker->shouldReceive('reduceEps')
            ->with(1)
            ->once();
        $attacker->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $attacker->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn("ATTACKER");
        $attacker->shouldReceive('getHitChance')
            ->withNoArgs()
            ->andReturn(100);
        $attacker->shouldReceive('getWeaponDamage')
            ->with(true)
            ->andReturn(100);
        $attacker->shouldReceive('getPhaserShieldDamageFactor')
            ->withNoArgs()
            ->andReturn(100);
        $attacker->shouldReceive('getPhaserHullDamageFactor')
            ->withNoArgs()
            ->andReturn(100);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(888);

        $targetWrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($target);

        $target->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($targetId);
        $target->shouldReceive('getCloakState')
            ->withNoArgs()
            ->andReturn(false);
        $target->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn("TARGET");
        $target->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($targetUser);
        $target->shouldReceive('getEvadeChance')
            ->withNoArgs()
            ->andReturn(0);
        $target->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->andReturn(true);
        $target->shouldReceive('isBase')
            ->withNoArgs()
            ->andReturn(false);
        $target->shouldReceive('getBuildplan')
            ->withNoArgs()
            ->andReturn(null);
        $target->shouldReceive('getRump')
            ->withNoArgs()
            ->andReturn($targetRump);
        $target->shouldReceive('getSectorString')
            ->withNoArgs()
            ->andReturn("SECTOR");

        $targetRump->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn("RUMP");
        $targetRump->shouldReceive('getPrestige')
            ->withNoArgs()
            ->andReturn(0);

        $targetUser->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(999);

        $weapon->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn("WEAPON");
        $weapon->shouldReceive('getCriticalChance')
            ->withNoArgs()
            ->andReturn(0);
        $weapon->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(123);
        $weapon->shouldReceive('getFiringMode')
            ->withNoArgs()
            ->andReturn(1);

        $this->stuRandom->shouldReceive('rand')
            ->with(1, 100)
            ->andReturn(0);
        $this->stuRandom->shouldReceive('rand')
            ->with(1, 10000)
            ->andReturn(0);

        $this->applyDamage->shouldReceive('damage')
            ->with(Mockery::any(), $targetWrapper)
            ->andReturn($informations);

        $this->shipDestruction->shouldReceive('destroy')
            ->with(
                $attacker,
                $targetWrapper,
                ShipDestructionCauseEnum::SHIP_FIGHT,
                Mockery::any()
            )
            ->once();

        $this->subject->fire($attacker, $targetPool, ShipAttackCauseEnum::SHIP_FIGHT);
    }
}
