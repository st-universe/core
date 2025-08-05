<?php

declare(strict_types=1);

namespace Stu\Component\Anomaly\Type;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Override;
use Stu\Component\Anomaly\AnomalyCreationInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Control\StuRandom;
use Stu\Module\Message\Lib\DistributedMessageSenderInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Module\Spacecraft\Lib\Damage\SystemDamageInterface;
use Stu\Module\Spacecraft\Lib\Message\Message;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageFactoryInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapper;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Entity\Anomaly;
use Stu\Orm\Entity\Location;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\SpacecraftCondition;
use Stu\Orm\Entity\SpacecraftSystem;
use Stu\Orm\Repository\LocationRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;
use Stu\StuTestCase;

class SubspaceEllipseHandlerTest extends StuTestCase
{
    private MockInterface&LocationRepositoryInterface $locationRepository;
    private MockInterface&AnomalyCreationInterface $anomalyCreation;
    private MockInterface&SpacecraftRepositoryInterface $spacecraftRepository;
    private MockInterface&SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory;
    private MockInterface&SystemDamageInterface $systemDamage;
    private MockInterface&PrivateMessageSenderInterface $privateMessageSender;
    private MockInterface&DistributedMessageSenderInterface $distributedMessageSender;
    private MockInterface&StuRandom $stuRandom;
    private MockInterface&MessageFactoryInterface $messageFactory;

    private MockInterface&Anomaly $anomaly;

    private AnomalyHandlerInterface $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->locationRepository = $this->mock(LocationRepositoryInterface::class);
        $this->anomalyCreation = $this->mock(AnomalyCreationInterface::class);
        $this->spacecraftRepository = $this->mock(SpacecraftRepositoryInterface::class);
        $this->spacecraftWrapperFactory = $this->mock(SpacecraftWrapperFactoryInterface::class);
        $this->systemDamage = $this->mock(SystemDamageInterface::class);
        $this->privateMessageSender = $this->mock(PrivateMessageSenderInterface::class);
        $this->distributedMessageSender = $this->mock(DistributedMessageSenderInterface::class);
        $this->stuRandom = $this->mock(StuRandom::class);
        $this->messageFactory = $this->mock(MessageFactoryInterface::class);

        $this->anomaly = $this->mock(Anomaly::class);

