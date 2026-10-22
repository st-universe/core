<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\SortPmCategories;

use request;
use Stu\Exception\AccessViolationException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Message\View\ShowPmCategoryList\ShowPmCategoryList;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;

final class SortPmCategories implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_PMCATEGORY_SORT';

    public function __construct(private SortPmCategoriesRequestInterface $sortPmCategoriesRequest, private PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        if (!request::isPost()) {
            return;
        }

        $context->setView(ShowPmCategoryList::VIEW_IDENTIFIER);

        foreach ($this->sortPmCategoriesRequest->getCategoryIds() as $key => $value) {
            $cat = $this->privateMessageFolderRepository->find((int) $value);
            if ($cat === null || $cat->getUserId() !== $context->getUser()->getId()) {
                throw new AccessViolationException();
            }
            $cat->setSort((int) $key);

            $this->privateMessageFolderRepository->save($cat);
        }
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
