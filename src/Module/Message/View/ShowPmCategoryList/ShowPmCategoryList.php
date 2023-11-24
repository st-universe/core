<?php

declare(strict_types=1);

namespace Stu\Module\Message\View\ShowPmCategoryList;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderItem;
use Stu\Module\Message\Lib\PrivateMessageUiFactoryInterface;
use Stu\Orm\Entity\PrivateMessageFolderInterface;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;

final class ShowPmCategoryList implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_CAT_LIST';

    private PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository;

    private PrivateMessageUiFactoryInterface $privateMessageUiFactory;

    public function __construct(
        PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository,
        PrivateMessageUiFactoryInterface $privateMessageUiFactory
    ) {
        $this->privateMessageFolderRepository = $privateMessageFolderRepository;
        $this->privateMessageUiFactory = $privateMessageUiFactory;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->showMacro('html/communication/pmCategoryListAjax.twig');

        $game->setTemplateVar('markcat', true);
        $game->setTemplateVar(
            'PM_CATEGORIES',
            array_map(
                fn (PrivateMessageFolderInterface $privateMessageFolder): PrivateMessageFolderItem =>
                $this->privateMessageUiFactory->createPrivateMessageFolderItem($privateMessageFolder),
                $this->privateMessageFolderRepository->getOrderedByUser($game->getUser()->getId())
            )
        );
    }
}
