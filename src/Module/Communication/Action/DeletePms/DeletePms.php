<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\DeletePms;

use PM;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;

final class DeletePms implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DELETE_PMS';

    private $deletePmsRequest;

    public function __construct(
        DeletePmsRequestInterface $deletePmsRequest
    ) {
        $this->deletePmsRequest = $deletePmsRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        foreach ($this->deletePmsRequest->getIgnoreIds() as $key => $val) {
            $pm = PM::getPMById($val);
            if (!$pm || !$pm->isOwnPM()) {
                continue;
            }
            $pm->deleteFromDatabase();
        }
        $game->addInformation(_('Die Nachrichten wurden gelöscht'));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
