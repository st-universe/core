<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\AddPmCategory;

use PMCategoryData;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Communication\View\ShowPmCategoryList\ShowPmCategoryList;

final class AddPmCategory implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_ADD_PMCATEGORY';

    private $addPmCategoryRequest;

    public function __construct(
        AddPmCategoryRequestInterface $addPmCategoryRequest
    ) {
        $this->addPmCategoryRequest = $addPmCategoryRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowPmCategoryList::VIEW_IDENTIFIER);

        $name = $this->addPmCategoryRequest->getName();
        if (mb_strlen($name) < 1) {
            return;
        }
        $cat = new PMCategoryData(array());
        $cat->setUserId($game->getUser()->getId());
        $cat->appendToSorting();
        $cat->setDescription($name);
        $cat->save();

        $game->setTemplateVar('CATEGORY', $cat);
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
