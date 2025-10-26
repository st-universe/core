<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\SortPmCategories;

use Stu\Exception\AccessViolationException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\View\Noop\Noop;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;

final class SortPmCategories implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_PMCATEGORY_SORT';

    public function __construct(private SortPmCategoriesRequestInterface $sortPmCategoriesRequest, private PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository)
    {
    }

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(Noop::VIEW_IDENTIFIER);

        foreach ($this->sortPmCategoriesRequest->getCategoryIds() as $key => $value) {
            $cat = $this->privateMessageFolderRepository->find((int) $value);
            if ($cat === null || $cat->getUserId() !== $game->getUser()->getId()) {
                throw new AccessViolationException();
            }
            $cat->setSort((int) $key);

            $this->privateMessageFolderRepository->save($cat);
        }
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
