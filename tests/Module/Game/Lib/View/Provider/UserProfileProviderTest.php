<?php

declare(strict_types=1);

namespace Stu\Module\Game\Lib\View\Provider;

use Override;
use Stu\Component\Game\GameEnum;
use JBBCode\Parser;
use Mockery\MockInterface;
use request;
use Stu\Lib\ParserWithImageInterface;
use Stu\Module\Control\Exception\ItemNotFoundException;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\ContactListModeEnum;
use Stu\Module\PlayerProfile\Lib\ProfileVisitorRegistrationInterface;
use Stu\Orm\Entity\ContactInterface;
use Stu\Orm\Entity\RpgPlotMemberInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Entity\ColonyScanInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Repository\ContactRepositoryInterface;
use Stu\Orm\Repository\RpgPlotMemberRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\StuTestCase;

class UserProfileProviderTest extends StuTestCase
{
    /** @var MockInterface&RpgPlotMemberRepositoryInterface */
    private MockInterface $rpgPlotMemberRepository;

    /** @var MockInterface&ContactRepositoryInterface */
    private MockInterface $contactRepository;

    /** @var MockInterface&UserRepositoryInterface */
    private MockInterface $userRepository;

    /** @var MockInterface&ParserWithImageInterface */
    private MockInterface $parserWithImage;

    /** @var MockInterface&ProfileVisitorRegistrationInterface */
    private MockInterface $profileVisitorRegistration;

    private ViewComponentProviderInterface $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->rpgPlotMemberRepository = $this->mock(RpgPlotMemberRepositoryInterface::class);
        $this->contactRepository = $this->mock(ContactRepositoryInterface::class);
        $this->userRepository = $this->mock(UserRepositoryInterface::class);
        $this->parserWithImage = $this->mock(ParserWithImageInterface::class);
        $this->profileVisitorRegistration = $this->mock(ProfileVisitorRegistrationInterface::class);

        $this->subject = new UserProfileProvider(
            $this->rpgPlotMemberRepository,
            $this->contactRepository,
            $this->userRepository,
            $this->parserWithImage,
            $this->profileVisitorRegistration
        );
    }

    public function testHandleThrowsIfPlayerDoesNotExist(): void
    {
        $playerId = 666;

        $game = $this->mock(GameControllerInterface::class);

        static::expectException(ItemNotFoundException::class);

        request::setMockVars(['uid' => $playerId]);

        $this->userRepository->shouldReceive('find')
            ->with($playerId)
            ->once()
            ->andReturnNull();

        $this->subject->setTemplateVariables($game);
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
        $colonyScan = $this->mock(ColonyScanInterface::class);
        $this->mock(ColonyInterface::class);

        request::setMockVars(['uid' => $playerId]);

        $this->userRepository->shouldReceive('find')
            ->with($playerId)
            ->once()
            ->andReturn($player);

        $game->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($visitor);
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
        $game->shouldReceive('setTemplateVar')
            ->with('CONTACT_LIST_MODES', ContactListModeEnum::cases())
            ->once();

        $visitor->shouldReceive('getId')
            ->withNoArgs()
            ->atLeast()->once()
            ->andReturn($visitorId);
        $visitor->shouldReceive('getAlliance')
            ->withNoArgs()
            ->once()
            ->andReturn(null);
        $visitor->shouldReceive('getColonyScans->toArray')
            ->withNoArgs()
            ->once()
            ->andReturn([123 => $colonyScan]);

        $player->shouldReceive('getDescription')
            ->withNoArgs()
            ->once()
            ->andReturn($description);
        $player->shouldReceive('getAlliance')
            ->withNoArgs()
            ->once()
            ->andReturnNull();
        $player->shouldReceive('getId')
            ->withNoArgs()
            ->atLeast()->once()
            ->andReturn($playerId);

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

        $game->shouldReceive('addExecuteJS')
            ->with("initTranslations();", GameEnum::JS_EXECUTION_AFTER_RENDER)
            ->once();

        $colonyScan->shouldReceive('getColonyUserId')
            ->withNoArgs()
            ->once()
            ->andReturn($playerId);

        $game->shouldReceive('setTemplateVar')
            ->with('COLONYSCANLIST', [123 => $colonyScan])
            ->once();

        $this->subject->setTemplateVariables($game);
    }
}
