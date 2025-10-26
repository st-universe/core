<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\DeleteAvatar;

use Noodlehaus\ConfigInterface;
use RuntimeException;
use Stu\Exception\AccessViolationException;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Alliance\View\Edit\Edit;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\AllianceRepositoryInterface;

final class DeleteAvatar implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_DELETE_AVATAR';

    public function __construct(private AllianceActionManagerInterface $allianceActionManager, private AllianceRepositoryInterface $allianceRepository, private ConfigInterface $config) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $alliance = $user->getAlliance();

        if ($alliance === null) {
            throw new AccessViolationException();
        }

        if (!$this->allianceActionManager->mayEdit($alliance, $user)) {
            throw new AccessViolationException();
        }

        $game->setView(Edit::VIEW_IDENTIFIER);

        if ($alliance->hasAvatar()) {
            $result = @unlink(
                sprintf(
                    '%s%s/%s.png',
                    $this->config->get('game.webroot'),
                    $this->config->get('game.alliance_avatar_path'),
                    $alliance->getAvatar()
                )
            );


            if ($result === false) {
                throw new RuntimeException('alliance avatar could not be deleted');
            }

            $alliance->setAvatar('');

            $this->allianceRepository->save($alliance);
        }

        $game->getInfo()->addInformation(_('Das Bild wurde gelöscht'));
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
