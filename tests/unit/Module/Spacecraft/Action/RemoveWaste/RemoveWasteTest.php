<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\RemoveWaste;

use Mockery\MockInterface;
use Stu\ActionControllerTestCase;
use Stu\Lib\Information\InformationWrapper;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\NPCLogRepositoryInterface;

class RemoveWasteTest extends ActionControllerTestCase
{
    private MockInterface&StorageManagerInterface $storageManager;

    private MockInterface&CommodityRepositoryInterface $commodityRepository;

    /** @var MockInterface&SpacecraftLoaderInterface<SpacecraftWrapperInterface> */
    private MockInterface&SpacecraftLoaderInterface $spacecraftLoader;

    private MockInterface&NPCLogRepositoryInterface $npcLogRepository;

    private RemoveWaste $subject;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->storageManager = $this->mock(StorageManagerInterface::class);
        $this->commodityRepository = $this->mock(CommodityRepositoryInterface::class);
        $this->spacecraftLoader = $this->mock(SpacecraftLoaderInterface::class);
        $this->npcLogRepository = $this->mock(NPCLogRepositoryInterface::class);

        $this->subject = new RemoveWaste(
            $this->storageManager,
            $this->commodityRepository,
            $this->spacecraftLoader,
            $this->npcLogRepository
        );
    }

    public function testHandleReadsSpacecraftIdFromPostRequest(): void
    {
        $getBackup = $_GET;
        $postBackup = $_POST;

        try {
            $_GET = [];
            $_POST = [
                'id' => '168',
                'commodity' => []
            ];

            $user = $this->mock(User::class);
            $spacecraft = $this->mock(Spacecraft::class);
            $info = $this->mock(InformationWrapper::class);

            $this->game->shouldReceive('setView')
                ->with(ShowSpacecraft::VIEW_IDENTIFIER)
                ->once();
            $this->game->shouldReceive('getUser')
                ->withNoArgs()
                ->once()
                ->andReturn($user);
            $this->game->shouldReceive('getInfo')
                ->withNoArgs()
                ->once()
                ->andReturn($info);

            $user->shouldReceive('getId')
                ->withNoArgs()
                ->once()
                ->andReturn(14);

            $this->spacecraftLoader->shouldReceive('getByIdAndUser')
                ->with(168, 14)
                ->once()
                ->andReturn($spacecraft);

            $info->shouldReceive('addInformation')
                ->with('Es wurden keine Waren ausgewählt')
                ->once();

            $this->subject->handle($this->game);
        } finally {
            $_GET = $getBackup;
            $_POST = $postBackup;
        }
    }
}
