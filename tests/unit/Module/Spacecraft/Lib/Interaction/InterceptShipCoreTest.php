<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Interaction;

use Doctrine\ORM\EntityManagerInterface;
use Mockery\MockInterface;
use Override;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Spacecraft\Lib\Battle\AlertDetection\AlertReactionFacadeInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;
use Stu\StuTestCase;

class InterceptShipCoreTest extends StuTestCase
{
    /** @var MockInterface&SpacecraftRepositoryInterface */
    private $spacecraftRepository;
    /** @var MockInterface&SpacecraftSystemManagerInterface */
    private $spacecraftSystemManager;
    /** @var MockInterface&AlertReactionFacadeInterface */
    private $alertReactionFacade;
    /** @var MockInterface&PrivateMessageSenderInterface */
    private $privateMessageSender;
    /** @var MockInterface&EntityManagerInterface */
    private $entityManager;

    /** @var MockInterface&ShipWrapperInterface */
    private $wrapper;
    /** @var MockInterface&ShipWrapperInterface */
    private $targetWrapper;
    /** @var MockInterface&ShipInterface */
    private $ship;
    /** @var MockInterface&ShipInterface */
    private $target;
    /** @var MockInterface&InformationInterface */
    private $informations;

    private InterceptShipCoreInterface $subject;

    #[Override]
    public function setUp(): void
    {
        //injected
        $this->spacecraftRepository = $this->mock(SpacecraftRepositoryInterface::class);
        $this->spacecraftSystemManager = $this->mock(SpacecraftSystemManagerInterface::class);
        $this->alertReactionFacade = $this->mock(AlertReactionFacadeInterface::class);
        $this->privateMessageSender = $this->mock(PrivateMessageSenderInterface::class);
        $this->entityManager = $this->mock(EntityManagerInterface::class);

        $this->wrapper = $this->mock(ShipWrapperInterface::class);
        $this->targetWrapper = $this->mock(ShipWrapperInterface::class);
        $this->ship = $this->mock(ShipInterface::class);
        $this->target = $this->mock(ShipInterface::class);
        $this->target = $this->mock(ShipInterface::class);
        $this->informations = $this->mock(InformationInterface::class);

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($this->ship);
        $this->targetWrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($this->target);

        $this->subject = new InterceptShipCore(
            $this->spacecraftRepository,
            $this->spacecraftSystemManager,
            $this->alertReactionFacade,
            $this->privateMessageSender,
            $this->entityManager
        );
    }

    public function testInterceptExpectInterceptionOfSingleTarget(): void
    {
        $tractoredWrapper1 = $this->mock(ShipWrapperInterface::class);
        $tractoredWrapper2 = $this->mock(ShipWrapperInterface::class);

        $userId = 123;
        $targetUserId = 456;

        $this->ship->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);
        $this->ship->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('SHIP');

        $this->target->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn($targetUserId);
        $this->target->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('TARGET');

        $this->wrapper->shouldReceive('getFleetWrapper')
            ->withNoArgs()
            ->once()
            ->andReturn(null);
        $this->wrapper->shouldReceive('getTractoredShipWrapper')
            ->withNoArgs()
            ->once()
            ->andReturn($tractoredWrapper1);

        $this->targetWrapper->shouldReceive('getFleetWrapper')
            ->withNoArgs()
            ->once()
            ->andReturn(null);
        $this->targetWrapper->shouldReceive('getTractoredShipWrapper')
            ->withNoArgs()
            ->once()
            ->andReturn($tractoredWrapper2);

        $this->spacecraftSystemManager->shouldReceive('deactivate')
            ->with($this->wrapper, SpacecraftSystemTypeEnum::WARPDRIVE)
            ->once();
        $this->spacecraftSystemManager->shouldReceive('deactivate')
            ->with($this->targetWrapper, SpacecraftSystemTypeEnum::WARPDRIVE)
            ->once();

        $this->spacecraftRepository->shouldReceive('save')
            ->with($this->ship)
            ->once();
        $this->spacecraftRepository->shouldReceive('save')
            ->with($this->target)
            ->once();

        $this->alertReactionFacade->shouldReceive('doItAll')
            ->with($this->targetWrapper, $this->informations)
            ->once();
        $this->alertReactionFacade->shouldReceive('doItAll')
            ->with($tractoredWrapper2, $this->informations)
            ->once();
        $this->alertReactionFacade->shouldReceive('doItAll')
            ->with($tractoredWrapper1, $this->informations)
            ->once();
        $this->alertReactionFacade->shouldReceive('doItAll')
            ->with($this->wrapper, $this->informations)
            ->once();

        $this->informations->shouldReceive('addInformationf')
            ->with('Die %s wurde abgefangen', 'TARGET')
            ->once();

        $this->privateMessageSender->shouldReceive('send')
            ->with(
                $userId,
                $targetUserId,
                'Die TARGET wurde von der SHIP abgefangen',
                PrivateMessageFolderTypeEnum::SPECIAL_SHIP,
                $this->target
            )
            ->once();

        $this->entityManager->shouldReceive('flush')
            ->withNoArgs()
            ->once();

        $this->subject->intercept($this->wrapper, $this->targetWrapper, $this->informations);
    }
}
