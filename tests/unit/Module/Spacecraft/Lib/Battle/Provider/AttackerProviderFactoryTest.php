<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Battle\Provider;

use Override;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Control\StuRandom;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\Torpedo\ShipTorpedoManagerInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Repository\ModuleRepositoryInterface;
use Stu\StuTestCase;

class AttackerProviderFactoryTest extends StuTestCase
{
    /**
     * @var MockInterface&ShipTorpedoManagerInterface
     */
    private $shipTorpedoManager;
    /**
     * @var MockInterface&ModuleRepositoryInterface
     */
    private $moduleRepository;
    /**
     * @var MockInterface&StorageManagerInterface
     */
    private $storageManager;
    /**
     * @var MockInterface&StuRandom
     */
    private $stuRandom;

    private AttackerProviderFactoryInterface $subject;

    #[Override]
    public function setUp(): void
    {
        //injected
        $this->shipTorpedoManager = $this->mock(ShipTorpedoManagerInterface::class);
        $this->moduleRepository = $this->mock(ModuleRepositoryInterface::class);
        $this->storageManager = $this->mock(StorageManagerInterface::class);
        $this->stuRandom = $this->mock(StuRandom::class);

        $this->subject = new AttackerProviderFactory(
            $this->shipTorpedoManager,
            $this->moduleRepository,
            $this->storageManager,
            $this->stuRandom
        );
    }

    public function testGetSpacecraftAttacker(): void
    {
        $wrapper = $this->mock(ShipWrapperInterface::class);

        $spacecraftAttacker = $this->subject->getSpacecraftAttacker($wrapper);

        $this->assertNotNull($spacecraftAttacker);
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
