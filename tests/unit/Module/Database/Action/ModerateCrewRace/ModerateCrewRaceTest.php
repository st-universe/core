<?php

declare(strict_types=1);

namespace Stu\Module\Database\Action\ModerateCrewRace;

use request;
use Stu\ActionControllerTestCase;
use Stu\Module\Database\View\ShowCrewRaceModeration\ShowCrewRaceModeration;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Entity\CrewRace;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\CrewRaceRepositoryInterface;
use Stu\Orm\Repository\UserCrewRaceRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class ModerateCrewRaceTest extends ActionControllerTestCase
{
    public function testRejectsWithReasonAndSendsSystemMessage(): void
    {
        $races = $this->mock(CrewRaceRepositoryInterface::class);
        $assignments = $this->mock(UserCrewRaceRepositoryInterface::class);
        $users = $this->mock(UserRepositoryInterface::class);
        $sender = $this->mock(PrivateMessageSenderInterface::class);
        $moderator = $this->mock(User::class);
        $race = new CrewRace()->setCreatorUserId(42)->setDescription('Test');
        request::setMockVars(['crew_race_id' => 5, 'decision' => 'reject', 'rejection_reason' => "  Bitte transparente Bilder verwenden.\nRahmen korrigieren.  "]);
        $this->game->shouldReceive('setView')->with(ShowCrewRaceModeration::VIEW_IDENTIFIER)->once();
        $this->game->shouldReceive('getUser')->once()->andReturn($moderator);
        $moderator->shouldReceive('getId')->once()->andReturn(7);
        $races->shouldReceive('find')->with(5)->once()->andReturn($race);
        $races->shouldReceive('save')->with($race)->once();
        $users->shouldReceive('find')->with(42)->once()->andReturn(new User());
        $sender->shouldReceive('send')->with(
            UserConstants::USER_NOONE,
            42,
            "Deine Crew-Rasse Test wurde abgelehnt\n\nBegründung: Bitte transparente Bilder verwenden.\nRahmen korrigieren.\n\nDu kannst sie unter Optionen > Crew-Rassen überarbeiten und erneut einreichen",
            PrivateMessageFolderTypeEnum::SPECIAL_SYSTEM,
            'options.php?SHOW_CREW_RACE_MANAGEMENT=1'
        )->once();
        $this->game->shouldReceive('getInfo->addInformation')->with('Die Crew-Rasse wurde abgelehnt')->once();

        new ModerateCrewRace($races, $assignments, $users, $sender)->handle($this->game);

        self::assertTrue($race->isRejected());
        self::assertSame(7, $race->getAcceptedUserId());
        self::assertSame("Bitte transparente Bilder verwenden.\nRahmen korrigieren.", $race->getRejectionReason());
    }

    public function testRejectsOverlongReasonWithoutSaving(): void
    {
        $races = $this->mock(CrewRaceRepositoryInterface::class);
        $race = new CrewRace()->setCreatorUserId(42);
        $races->shouldReceive('find')->with(5)->once()->andReturn($race);
        request::setMockVars(['crew_race_id' => 5, 'decision' => 'reject', 'rejection_reason' => str_repeat('ä', 2001)]);
        $this->game->shouldReceive('setView')->once();
        $this->game->shouldReceive('getInfo->addInformation')->with('Der Ablehnungsgrund darf höchstens 2000 Zeichen enthalten')->once();
        new ModerateCrewRace(
            $races,
            $this->mock(UserCrewRaceRepositoryInterface::class),
            $this->mock(UserRepositoryInterface::class),
            $this->mock(PrivateMessageSenderInterface::class)
        )->handle($this->game);
        self::assertNull($race->getAcceptedUserId());
        self::assertNull($race->getRejectionReason());
    }
}
