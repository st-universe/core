<?php

declare(strict_types=1);

namespace Stu\Component\Cli;

use Ahc\Cli\Input\Command;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Stu\Component\Faction\FactionEnum;
use Stu\Component\Player\Register\LocalPlayerCreator;
use Stu\Module\PlayerSetting\Action\ChangePassword\ChangePassword;
use Stu\Orm\Entity\FactionInterface;
use Stu\Orm\Repository\FactionRepositoryInterface;

/**
 * Provides cli method for user creation
 */
final class UserCreateCommand extends Command
{
    private ContainerInterface $dic;

    public function __construct(
        ContainerInterface $dic
    ) {
        $this->dic = $dic;

        parent::__construct(
            'user:create',
            'Creates a new user'
        );

        $this
            ->argument(
                '<username>',
                'Login name'
            )
            ->argument(
                '<email>',
                'Email-address'
            )
            ->argument(
                '<faction>',
                'Name of the faction the user should belong to'
            )
            ->usage(
                '<bold>  $0 user:create foobar foobar@example.com klingon</end> <comment></end> ## Creates a new klingon player<eol/>'
            );
    }

    public function execute(string $username, string $email, string $faction): void
    {
        $io = $this->io();

        $passValidator = function (string $password): string {
            if (!preg_match(ChangePassword::PASSWORD_REGEX, $password)) {
                throw new InvalidArgumentException('Password does not meet requirements (6-20 alphanumerical characters)');
            }

            return $password;
        };

        // perform some validation
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('The provided email address is invalid');
        }

        $factionId = FactionEnum::FACTION_NAME_TO_ID_MAP[$faction] ?? null;
        if ($factionId === null) {
            throw new InvalidArgumentException('The provided faction is invalid');
        }

        // prompt for the password
        $password = $io->promptHidden('Password', $passValidator, 2);

        /** @var FactionInterface $faction */
        $faction = $this->dic->get(FactionRepositoryInterface::class)->find($factionId);

        $player = $this->dic->get(LocalPlayerCreator::class)->createPlayer(
            $username,
            $email,
            $faction,
            $password
        );

        $io->ok(
            sprintf(
                'Player with ID %s has been created',
                $player->getId()
            ),
            true
        );
    }
}
