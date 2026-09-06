<?php

declare(strict_types=1);

namespace Stu\Component\Crew;

use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Entity\CrewRace;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\StuTestCase;

final class CrewRaceSubmissionNotifierTest extends StuTestCase
{
    public static function submissionProvider(): array
    {
        return [[false, 'Die Crew-Rasse Test wurde von Spieler 42 zur Freigabe eingereicht'],
            [true, 'Die Crew-Rasse Test wurde von Spieler 42 überarbeitet und erneut zur Freigabe eingereicht']];
    }

    #[DataProvider('submissionProvider')]
    public function testNotifiesOnlyExplicitGrantsOnce(bool $resubmitted, string $message): void
    {
        $config = $this->mock(StuConfigInterface::class);
        $users = $this->mock(UserRepositoryInterface::class);
        $sender = $this->mock(PrivateMessageSenderInterface::class);
        $config->shouldReceive('getGameSettings->getGrantedFeatures')->once()->andReturn([
            ['feature' => 'CREW_RACE_MODERATION', 'userIds' => [7, 8, 7]],
            ['feature' => 'OTHER', 'userIds' => [9]],
            ['feature' => 'CREW_RACE_MODERATION', 'userIds' => [7, 10]]
        ]);
        // No admin list is queried: only explicit feature grants receive notifications.
        foreach ([7, 8] as $id) {
            $users->shouldReceive('find')->with($id)->once()->andReturn(new User());
            $sender->shouldReceive('send')->with(
                UserConstants::USER_NOONE,
                $id,
                $message,
                PrivateMessageFolderTypeEnum::SPECIAL_SYSTEM,
                'database.php?SHOW_CREW_RACE_MODERATION=1'
            )->once();
        }
        $users->shouldReceive('find')->with(10)->once()->andReturn(null);

        new CrewRaceSubmissionNotifier($config, $users, $sender)->notify(
            new CrewRace()->setDescription('Test')->setCreatorUserId(42),
            $resubmitted
        );
    }
}
