<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\SortPmCategories;

use AccessViolation;
use PMCategory;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;
use Stu\Module\Communication\View\Noop\Noop;

final class SortPmCategories implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_PMCATEGORY_SORT';

    private $sortPmCategoriesRequest;

    public function __construct(
        SortPmCategoriesRequestInterface $sortPmCategoriesRequest
    ) {
        $this->sortPmCategoriesRequest = $sortPmCategoriesRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(Noop::VIEW_IDENTIFIER);

        foreach ($this->sortPmCategoriesRequest->getCategoryIds() as $key => $value) {
            $cat = new PMCategory($value);
            if (!$cat->isOwnCategory()) {
                throw new AccessViolation();
            }
            $cat->setSort(intval($key));
            $cat->save();
        }
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
