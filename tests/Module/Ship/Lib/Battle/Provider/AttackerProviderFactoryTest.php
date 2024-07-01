<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle\Provider;

use Stu\Component\Colony\Storage\ColonyStorageManagerInterface;
use Stu\Module\Control\StuRandom;
use Stu\Module\Ship\Lib\ModuleValueCalculatorInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Ship\Lib\Torpedo\ShipTorpedoManagerInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Repository\ModuleRepositoryInterface;
use Stu\StuTestCase;

class AttackerProviderFactoryTest extends StuTestCase
{
    /**
     * @var MockInterface|ModuleValueCalculatorInterface
     */
    private $moduleValueCalculator;
    /**
     * @var MockInterface|ShipTorpedoManagerInterface
     */
    private $shipTorpedoManager;
    /**
     * @var MockInterface|ModuleRepositoryInterface
     */
    private $moduleRepository;
    /**
     * @var MockInterface|ColonyStorageManagerInterface
     */
    private $colonyStorageManager;
    /**
     * @var MockInterface|StuRandom
     */
    private $stuRandom;

    private AttackerProviderFactoryInterface $subject;

    public function setUp(): void
    {
        //injected
        $this->moduleValueCalculator = $this->mock(ModuleValueCalculatorInterface::class);
        $this->shipTorpedoManager = $this->mock(ShipTorpedoManagerInterface::class);
        $this->moduleRepository = $this->mock(ModuleRepositoryInterface::class);
        $this->colonyStorageManager = $this->mock(ColonyStorageManagerInterface::class);
        $this->stuRandom = $this->mock(StuRandom::class);

        $this->subject = new AttackerProviderFactory(
            $this->moduleValueCalculator,
            $this->shipTorpedoManager,
            $this->moduleRepository,
            $this->colonyStorageManager,
            $this->stuRandom
        );
    }

    public function testGetShipAttacker(): void
    {
        $wrapper = $this->mock(ShipWrapperInterface::class);

        $shipAttacker = $this->subject->getShipAttacker($wrapper);

        $this->assertNotNull($shipAttacker);
    }

    public function testGetEnergyPhalanxAttacker(): void
    {
        $colony = $this->mock(ColonyInterface::class);

        $attacker = $this->subject->getEnergyPhalanxAttacker($colony);

        $this->assertNotNull($attacker);
    }

    public function testGetProjectilePhalanxAttacker(): void
    {
        $colony = $this->mock(ColonyInterface::class);

        $attacker = $this->subject->getProjectilePhalanxAttacker($colony);

        $this->assertNotNull($attacker);
    }
}
