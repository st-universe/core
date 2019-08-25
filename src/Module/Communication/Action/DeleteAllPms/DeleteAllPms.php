<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\DeleteAllPms;

use PMCategory;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;

final class DeleteAllPms implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DELETE_ALL_PMS';

    private $deleteAllPmsRequest;

    public function __construct(
        DeleteAllPmsRequestInterface $deleteAllPmsRequest
    ) {
        $this->deleteAllPmsRequest = $deleteAllPmsRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $cat = PMCategory::getById($this->deleteAllPmsRequest->getCategoryId());
        if (!$cat || $cat->getUserId() != $game->getUser()->getId()) {
            return;
        }
        $cat->truncate();

        $game->addInformation(_('Der Ordner wurden geleert'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
