<?php

declare(strict_types=1);

namespace Stu\Module\Game\Lib\View\Provider;

use Mockery;
use JBBCode\Parser;
use Mockery\MockInterface;
use Override;
use request;
use Stu\Component\Game\GameEnum;
use Stu\Lib\ParserWithImageInterface;
use Stu\Module\Control\Exception\ItemNotFoundException;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\ContactListModeEnum;
use Stu\Module\PlayerProfile\Lib\ProfileVisitorRegistrationInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ColonyScan;
use Stu\Orm\Entity\Contact;
use Stu\Orm\Entity\RpgPlotMember;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\ContactRepositoryInterface;
use Stu\Orm\Repository\RpgPlotMemberRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\StuTestCase;

class UserProfileProviderTest extends StuTestCase
{
    private MockInterface&RpgPlotMemberRepositoryInterface $rpgPlotMemberRepository;

    private MockInterface&ContactRepositoryInterface $contactRepository;

    private MockInterface&UserRepositoryInterface $userRepository;

    private MockInterface&ParserWithImageInterface $parserWithImage;

    private MockInterface&ProfileVisitorRegistrationInterface $profileVisitorRegistration;

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
        $colonyId = 123;
        $parsedDescription = 'some-parsed-description';
        $description = 'some-description';

        $game = $this->mock(GameControllerInterface::class);
        $player = $this->mock(User::class);
        $visitor = $this->mock(User::class);
        $plotMember = $this->mock(RpgPlotMember::class);
        $contact = $this->mock(Contact::class);
        $friend = $this->mock(User::class);
        $bbCodeParser = $this->mock(Parser::class);
        $colonyScan = $this->mock(ColonyScan::class);
        $colony = $this->mock(Colony::class);
        $this->mock(Colony::class);

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
            ->with('HAS_TRANSLATION', false)
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

        $colonyScan->shouldReceive('getColony')
            ->withNoArgs()
            ->once()
            ->andReturn($colony);

        $colony->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($colonyId);

        $game->shouldReceive('setTemplateVar')
            ->with('COLONYSCANLIST', Mockery::on(function ($arg) use ($colonyScan): bool {
                return is_array($arg) && count($arg) === 1 && $arg[0] === $colonyScan;
            }))
            ->once();

        $this->subject->setTemplateVariables($game);
    }
}
