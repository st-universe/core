<?php

declare(strict_types=1);

namespace Stu\Module\PlayerProfile\View\Overview;

use JBBCode\Parser;
use Mockery\MockInterface;
use Stu\Lib\ParserWithImageInterface;
use Stu\Module\Control\Exception\ItemNotFoundException;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\PlayerProfile\Lib\ProfileVisitorRegistrationInterface;
use Stu\Orm\Entity\ContactInterface;
use Stu\Orm\Entity\RpgPlotMemberInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ContactRepositoryInterface;
use Stu\Orm\Repository\RpgPlotMemberRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\StuTestCase;

class OverviewTest extends StuTestCase
{
    /** @var MockInterface&RpgPlotMemberRepositoryInterface */
    private MockInterface $rpgPlotMemberRepository;

    /** @var MockInterface&ContactRepositoryInterface */
    private MockInterface $contactRepository;

    /** @var MockInterface&UserRepositoryInterface */
    private MockInterface $userRepository;

    /** @var MockInterface&ParserWithImageInterface */
    private MockInterface $parserWithImage;

    /** @var MockInterface&OverviewRequestInterface */
    private MockInterface $overviewRequest;

    /** @var MockInterface&ProfileVisitorRegistrationInterface */
    private MockInterface $profileVisitorRegistration;

    private Overview $subject;

    protected function setUp(): void
    {
        $this->rpgPlotMemberRepository = $this->mock(RpgPlotMemberRepositoryInterface::class);
        $this->contactRepository = $this->mock(ContactRepositoryInterface::class);
        $this->userRepository = $this->mock(UserRepositoryInterface::class);
        $this->parserWithImage = $this->mock(ParserWithImageInterface::class);
        $this->overviewRequest = $this->mock(OverviewRequestInterface::class);
        $this->profileVisitorRegistration = $this->mock(ProfileVisitorRegistrationInterface::class);

        $this->subject = new Overview(
            $this->rpgPlotMemberRepository,
            $this->contactRepository,
            $this->userRepository,
            $this->parserWithImage,
            $this->overviewRequest,
            $this->profileVisitorRegistration
        );
    }

    public function testHandleThrowsIfPlayerDoesNotExist(): void
    {
        $playerId = 666;

        $game = $this->mock(GameControllerInterface::class);

        static::expectException(ItemNotFoundException::class);

        $this->overviewRequest->shouldReceive('getPlayerId')
            ->withNoArgs()
            ->once()
            ->andReturn($playerId);

        $this->userRepository->shouldReceive('find')
            ->with($playerId)
            ->once()
            ->andReturnNull();

        $this->subject->handle($game);
    }

    public function testHandleRenders(): void
    {
        $playerId = 666;
        $visitorId = 42;
        $parsedDescription = 'some-parsed-description';
        $description = 'some-description';

        $game = $this->mock(GameControllerInterface::class);
        $player = $this->mock(UserInterface::class);
        $visitor = $this->mock(UserInterface::class);
        $plotMember = $this->mock(RpgPlotMemberInterface::class);
        $contact = $this->mock(ContactInterface::class);
        $friend = $this->mock(UserInterface::class);
        $bbCodeParser = $this->mock(Parser::class);

        $this->overviewRequest->shouldReceive('getPlayerId')
            ->withNoArgs()
            ->once()
            ->andReturn($playerId);

        $this->userRepository->shouldReceive('find')
            ->with($playerId)
            ->once()
            ->andReturn($player);

        $game->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($visitor);
        $game->shouldReceive('setTemplateFile')
            ->with('html/userprofile.xhtml')
            ->once();
        $game->shouldReceive('setPageTitle')
            ->with('Spielerprofil')
            ->once();
        $game->shouldReceive('appendNavigationPart')
            ->with(
                sprintf('userprofile.php?uid=%d', $playerId),
                'Spielerprofil'
            )
            ->once();
        $game->shouldReceive('setTemplateVar')
            ->with('PROFILE', $player)
            ->once();
        $game->shouldReceive('setTemplateVar')
            ->with(
                'DESCRIPTION',
                $parsedDescription
            )
            ->once();
        $game->shouldReceive('setTemplateVar')
            ->with('IS_PROFILE_CURRENT_USER', false)
            ->once()
            ->andReturnFalse();
        $game->shouldReceive('setTemplateVar')
            ->with('RPG_PLOTS', [$plotMember])
            ->once();
        $game->shouldReceive('setTemplateVar')
            ->with('CONTACT', $contact)
            ->once();
        $game->shouldReceive('setTemplateVar')
            ->with('FRIENDS', [$friend])
            ->once();

        $visitor->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($visitorId);

        $player->shouldReceive('getDescription')
            ->withNoArgs()
            ->once()
            ->andReturn($description);
        $player->shouldReceive('getAlliance')
            ->withNoArgs()
            ->once()
            ->andReturnNull();

        $this->profileVisitorRegistration->shouldReceive('register')
            ->with($player, $visitor)
            ->once();

        $this->parserWithImage->shouldReceive('parse')
            ->with($description)
            ->once()
            ->andReturn($bbCodeParser);

        $bbCodeParser->shouldReceive('getAsHTML')
            ->withNoArgs()
            ->once()
            ->andReturn($parsedDescription);

        $this->rpgPlotMemberRepository->shouldReceive('getByUser')
            ->with($player)
            ->once()
            ->andReturn([$plotMember]);

        $this->contactRepository->shouldReceive('getByUserAndOpponent')
            ->with($visitorId, $playerId)
            ->once()
            ->andReturn($contact);

        $this->userRepository->shouldReceive('getFriendsByUserAndAlliance')
            ->with($player, null)
            ->once()
            ->andReturn([$friend]);

        $this->subject->handle($game);
    }
}
