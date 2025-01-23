<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Battle\Weapon;

use Mockery;
use Mockery\MockInterface;
use Override;
use Stu\Component\Building\BuildingManagerInterface;
use Stu\Module\Control\StuRandom;
use Stu\Module\History\Lib\EntryCreatorInterface;
use Stu\Module\Spacecraft\Lib\Battle\Party\BattlePartyInterface;
use Stu\Module\Spacecraft\Lib\Battle\Provider\EnergyAttackerInterface;
use Stu\Module\Spacecraft\Lib\Battle\SpacecraftAttackCauseEnum;
use Stu\Module\Spacecraft\Lib\Damage\ApplyDamageInterface;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestructionCauseEnum;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestructionInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageFactoryInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\SpacecraftRumpInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Entity\WeaponInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\Orm\Repository\WeaponRepositoryInterface;
use Stu\StuTestCase;

class EnergyWeaponPhaseTest extends StuTestCase
{
    /** @var MockInterface&UserRepositoryInterface */
    protected $userRepository;
    /** @var MockInterface&WeaponRepositoryInterface */
    protected $weaponRepository;
    /** @var MockInterface&EntryCreatorInterface */
    protected $entryCreator;
    /** @var MockInterface&ApplyDamageInterface */
    protected $applyDamage;
    /** @var MockInterface&BuildingManagerInterface */
    protected $buildingManager;
    /** @var MockInterface&SpacecraftDestructionInterface */
    private $spacecraftDestruction;
    /** @var MockInterface&StuRandom */
    private $stuRandom;
    /** @var MockInterface&MessageFactoryInterface */
    private $messageFactory;

    private EnergyWeaponPhaseInterface $subject;

    #[Override]
    public function setUp(): void
    {
        $this->userRepository = $this->mock(UserRepositoryInterface::class);
        $this->weaponRepository = $this->mock(WeaponRepositoryInterface::class);
        $this->entryCreator = $this->mock(EntryCreatorInterface::class);
        $this->applyDamage = $this->mock(ApplyDamageInterface::class);
        $this->buildingManager = $this->mock(BuildingManagerInterface::class);
        $this->stuRandom = $this->mock(StuRandom::class);
        $this->messageFactory = $this->mock(MessageFactoryInterface::class);
        $this->spacecraftDestruction = $this->mock(SpacecraftDestructionInterface::class);

        $this->subject = new EnergyWeaponPhase(
            $this->userRepository,
            $this->entryCreator,
            $this->applyDamage,
            $this->buildingManager,
            $this->stuRandom,
            $this->messageFactory,
            $this->spacecraftDestruction,
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
        $targetRump = $this->mock(SpacecraftRumpInterface::class);
        $targetPool = $this->mock(BattlePartyInterface::class);
        $messages = $this->mock(MessageCollectionInterface::class);
        $message = $this->mock(MessageInterface::class);

        $targetId = 42;

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
        $attacker->shouldReceive('getUserId')
            ->withNoArgs()
            ->andReturn(888);
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
        $target->shouldReceive('isCloaked')
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
        $target->shouldReceive('isStation')
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
            ->with(Mockery::any(), $targetWrapper, $message);

        $this->spacecraftDestruction->shouldReceive('destroy')
            ->with(
                $attacker,
                $targetWrapper,
                SpacecraftDestructionCauseEnum::SHIP_FIGHT,
                $message
            )
            ->once();

        $messages->shouldReceive('add')
            ->with($message)
            ->once();

        $this->messageFactory->shouldReceive('createMessage')
            ->with(888, 999)
            ->once()
            ->andReturn($message);

        $message->shouldReceive('add')
            ->with('Die ATTACKER feuert mit einem WEAPON auf die TARGET')
            ->once();

        $this->userRepository->shouldReceive('find')
            ->with(888)
            ->once()
            ->andReturn($user);

        $this->subject->fire($attacker, $targetPool, SpacecraftAttackCauseEnum::SHIP_FIGHT, $messages);
    }
}
