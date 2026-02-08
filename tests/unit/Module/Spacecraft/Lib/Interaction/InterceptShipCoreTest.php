<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Interaction;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mockery\MockInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\Battle\AlertDetection\AlertReactionFacadeInterface;
use Stu\Orm\Entity\Ship;
use Stu\StuTestCase;

class InterceptShipCoreTest extends StuTestCase
{
    private MockInterface&SpacecraftSystemManagerInterface $spacecraftSystemManager;
    private MockInterface&AlertReactionFacadeInterface $alertReactionFacade;
    private MockInterface&PrivateMessageSenderInterface $privateMessageSender;
    private MockInterface&EntityManagerInterface $entityManager;

    private MockInterface&ShipWrapperInterface $wrapper;
    private MockInterface&ShipWrapperInterface $targetWrapper;
    private MockInterface&Ship $ship;
    private MockInterface&Ship $target;
    private MockInterface&InformationInterface $informations;

    private InterceptShipCoreInterface $subject;

    #[\Override]
    public function setUp(): void
    {
        //injected
        $this->spacecraftSystemManager = $this->mock(SpacecraftSystemManagerInterface::class);
        $this->alertReactionFacade = $this->mock(AlertReactionFacadeInterface::class);
        $this->privateMessageSender = $this->mock(PrivateMessageSenderInterface::class);
        $this->entityManager = $this->mock(EntityManagerInterface::class);

        $this->wrapper = $this->mock(ShipWrapperInterface::class);
        $this->targetWrapper = $this->mock(ShipWrapperInterface::class);
        $this->ship = $this->mock(Ship::class);
        $this->target = $this->mock(Ship::class);
        $this->target = $this->mock(Ship::class);
        $this->informations = $this->mock(InformationInterface::class);

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($this->ship);
        $this->targetWrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($this->target);

        $this->subject = new InterceptShipCore(
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
        $this->ship->shouldReceive('isWarped')
            ->withNoArgs()
            ->andReturn(false);

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

    public function testInterceptExpectAlertReactionForSourceShipIfWarped(): void
    {
        $userId = 123;
        $targetUserId = 456;

        $this->ship->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);
        $this->ship->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('SHIP');
        $this->ship->shouldReceive('isWarped')
            ->withNoArgs()
            ->andReturn(true);

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
            ->andReturn(null);

        $this->targetWrapper->shouldReceive('getFleetWrapper')
            ->withNoArgs()
            ->once()
            ->andReturn(null);
        $this->targetWrapper->shouldReceive('getTractoredShipWrapper')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $this->spacecraftSystemManager->shouldReceive('deactivate')
            ->with($this->wrapper, SpacecraftSystemTypeEnum::WARPDRIVE)
            ->once();
        $this->spacecraftSystemManager->shouldReceive('deactivate')
            ->with($this->targetWrapper, SpacecraftSystemTypeEnum::WARPDRIVE)
            ->once();

        $this->alertReactionFacade->shouldReceive('doItAll')
            ->with($this->targetWrapper, $this->informations)
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

    public function testInterceptExpectAlertReactionForSourceFleetIfWarped(): void
    {
        $fleetWrapper = $this->mock(FleetWrapperInterface::class);
        $userId = 123;
        $targetUserId = 456;

        $fleetWrapper->shouldReceive('getShipWrappers')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$this->wrapper]));

        $this->ship->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);
        $this->ship->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('SHIP');
        $this->ship->shouldReceive('isWarped')
            ->withNoArgs()
            ->andReturn(true);

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
            ->andReturn($fleetWrapper);
        $this->wrapper->shouldReceive('getTractoredShipWrapper')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $this->targetWrapper->shouldReceive('getFleetWrapper')
            ->withNoArgs()
            ->once()
            ->andReturn(null);
        $this->targetWrapper->shouldReceive('getTractoredShipWrapper')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $this->spacecraftSystemManager->shouldReceive('deactivate')
            ->with($this->wrapper, SpacecraftSystemTypeEnum::WARPDRIVE)
            ->once();
        $this->spacecraftSystemManager->shouldReceive('deactivate')
            ->with($this->targetWrapper, SpacecraftSystemTypeEnum::WARPDRIVE)
            ->once();

        $this->alertReactionFacade->shouldReceive('doItAll')
            ->with($this->targetWrapper, $this->informations)
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
