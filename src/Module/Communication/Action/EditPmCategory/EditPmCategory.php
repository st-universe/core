<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\EditPmCategory;

use AccessViolation;
use PMCategory;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Communication\View\ShowPmCategoryList\ShowPmCategoryList;

final class EditPmCategory implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_EDIT_PMCATEGORY_NAME';

    private $editPmCategoryRequest;

    public function __construct(
        EditPmCategoryRequestInterface $editPmCategoryRequest
    ) {
        $this->editPmCategoryRequest = $editPmCategoryRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowPmCategoryList::VIEW_IDENTIFIER);

        $name = $this->editPmCategoryRequest->getName();
        if (mb_strlen($name) < 1) {
            return;
        }

        $cat = new PMCategory($this->editPmCategoryRequest->getCategoryId());
        if ($cat->getUserId() != $game->getUser()->getId()) {
            throw new AccessViolation();
        }

        $cat->setDescription($name);
        $cat->save();

        $game->setTemplateVar('CATEGORY', $cat);
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
