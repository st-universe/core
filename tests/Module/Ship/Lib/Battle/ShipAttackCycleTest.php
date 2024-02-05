<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle;

use Mockery;
use Mockery\MockInterface;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Ship\Lib\Message\Message;
use Stu\Module\Ship\Lib\Battle\Provider\AttackerProviderFactoryInterface;
use Stu\Module\Ship\Lib\Battle\Provider\ShipAttacker;
use Stu\Module\Ship\Lib\Battle\Weapon\EnergyWeaponPhaseInterface;
use Stu\Module\Ship\Lib\Battle\Weapon\ProjectileWeaponPhaseInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\StuTestCase;

class ShipAttackCycleTest extends StuTestCase
{
    /** @var MockInterface|ShipRepositoryInterface */
    private ShipRepositoryInterface $shipRepository;

    /** @var MockInterface|EnergyWeaponPhaseInterface */
    private EnergyWeaponPhaseInterface $energyWeaponPhase;

    /** @var MockInterface|ProjectileWeaponPhaseInterface */
    private ProjectileWeaponPhaseInterface $projectileWeaponPhase;

    /** @var MockInterface|FightLibInterface */
    private FightLibInterface $fightLib;

    /** @var MockInterface|AttackerProviderFactoryInterface */
    private AttackerProviderFactoryInterface $attackerProviderFactory;

    /** @var MockInterface|AttackMatchupInterface */
    private AttackMatchupInterface $attackMatchup;

    private ShipAttackCycleInterface $subject;

    public function setUp(): void
    {
        $this->shipRepository = $this->mock(ShipRepositoryInterface::class);
        $this->energyWeaponPhase = $this->mock(EnergyWeaponPhaseInterface::class);
        $this->projectileWeaponPhase = $this->mock(ProjectileWeaponPhaseInterface::class);
        $this->fightLib = $this->mock(FightLibInterface::class);
        $this->attackerProviderFactory = $this->mock(AttackerProviderFactoryInterface::class);
        $this->attackMatchup = $this->mock(AttackMatchupInterface::class);

        $this->subject = new ShipAttackCycle(
            $this->shipRepository,
            $this->energyWeaponPhase,
            $this->projectileWeaponPhase,
            $this->fightLib,
            $this->attackerProviderFactory,
            $this->attackMatchup
        );
    }

    public function testCycleExpectReadyMessagesInResult(): void
    {
        $attacker1 = $this->mock(ShipWrapperInterface::class);
        $attacker2 = $this->mock(ShipWrapperInterface::class);
        $defender1 = $this->mock(ShipWrapperInterface::class);
        $defender2 = $this->mock(ShipWrapperInterface::class);

        $ship1 = $this->mock(ShipInterface::class);
        $ship2 = $this->mock(ShipInterface::class);
        $ship3 = $this->mock(ShipInterface::class);
        $ship4 = $this->mock(ShipInterface::class);

        $attackers = [$attacker1, $attacker2];
        $defenders = [$defender1, $defender2];

        $attacker1->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship1);
        $attacker2->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship2);
        $defender1->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship3);
        $defender2->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship4);

        $ship1->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn(1);
        $ship2->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn(2);
        $ship3->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn(3);
        $ship4->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn(4);

        $this->fightLib->shouldReceive('ready')
            ->with($attacker1)
            ->once()
            ->andReturn(new InformationWrapper(['ready1']));
        $this->fightLib->shouldReceive('ready')
            ->with($attacker2)
            ->once()
            ->andReturn(new InformationWrapper(['ready2']));
        $this->fightLib->shouldReceive('ready')
            ->with($defender1)
            ->once()
            ->andReturn(new InformationWrapper(['ready3']));
        $this->fightLib->shouldReceive('ready')
            ->with($defender2)
            ->once()
            ->andReturn(new InformationWrapper(['ready4']));

        $this->attackMatchup->shouldReceive('getMatchup')
            ->with($attackers, $defenders, [], true, false)
            ->once()
            ->andReturn(null);

        $this->shipRepository->shouldReceive('save')
            ->with($ship1)
            ->once();
        $this->shipRepository->shouldReceive('save')
            ->with($ship2)
            ->once();
        $this->shipRepository->shouldReceive('save')
            ->with($ship3)
            ->once();
        $this->shipRepository->shouldReceive('save')
            ->with($ship4)
            ->once();

        $collection = $this->subject->cycle($attackers, $defenders);

        $this->assertEquals(
            new InformationWrapper(['ready1', 'ready2', 'ready3', 'ready4']),
            $collection->getInformationDump()
        );
    }

    public function testCycleExpectTwoRoundsToHappen(): void
    {
        $attackers = [];
        $defenders = [];

        $firstMatchup = $this->mock(Matchup::class);
        $attacker = $this->mock(ShipWrapperInterface::class);
        $shipAttacker = $this->mock(ShipAttacker::class);

        $firstMatchup->shouldReceive('getDefenders')
            ->withNoArgs()
            ->once()
            ->andReturn($defenders);
        $firstMatchup->shouldReceive('getAttacker')
            ->withNoArgs()
            ->once()
            ->andReturn($attacker);

        $this->attackerProviderFactory->shouldReceive('getShipAttacker')
            ->with($attacker)
            ->once()
            ->andReturn($shipAttacker);

        $this->attackMatchup->shouldReceive('getMatchup')
            ->with(
                $attackers,
                $defenders,
                Mockery::on(function (&$data) {
                    if (!is_array($data)) {
                        return false;
                    }
                    $data[] = 42;
                    return true;
                }),
                true,
                true
            )
            ->once()
            ->andReturn($firstMatchup);

        $this->attackMatchup->shouldReceive('getMatchup')
            ->with(
                $attackers,
                $defenders,
                [42],
                false,
                true
            )
            ->once()
            ->andReturn(null);

        $this->energyWeaponPhase->shouldReceive('fire')
            ->with($shipAttacker, $defenders, true)
            ->once()
            ->andReturn([new Message(1, 2, ['energy'])]);
        $this->fightLib->shouldReceive('filterInactiveShips')
            ->with($defenders)
            ->once()
            ->andReturn($defenders);
        $this->projectileWeaponPhase->shouldReceive('fire')
            ->with($shipAttacker, $defenders, true)
            ->once()
            ->andReturn([new Message(1, 2, ['projectile'])]);

        $collection = $this->subject->cycle($attackers, $defenders, true, true);

        $this->assertEquals(['energy', 'projectile'], $collection->getInformationDump()->getInformations());
    }
}