        $this->subject = new SubspaceEllipseHandler(
            $this->locationRepository,
            $this->anomalyCreation,
            $this->spacecraftRepository,
            $this->spacecraftWrapperFactory,
            $this->systemDamage,
            $this->privateMessageSender,
            $this->distributedMessageSender,
            $this->stuRandom,
            $this->messageFactory
        );
    }

    public function testHandleSpacecraftTickExpectNoActionWhenConditionsNotSatisfied(): void
    {
        $location = $this->mock(Location::class);
        $spacecraftWithoutShields = $this->mock(Spacecraft::class);
        $spacecraftWithDestroyedShields = $this->mock(Spacecraft::class);
        $spacecraftWithDestroyedShieldsCondition = $this->mock(SpacecraftCondition::class);
        $destroyedShieldSystem = $this->mock(SpacecraftSystem::class);
        $shipsMessageCollecton = $this->mock(MessageCollectionInterface::class);
        $basesMessageCollecton = $this->mock(MessageCollectionInterface::class);

        $this->anomaly->shouldReceive('getLocation')
            ->withNoArgs()
            ->once()
            ->andReturn($location);

        $location->shouldReceive('getSpacecraftsWithoutVacation')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$spacecraftWithoutShields, $spacecraftWithDestroyedShields]));
        $location->shouldReceive('getSectorString')
            ->withNoArgs()
            ->once()
            ->andReturn('SECTOR');

        $spacecraftWithoutShields->shouldReceive('hasSpacecraftSystem')
            ->with(SpacecraftSystemTypeEnum::SHIELDS)
            ->andReturn(false);

        $spacecraftWithDestroyedShields->shouldReceive('hasSpacecraftSystem')
            ->with(SpacecraftSystemTypeEnum::SHIELDS)
            ->andReturn(true);
        $spacecraftWithDestroyedShields->shouldReceive('getSpacecraftSystem')
            ->with(SpacecraftSystemTypeEnum::SHIELDS)
            ->andReturn($destroyedShieldSystem);
        $spacecraftWithDestroyedShields->shouldReceive('getCondition')
            ->withNoArgs()
            ->andReturn($spacecraftWithDestroyedShieldsCondition);

        $spacecraftWithDestroyedShieldsCondition->shouldReceive('getShield')
            ->withNoArgs()
            ->andReturn(0);

        $destroyedShieldSystem->shouldReceive('isHealthy')
            ->withNoArgs()
            ->andReturn(false);

        $this->messageFactory->shouldReceive('createMessageCollection')
            ->withNoArgs()
            ->twice()
            ->andReturn($shipsMessageCollecton, $basesMessageCollecton);

        $shipsMessageCollecton->shouldReceive('isEmpty')
            ->withNoArgs()
            ->andReturn(true);
        $basesMessageCollecton->shouldReceive('isEmpty')
            ->withNoArgs()
            ->andReturn(true);

        $this->subject->handleSpacecraftTick($this->anomaly);
    }

    public function testHandleSpacecraftTickExpectActionsWhenConditionsSatisfied(): void
    {
        $location = $this->mock(Location::class);
        $spacecraftWithDepletedShields = $this->mock(Spacecraft::class);
        $spacecraftWithFilledShields = $this->mock(Spacecraft::class);
        $wrapperWithDepletedShields = $this->mock(SpacecraftWrapper::class);
        $wrapperWithFilledShields = $this->mock(SpacecraftWrapper::class);
        $spacecraftWithDepletedShieldsCondition = $this->mock(SpacecraftCondition::class);
        $spacecraftWithFilledShieldsCondition = $this->mock(SpacecraftCondition::class);
        $depletedShieldSystem = $this->mock(SpacecraftSystem::class);
        $filledShieldSystem = $this->mock(SpacecraftSystem::class);
        $shipsMessageCollecton = $this->mock(MessageCollectionInterface::class);
        $basesMessageCollecton = $this->mock(MessageCollectionInterface::class);
        $depletedMessage = $this->mock(Message::class);
        $filledMessage = $this->mock(Message::class);

        $this->anomaly->shouldReceive('getLocation')
            ->withNoArgs()
            ->once()
            ->andReturn($location);

        $location->shouldReceive('getSpacecraftsWithoutVacation')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$spacecraftWithDepletedShields, $spacecraftWithFilledShields]));
        $location->shouldReceive('getSectorString')
            ->withNoArgs()
            ->once()
            ->andReturn('SECTOR');

        $spacecraftWithDepletedShields->shouldReceive('isStation')
            ->withNoArgs()
            ->andReturn(true);
        $spacecraftWithDepletedShields->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('DEPLETED_SHIELDS_NAME');
        $spacecraftWithDepletedShields->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->andReturn(111);
        $spacecraftWithDepletedShields->shouldReceive('hasSpacecraftSystem')
            ->with(SpacecraftSystemTypeEnum::SHIELDS)
            ->andReturn(true);
        $spacecraftWithDepletedShields->shouldReceive('getSpacecraftSystem')
            ->with(SpacecraftSystemTypeEnum::SHIELDS)
            ->andReturn($depletedShieldSystem);
        $spacecraftWithDepletedShields->shouldReceive('getCondition')
            ->withNoArgs()
            ->andReturn($spacecraftWithDepletedShieldsCondition);

        $spacecraftWithFilledShields->shouldReceive('isStation')
            ->withNoArgs()
            ->andReturn(false);
        $spacecraftWithFilledShields->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('FILLED_SHIELDS_NAME');
        $spacecraftWithFilledShields->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->andReturn(222);
        $spacecraftWithFilledShields->shouldReceive('hasSpacecraftSystem')
            ->with(SpacecraftSystemTypeEnum::SHIELDS)
            ->andReturn(true);
        $spacecraftWithFilledShields->shouldReceive('getSpacecraftSystem')
            ->with(SpacecraftSystemTypeEnum::SHIELDS)
            ->andReturn($filledShieldSystem);
        $spacecraftWithFilledShields->shouldReceive('getCondition')
            ->withNoArgs()
            ->andReturn($spacecraftWithFilledShieldsCondition);

        $spacecraftWithDepletedShieldsCondition->shouldReceive('getShield')
            ->withNoArgs()
            ->andReturn(0);

        $spacecraftWithFilledShieldsCondition->shouldReceive('getShield')
            ->withNoArgs()
            ->andReturn(1);
        $spacecraftWithFilledShieldsCondition->shouldReceive('setShield')
            ->with(0);

        $depletedShieldSystem->shouldReceive('isHealthy')
            ->withNoArgs()
            ->andReturn(true);
        $depletedShieldSystem->shouldReceive('getMode')
            ->withNoArgs()
            ->andReturn(SpacecraftSystemModeEnum::MODE_OFF);
        $depletedShieldSystem->shouldReceive('getStatus')
            ->withNoArgs()
            ->andReturn(1);

        $filledShieldSystem->shouldReceive('isHealthy')
            ->withNoArgs()
            ->andReturn(true);
        $filledShieldSystem->shouldReceive('getMode')
            ->withNoArgs()
            ->andReturn(SpacecraftSystemModeEnum::MODE_ON);
        $filledShieldSystem->shouldReceive('setMode')
            ->with(SpacecraftSystemModeEnum::MODE_OFF);
        $filledShieldSystem->shouldReceive('getStatus')
            ->withNoArgs()
            ->andReturn(90);

        $this->messageFactory->shouldReceive('createMessageCollection')
            ->withNoArgs()
            ->twice()
            ->andReturn($shipsMessageCollecton, $basesMessageCollecton);

        $basesMessageCollecton->shouldReceive('addMessageBy')
            ->with('DEPLETED_SHIELDS_NAME', 111)
            ->andReturn($depletedMessage);
        $shipsMessageCollecton->shouldReceive('addMessageBy')
            ->with('FILLED_SHIELDS_NAME', 222)
            ->andReturn($filledMessage);

        $filledMessage->shouldReceive('add')
            ->with('- die Schilde wurden entladen');
        $filledMessage->shouldReceive('addMessageMerge')
            ->with('DAMAGE_INFOS_222');

        $depletedMessage->shouldReceive('addMessageMerge')
            ->with('DAMAGE_INFOS_111');

        $this->spacecraftWrapperFactory->shouldReceive('wrapSpacecraft')
            ->with($spacecraftWithDepletedShields)
            ->andReturn($wrapperWithDepletedShields);
        $this->spacecraftWrapperFactory->shouldReceive('wrapSpacecraft')
            ->with($spacecraftWithFilledShields)
            ->andReturn($wrapperWithFilledShields);

        $this->stuRandom->shouldReceive('rand')
            ->with(1, 50, true)
            ->twice()
            ->andReturn(11, 22);

        $this->systemDamage->shouldReceive('damageShipSystem')
            ->with(
                $wrapperWithDepletedShields,
                $depletedShieldSystem,
                11,
                $depletedMessage
            );
        $this->systemDamage->shouldReceive('damageShipSystem')
            ->with(
                $wrapperWithFilledShields,
                $filledShieldSystem,
                22,
                $filledMessage
            );

        $shipsMessageCollecton->shouldReceive('add')
            ->with($filledMessage);
        $shipsMessageCollecton->shouldReceive('isEmpty')
            ->withNoArgs()
            ->andReturn(false);

        $basesMessageCollecton->shouldReceive('add')
            ->with($depletedMessage);
        $basesMessageCollecton->shouldReceive('isEmpty')
            ->withNoArgs()
            ->andReturn(false);

        $this->spacecraftRepository->shouldReceive('save')
            ->with($spacecraftWithDepletedShields);
        $this->spacecraftRepository->shouldReceive('save')
            ->with($spacecraftWithFilledShields);

        $this->distributedMessageSender->shouldReceive('distributeMessageCollection')
            ->with(
                $shipsMessageCollecton,
                UserConstants::USER_NOONE,
                PrivateMessageFolderTypeEnum::SPECIAL_SHIP,
                "[b][color=red]Subraumellipse in Sektor SECTOR[/color][/b]"
            );
        $this->distributedMessageSender->shouldReceive('distributeMessageCollection')
            ->with(
                $basesMessageCollecton,
                UserConstants::USER_NOONE,
                PrivateMessageFolderTypeEnum::SPECIAL_STATION,
                "[b][color=red]Subraumellipse in Sektor SECTOR[/color][/b]"
            );

        $this->subject->handleSpacecraftTick($this->anomaly);
    }
}
