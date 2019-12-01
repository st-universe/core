<?php

declare(strict_types=1);

namespace Stu\Module\Message\View\ShowPmCategoryList;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;

final class ShowPmCategoryList implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_CAT_LIST';

    private PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository;

    public function __construct(
        PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository
    ) {
        $this->privateMessageFolderRepository = $privateMessageFolderRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->showMacro('html/commmacros.xhtml/pmcategorylist_ajax');

        $game->setTemplateVar('markcat', true);
        $game->setTemplateVar(
            'PM_CATEGORIES',
            $this->privateMessageFolderRepository->getOrderedByUser($game->getUser()->getId())
        );
    }
}
