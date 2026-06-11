<?php

declare(strict_types=1);

namespace Stu\Component\Anomaly\Type\IonStorm;

use Doctrine\Common\Collections\ArrayCollection;
use JsonMapper\JsonMapperInterface;
use Mockery\MockInterface;
use Stu\Component\Anomaly\AnomalyCreationInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Lib\Information\InformationFactoryInterface;
use Stu\Module\Control\StuRandom;
use Stu\Module\Control\StuTime;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Spacecraft\Lib\Damage\ApplyDamageInterface;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestructionInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageFactoryInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Entity\Anomaly;
use Stu\Orm\Repository\AnomalyRepositoryInterface;
use Stu\Orm\Repository\LayerRepositoryInterface;
use Stu\Orm\Repository\LocationRepositoryInterface;
use Stu\StuTestCase;

class IonStormHandlerTest extends StuTestCase
{
    private MockInterface&AnomalyRepositoryInterface $anomalyRepository;
    private MockInterface&LocationRepositoryInterface $locationRepository;
    private MockInterface&LayerRepositoryInterface $layerRepository;
    private MockInterface&AnomalyCreationInterface $anomalyCreation;
    private MockInterface&LocationPoolFactory $locationPoolFactory;
    private MockInterface&IonStormPropagation $ionStormPropagation;
    private MockInterface&IonStormMovement $ionStormMovement;
    private MockInterface&SpacecraftSystemManagerInterface $spacecraftSystemManager;
    private MockInterface&ApplyDamageInterface $applyDamage;
    private MockInterface&SpacecraftDestructionInterface $spacecraftDestruction;
    private MockInterface&SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory;
    private MockInterface&PrivateMessageSenderInterface $privateMessageSender;
    private MockInterface&MessageFactoryInterface $messageFactory;
    private MockInterface&JsonMapperInterface $jsonMapper;
    private MockInterface&InformationFactoryInterface $informationFactory;
    private MockInterface&StuRandom $stuRandom;
    private MockInterface&StuTime $stuTime;

    private IonStormHandler $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->anomalyRepository = $this->mock(AnomalyRepositoryInterface::class);
        $this->locationRepository = $this->mock(LocationRepositoryInterface::class);
        $this->layerRepository = $this->mock(LayerRepositoryInterface::class);
        $this->anomalyCreation = $this->mock(AnomalyCreationInterface::class);
        $this->locationPoolFactory = $this->mock(LocationPoolFactory::class);
        $this->ionStormPropagation = $this->mock(IonStormPropagation::class);
        $this->ionStormMovement = $this->mock(IonStormMovement::class);
        $this->spacecraftSystemManager = $this->mock(SpacecraftSystemManagerInterface::class);
        $this->applyDamage = $this->mock(ApplyDamageInterface::class);
        $this->spacecraftDestruction = $this->mock(SpacecraftDestructionInterface::class);
        $this->spacecraftWrapperFactory = $this->mock(SpacecraftWrapperFactoryInterface::class);
        $this->privateMessageSender = $this->mock(PrivateMessageSenderInterface::class);
        $this->messageFactory = $this->mock(MessageFactoryInterface::class);
        $this->jsonMapper = $this->mock(JsonMapperInterface::class);
        $this->informationFactory = $this->mock(InformationFactoryInterface::class);
        $this->stuRandom = $this->mock(StuRandom::class);
        $this->stuTime = $this->mock(StuTime::class);

        $this->subject = new IonStormHandler(
            $this->anomalyRepository,
            $this->locationRepository,
            $this->layerRepository,
            $this->anomalyCreation,
            $this->locationPoolFactory,
            $this->ionStormPropagation,
            $this->ionStormMovement,
            $this->spacecraftSystemManager,
            $this->applyDamage,
            $this->spacecraftDestruction,
            $this->spacecraftWrapperFactory,
            $this->privateMessageSender,
            $this->messageFactory,
            $this->jsonMapper,
            $this->informationFactory,
            $this->stuRandom,
            $this->stuTime
        );
    }

    public function testHandleSpacecraftTickSkipsDamageForStormsExpiringThisTick(): void
    {
        $root = $this->mock(Anomaly::class);
        $child = $this->mock(Anomaly::class);
        $locationPool = $this->mock(LocationPool::class);
        $ionStormData = new IonStormData();

        $root->shouldReceive('getData')
            ->withNoArgs()
            ->once()
            ->andReturn('{}');
        $root->shouldReceive('getChildren')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$child]));

        $child->shouldReceive('getRemainingTicks')
            ->withNoArgs()
            ->once()
            ->andReturn(1);
        $child->shouldNotReceive('getLocation');

        $this->jsonMapper->shouldReceive('mapObjectFromString')
            ->with('{}', \Mockery::type(IonStormData::class))
            ->once()
            ->andReturn($ionStormData);
        $this->locationPoolFactory->shouldReceive('createLocationPool')
            ->with($root, 1)
            ->once()
            ->andReturn($locationPool);
        $this->ionStormMovement->shouldReceive('moveStorm')
            ->with($root, $ionStormData, $locationPool)
            ->once();
        $this->ionStormPropagation->shouldReceive('propagateStorm')
            ->with($root, $locationPool)
            ->once();

        $this->informationFactory->shouldNotReceive('createInformationWrapper');
        $this->spacecraftWrapperFactory->shouldNotReceive('wrapSpacecraft');
        $this->applyDamage->shouldNotReceive('damage');
        $this->privateMessageSender->shouldNotReceive('send');

        $this->subject->handleSpacecraftTick($root);
    }
}
