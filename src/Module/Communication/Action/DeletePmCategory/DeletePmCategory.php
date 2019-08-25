<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\DeletePmCategory;

use PMCategory;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;

final class DeletePmCategory implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DELETE_PMCATEGORY';

    private $deletePmCategoryRequest;

    public function __construct(
        DeletePmCategoryRequestInterface $deletePmCategoryRequest
    ) {
        $this->deletePmCategoryRequest = $deletePmCategoryRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $cat = new PMCategory($this->deletePmCategoryRequest->getCategoryId());
        if (
            !$cat ||
            $cat->getUserId() != $game->getUser()->getId() ||
            !$cat->isDeleteAble()
        ) {
            return;
        }
        $cat->truncate();

        $cat->deleteFromDatabase();

        $game->addInformation(_('Der Ordner wurde gelöscht'));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
