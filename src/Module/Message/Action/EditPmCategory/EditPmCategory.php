<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\EditPmCategory;

use Stu\Exception\AccessViolationException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Message\View\ShowPmCategoryList\ShowPmCategoryList;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;

final class EditPmCategory implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_EDIT_PMCATEGORY_NAME';

    public function __construct(private EditPmCategoryRequestInterface $editPmCategoryRequest, private PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $context->setView(ShowPmCategoryList::VIEW_IDENTIFIER);

        $name = $this->editPmCategoryRequest->getName();
        if (mb_strlen($name) < 1) {
            return;
        }

        $cat = $this->privateMessageFolderRepository->find($this->editPmCategoryRequest->getCategoryId());
        if ($cat === null || $cat->getUserId() !== $context->getUser()->getId()) {
            throw new AccessViolationException();
        }

        $cat->setDescription($name);

        $this->privateMessageFolderRepository->save($cat);

        $context->setTemplateVar('CATEGORY', $cat);
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
