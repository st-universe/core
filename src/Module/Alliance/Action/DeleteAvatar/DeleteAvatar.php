<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\DeleteAvatar;

use AccessViolation;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Alliance\View\Edit\Edit;
use Stu\Orm\Repository\AllianceRepositoryInterface;

final class DeleteAvatar implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DELETE_AVATAR';

    private $allianceActionManager;

    private $allianceRepository;

    public function __construct(
        AllianceActionManagerInterface $allianceActionManager,
        AllianceRepositoryInterface $allianceRepository
    ) {
        $this->allianceActionManager = $allianceActionManager;
        $this->allianceRepository = $allianceRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $alliance = $user->getAlliance();

        if (!$this->allianceActionManager->mayEdit((int)$alliance->getId(), $user->getId())) {
            throw new AccessViolation();
        }

        $game->setView(Edit::VIEW_IDENTIFIER);

        if ($alliance->getAvatar()) {
            @unlink(AVATAR_ALLIANCE_PATH_INTERNAL . $alliance->getAvatar() . '.png');
            $alliance->setAvatar('');

            $this->allianceRepository->save($alliance);
        }
        $game->addInformation(_('Das Bild wurde gelöscht'));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
