<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\SortPmCategories;

use Stu\Exception\AccessViolation;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\View\Noop\Noop;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;

final class SortPmCategories implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_PMCATEGORY_SORT';

    private SortPmCategoriesRequestInterface $sortPmCategoriesRequest;

    private PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository;

    public function __construct(
        SortPmCategoriesRequestInterface $sortPmCategoriesRequest,
        PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository
    ) {
        $this->sortPmCategoriesRequest = $sortPmCategoriesRequest;
        $this->privateMessageFolderRepository = $privateMessageFolderRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(Noop::VIEW_IDENTIFIER);

        foreach ($this->sortPmCategoriesRequest->getCategoryIds() as $key => $value) {
            $cat = $this->privateMessageFolderRepository->find((int) $value);
            if ($cat === null || $cat->getUserId() !== $game->getUser()->getId()) {
                throw new AccessViolation();
            }
            $cat->setSort((int) $key);

            $this->privateMessageFolderRepository->save($cat);
        }
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
