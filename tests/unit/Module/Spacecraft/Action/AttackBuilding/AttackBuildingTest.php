<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\AttackBuilding;

use Mockery\MockInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use request;
use Stu\ActionControllerTestCase;
use Stu\Component\Colony\ColonyFunctionManagerInterface;
use Stu\Component\Player\Relation\PlayerRelationDeterminatorInterface;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Colony\Lib\PlanetFieldTypeRetrieverInterface;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Spacecraft\Lib\Battle\AlertDetection\AlertReactionFacadeInterface;
use Stu\Module\Spacecraft\Lib\Battle\Party\BattlePartyFactoryInterface;
use Stu\Module\Spacecraft\Lib\Battle\Provider\AttackerProviderFactoryInterface;
use Stu\Module\Spacecraft\Lib\Battle\Weapon\EnergyWeaponPhaseInterface;
use Stu\Module\Spacecraft\Lib\Battle\Weapon\ProjectileWeaponPhaseInterface;
use Stu\Module\Spacecraft\Lib\Interaction\InteractionCheckerInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageFactoryInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\PlanetField;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class AttackBuildingTest extends ActionControllerTestCase
{
    /** @var MockInterface&SpacecraftLoaderInterface<SpacecraftWrapperInterface> */
    private MockInterface&SpacecraftLoaderInterface $spacecraftLoader;
    private MockInterface&PlanetFieldRepositoryInterface $planetFieldRepository;
    private MockInterface&ColonyRepositoryInterface $colonyRepository;
    private MockInterface&PrivateMessageSenderInterface $privateMessageSender;

    private AttackBuilding $subject;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->spacecraftLoader = $this->mock(SpacecraftLoaderInterface::class);
        $this->planetFieldRepository = $this->mock(PlanetFieldRepositoryInterface::class);
        $this->colonyRepository = $this->mock(ColonyRepositoryInterface::class);
        $this->privateMessageSender = $this->mock(PrivateMessageSenderInterface::class);

        $this->subject = new AttackBuilding(
            $this->spacecraftLoader,
            $this->planetFieldRepository,
            $this->colonyRepository,
            $this->mock(InteractionCheckerInterface::class),
            $this->mock(EnergyWeaponPhaseInterface::class),
            $this->mock(ProjectileWeaponPhaseInterface::class),
            $this->privateMessageSender,
            $this->mock(AlertReactionFacadeInterface::class),
            $this->mock(PlanetFieldTypeRetrieverInterface::class),
            $this->mock(ColonyFunctionManagerInterface::class),
            $this->mock(AttackerProviderFactoryInterface::class),
            $this->mock(BattlePartyFactoryInterface::class),
            $this->mock(MessageFactoryInterface::class),
            $this->mock(PlayerRelationDeterminatorInterface::class),
            $this->mock(EventDispatcherInterface::class)
        );
    }

    public function testHandleAcquiresSemaphoreForTargetColonyOwner(): void
    {
        request::setMockVars([
            'id' => 11,
            'colonyid' => 22,
            'field' => 33
        ]);

        $user = $this->mock(User::class);
        $wrapper = $this->mock(SpacecraftWrapperInterface::class);
        $colony = $this->mock(Colony::class);
        $field = $this->mock(PlanetField::class);
        $information = $this->mock(InformationWrapper::class);

        $this->game->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);
        $this->game->shouldReceive('setView')
            ->with(ShowSpacecraft::VIEW_IDENTIFIER)
            ->once();
        $this->game->shouldReceive('getInfo')
            ->withNoArgs()
            ->once()
            ->andReturn($information);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(44);
        $this->colonyRepository->shouldReceive('find')
            ->with(22)
            ->once()
            ->andReturn($colony);
        $this->colonyRepository->shouldReceive('getUserIdOfColony')
            ->with(22)
            ->once()
            ->andReturn(55);
        $this->spacecraftLoader->shouldReceive('getWrapperByIdAndUserAndTargetUser')
            ->with(11, 44, 55)
            ->once()
            ->andReturn($wrapper);
        $this->planetFieldRepository->shouldReceive('find')
            ->with(33)
            ->once()
            ->andReturn($field);
        $field->shouldReceive('getFieldId')
            ->withNoArgs()
            ->once()
            ->andReturn(66);
        $field->shouldReceive('getBuilding')
            ->withNoArgs()
            ->once()
            ->andReturnNull();
        $information->shouldReceive('addInformation')
            ->with('Gebäude nicht vorhanden')
            ->once();
        $this->privateMessageSender->shouldReceive('send')
            ->never();

        $this->subject->handle($this->game);
    }
}
