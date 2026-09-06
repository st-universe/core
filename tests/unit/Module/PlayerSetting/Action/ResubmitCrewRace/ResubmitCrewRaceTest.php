<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting\Action\ResubmitCrewRace;

use Noodlehaus\ConfigInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionProperty;
use request;
use Stu\ActionControllerTestCase;
use Stu\Component\Crew\CrewRaceGraphics;
use Stu\Component\Crew\CrewRaceSubmissionNotifier;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\CrewRaceSubmission;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Entity\CrewRace;
use Stu\Orm\Entity\Faction;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\CrewRaceRepositoryInterface;
use Stu\Orm\Repository\FactionRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class ResubmitCrewRaceTest extends ActionControllerTestCase
{
    public static function forbiddenRaceProvider(): array
    {
        return [
            'missing' => [null],
            'foreign rejected' => [new CrewRace()->setCreatorUserId(99)->setAcceptedUserId(7)],
            'own pending' => [new CrewRace()->setCreatorUserId(42)],
            'own accepted' => [new CrewRace()->setCreatorUserId(42)->setAccepted(true)->setAcceptedUserId(7)]
        ];
    }

    #[DataProvider('forbiddenRaceProvider')]
    public function testCannotResubmitForeignOrNonRejectedRace(?CrewRace $race): void
    {
        $races = $this->mock(CrewRaceRepositoryInterface::class);
        $races->shouldReceive('find')->with(5)->once()->andReturn($race);
        $user = $this->mock(User::class);
        $user->shouldReceive('getId')->andReturn(42);
        $this->game->shouldReceive('getUser')->once()->andReturn($user);
        $this->game->shouldReceive('setView')->once();
        $this->game->shouldReceive('getInfo->addInformation')->with('Nur eigene abgelehnte Crew-Rassen können erneut eingereicht werden')->once();
        request::setMockVars(['crew_race_id' => 5]);
        new ResubmitCrewRace(new CrewRaceSubmission(
            $races,
            $this->mock(FactionRepositoryInterface::class),
            new CrewRaceGraphics($this->mock(ConfigInterface::class)),
            new CrewRaceSubmissionNotifier(
                $this->mock(StuConfigInterface::class),
                $this->mock(UserRepositoryInterface::class),
                $this->mock(PrivateMessageSenderInterface::class)
            )
        ))->handle($this->game);
    }

    public function testResubmitsSameRaceWithAllSettingsAndExistingGraphics(): void
    {
        $directory = sys_get_temp_dir() . '/stu-crew-resubmit-' . bin2hex(random_bytes(8));
        mkdir($directory . '/crew/OLD/m', 0o755, true);
        foreach (range(1, 6) as $imageType) {
            file_put_contents($directory . '/crew/OLD/m/1_' . $imageType . '.png', 'image-' . $imageType);
        }
        $_FILES = [];
        try {
            $race = new CrewRace()->setCreatorUserId(42)->setAcceptedUserId(7)
                ->setRejectionReason('Bitte überarbeiten')->setGfxPath('OLD')->setDescription('Alt')
                ->setMaleRatio(100)->setChance(25)->setFactionIds([1]);
            new ReflectionProperty(CrewRace::class, 'id')->setValue($race, 5);
            $races = $this->mock(CrewRaceRepositoryInterface::class);
            $races->shouldReceive('find')->with(5)->once()->andReturn($race);
            $races->shouldReceive('getByGfxPath')->with('NEW')->once()->andReturn(null);
            $races->shouldReceive('save')->with($race)->once();
            // Neither a new entity nor the three-race quota is consulted for resubmission.
            $factions = $this->mock(FactionRepositoryInterface::class);
            $faction = $this->mock(Faction::class);
            $faction->shouldReceive('getId')->andReturn(2);
            $factions->shouldReceive('getByChooseable')->with(true)->once()->andReturn([$faction]);
            $user = $this->mock(User::class);
            $user->shouldReceive('getId')->andReturn(42);
            $user->shouldReceive('getFactionId')->once()->andReturn(1);
            $this->game->shouldReceive('getUser')->once()->andReturn($user);
            $this->game->shouldReceive('setView')->once();
            $this->game->shouldReceive('getInfo->addInformation')->with('Die Crew-Rasse wurde erneut zur Freigabe eingereicht')->once();
            $config = $this->mock(StuConfigInterface::class);
            $config->shouldReceive('getGameSettings->getGrantedFeatures')->once()->andReturn([
                ['feature' => 'CREW_RACE_MODERATION', 'userIds' => [7]]
            ]);
            $users = $this->mock(UserRepositoryInterface::class);
            $users->shouldReceive('find')->with(7)->once()->andReturn(new User());
            $sender = $this->mock(PrivateMessageSenderInterface::class);
            $sender->shouldReceive('send')->with(
                UserConstants::USER_NOONE,
                7,
                'Die Crew-Rasse Neu wurde von Spieler 42 überarbeitet und erneut zur Freigabe eingereicht',
                PrivateMessageFolderTypeEnum::SPECIAL_SYSTEM,
                'database.php?SHOW_CREW_RACE_MODERATION=1'
            )->once();
            request::setMockVars([
                'crew_race_id' => 5, 'crew_race_name' => 'Neu', 'crew_race_define' => 'NEW',
                'crew_race_male_ratio' => '100', 'crew_race_chance' => '70', 'crew_race_shared' => '1',
                'crew_race_factions' => [2, 999], 'crew_race_civil' => '0'
            ]);
            $graphicsConfig = $this->mock(ConfigInterface::class);
            $graphicsConfig->shouldReceive('get')->with('game.webroot')->andReturn($directory);
            $graphicsConfig->shouldReceive('get')->with('game.user_avatar_path')->andReturn('');
            new ResubmitCrewRace(new CrewRaceSubmission(
                $races,
                $factions,
                new CrewRaceGraphics($graphicsConfig),
                new CrewRaceSubmissionNotifier($config, $users, $sender)
            ))->handle($this->game);
            self::assertSame(5, $race->getId());
            self::assertSame('Neu', $race->getDescription());
            self::assertSame('NEW', $race->getGfxPath());
            self::assertSame(70, $race->getChance());
            self::assertTrue($race->isShared());
            self::assertFalse($race->isCivil());
            self::assertSame([1, 2], $race->getFactionIds());
            self::assertNull($race->getAcceptedUserId());
            self::assertNull($race->getRejectionReason());
            self::assertSame('Wartet auf Freigabe', $race->getStatus());
            self::assertSame('/avatare/user/crew/NEW/m/1_3.png?v=1', $race->getImagePath('m', 3));
            self::assertSame('image-3', file_get_contents($directory . '/crew/NEW/m/1_3.png'));
        } finally {
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($files as $file) {
                $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
            }
            rmdir($directory);
            $_FILES = [];
        }
    }
}
