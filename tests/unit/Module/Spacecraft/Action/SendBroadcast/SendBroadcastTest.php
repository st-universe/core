<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\SendBroadcast;

use Mockery\MockInterface;
use request;
use Stu\ActionControllerTestCase;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\View\ShowBroadcastResponse\ShowBroadcastResponse;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\Station;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\StationRepositoryInterface;

class SendBroadcastTest extends ActionControllerTestCase
{
    private MockInterface&SpacecraftLoaderInterface $spacecraftLoader;
    private MockInterface&ColonyRepositoryInterface $colonyRepository;
    private MockInterface&StationRepositoryInterface $stationRepository;
    private MockInterface&PrivateMessageSenderInterface $privateMessageSender;

    private SendBroadcast $subject;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->spacecraftLoader = $this->mock(SpacecraftLoaderInterface::class);
        $this->colonyRepository = $this->mock(ColonyRepositoryInterface::class);
        $this->stationRepository = $this->mock(StationRepositoryInterface::class);
        $this->privateMessageSender = $this->mock(PrivateMessageSenderInterface::class);

        $this->subject = new SendBroadcast(
            $this->spacecraftLoader,
            $this->colonyRepository,
            $this->stationRepository,
            $this->privateMessageSender
        );
    }

    public function testHandleShowsNoTargetsMessageInPopupResponse(): void
    {
        request::setMockVars([
            'id' => 42,
            'text' => 'Broadcast text',
            'broadcastPopup' => 1
        ]);

        $info = $this->mock(InformationWrapper::class);
        $user = $this->mock(User::class);
        $ship = $this->mock(Spacecraft::class);

        $this->game->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);
        $this->game->shouldReceive('getInfo')
            ->withNoArgs()
            ->once()
            ->andReturn($info);
        $this->game->shouldReceive('setView')
            ->with(ShowBroadcastResponse::VIEW_IDENTIFIER)
            ->once();
        $this->game->shouldReceive('setTemplateVar')
            ->with('BROADCAST_MESSAGE', 'Keine Ziele in Reichweite')
            ->once();

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(101);

        $this->spacecraftLoader->shouldReceive('getByIdAndUser')
            ->with(42, 101)
            ->once()
            ->andReturn($ship);

        $ship->shouldReceive('getStarsystemMap')
            ->withNoArgs()
            ->once()
            ->andReturnNull();

        $this->colonyRepository->shouldReceive('getForeignColoniesInBroadcastRange')
            ->never();
        $this->stationRepository->shouldReceive('getForeignStationsInBroadcastRange')
            ->with($ship)
            ->once()
            ->andReturn([]);
        $this->privateMessageSender->shouldReceive('sendBroadcast')
            ->never();

        $info->shouldReceive('addInformation')
            ->with('Keine Ziele in Reichweite')
            ->once();

        $this->subject->handle($this->game);
    }

    public function testHandleRendersSpacecraftPageAfterSuccessfulPopupBroadcast(): void
    {
        request::setMockVars([
            'id' => 42,
            'text' => 'Broadcast text',
            'broadcastPopup' => 1
        ]);

        $info = $this->mock(InformationWrapper::class);
        $user = $this->mock(User::class);
        $sender = $this->mock(User::class);
        $recipient = $this->mock(User::class);
        $ship = $this->mock(Spacecraft::class);
        $station = $this->mock(Station::class);

        $this->game->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);
        $this->game->shouldReceive('getInfo')
            ->withNoArgs()
            ->once()
            ->andReturn($info);
        $this->game->shouldReceive('setView')
            ->with(ShowSpacecraft::VIEW_IDENTIFIER)
            ->once();

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(101);

        $this->spacecraftLoader->shouldReceive('getByIdAndUser')
            ->with(42, 101)
            ->once()
            ->andReturn($ship);

        $ship->shouldReceive('getStarsystemMap')
            ->withNoArgs()
            ->once()
            ->andReturnNull();
        $ship->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($sender);

        $station->shouldReceive('getUser')
            ->withNoArgs()
            ->twice()
            ->andReturn($recipient);

        $this->colonyRepository->shouldReceive('getForeignColoniesInBroadcastRange')
            ->never();
        $this->stationRepository->shouldReceive('getForeignStationsInBroadcastRange')
            ->with($ship)
            ->once()
            ->andReturn([$station]);
        $this->privateMessageSender->shouldReceive('sendBroadcast')
            ->with($sender, [$recipient], 'Broadcast text')
            ->once();

        $info->shouldReceive('addInformation')
            ->with('Der Broadcast wurde erfolgreich versendet')
            ->once();

        $this->subject->handle($this->game);
    }
}
