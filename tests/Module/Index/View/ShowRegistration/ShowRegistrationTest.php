<?php

declare(strict_types=1);

namespace Stu\Module\Index\View\ShowRegistration;

use Override;
use Mockery\MockInterface;
use Noodlehaus\ConfigInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Index\Lib\FactionItem;
use Stu\Module\Index\Lib\UiItemFactoryInterface;
use Stu\Orm\Entity\FactionInterface;
use Stu\Orm\Repository\FactionRepositoryInterface;
use Stu\StuTestCase;

class ShowRegistrationTest extends StuTestCase
{
    /** @var MockInterface&ShowRegistrationRequestInterface */
    private MockInterface $showRegistrationRequest;

    /** @var MockInterface&FactionRepositoryInterface */
    private MockInterface $factionRepository;

    /** @var MockInterface&ConfigInterface */
    private MockInterface $config;

    /** @var MockInterface&UiItemFactoryInterface */
    private MockInterface $uiItemFactory;

    private ShowRegistration $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->showRegistrationRequest = $this->mock(ShowRegistrationRequestInterface::class);
        $this->factionRepository = $this->mock(FactionRepositoryInterface::class);
        $this->config = $this->mock(ConfigInterface::class);
        $this->uiItemFactory = $this->mock(UiItemFactoryInterface::class);

        $this->subject = new ShowRegistration(
            $this->showRegistrationRequest,
            $this->factionRepository,
            $this->uiItemFactory,
            $this->config
        );
    }

    public function testHandleRenders(): void
    {
        $game = $this->mock(GameControllerInterface::class);
        $faction = $this->mock(FactionInterface::class);
        $factionItem = $this->mock(FactionItem::class);

        $token = 'some-token';
        $wikiBaseUrl = 'some-url';
        $playerCount = 666;

        $this->uiItemFactory->shouldReceive('createFactionItem')
            ->with($faction, $playerCount)
            ->once()
            ->andReturn($factionItem);

        $this->factionRepository->shouldReceive('getPlayableFactionsPlayerCount')
            ->withNoArgs()
            ->once()
            ->andReturn([['faction' => $faction, 'count' => $playerCount]]);

        $game->shouldReceive('setPageTitle')
            ->with('Registrierung - Star Trek Universe')
            ->once();
        $game->shouldReceive('setTemplateFile')
            ->with('html/registration.xhtml')
            ->once();
        $game->shouldReceive('setTemplateVar')
            ->with('REGISTRATION_POSSIBLE', true)
            ->once();
        $game->shouldReceive('setTemplateVar')
            ->with('IS_SMS_REGISTRATION', true)
            ->once();
        $game->shouldReceive('setTemplateVar')
            ->with('TOKEN', $token)
            ->once();
        $game->shouldReceive('setTemplateVar')
            ->with('WIKI', $wikiBaseUrl)
            ->once();
        $game->shouldReceive('setTemplateVar')
            ->with('POSSIBLE_FACTIONS', [$factionItem])
            ->once();

        $this->config->shouldReceive('get')
            ->with('game.registration.enabled')
            ->once()
            ->andReturnTrue();
        $this->config->shouldReceive('get')
            ->with('game.registration.sms_code_verification.enabled')
            ->once()
            ->andReturnTrue();
        $this->config->shouldReceive('get')
            ->with('wiki.base_url')
            ->once()
            ->andReturn($wikiBaseUrl);

        $this->showRegistrationRequest->shouldReceive('getToken')
            ->withNoArgs()
            ->once()
            ->andReturn($token);

        $this->subject->handle($game);
    }
}
