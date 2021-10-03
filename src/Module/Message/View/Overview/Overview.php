<?php

declare(strict_types=1);

namespace Stu\Module\Message\View\Overview;

use request;
use Stu\Module\Control\GameController;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageListItem;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\PrivateMessageInterface;
use Stu\Orm\Repository\ContactRepositoryInterface;
use Stu\Orm\Repository\IgnoreListRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageRepositoryInterface;

final class Overview implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = GameController::DEFAULT_VIEW;

    private const PMLIMITER = 6;

    private OverviewRequestInterface $showPmCategoryRequest;

    private PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository;

    private PrivateMessageRepositoryInterface $privateMessageRepository;

    private IgnoreListRepositoryInterface $ignoreListRepository;

    private ContactRepositoryInterface $contactRepository;

    public function __construct(
        OverviewRequestInterface $showPmCategoryRequest,
        PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository,
        PrivateMessageRepositoryInterface $privateMessageRepository,
        IgnoreListRepositoryInterface $ignoreListRepository,
        ContactRepositoryInterface $contactRepository
    ) {
        $this->showPmCategoryRequest = $showPmCategoryRequest;
        $this->privateMessageFolderRepository = $privateMessageFolderRepository;
        $this->privateMessageRepository = $privateMessageRepository;
        $this->ignoreListRepository = $ignoreListRepository;
        $this->contactRepository = $contactRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();
        $categoryId = request::indInt('pmcat');

        $mark = $this->showPmCategoryRequest->getListOffset();

        if ($categoryId === 0) {
            $category = $this->privateMessageFolderRepository->getByUserAndSpecial(
                $userId,
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_MAIN
            );
        } else {
            $category = $this->privateMessageFolderRepository->find($categoryId);
            if ($category === null || $category->getUserId() !== $userId) {
                $category = $this->privateMessageFolderRepository->getByUserAndSpecial(
                    $userId,
                    PrivateMessageFolderSpecialEnum::PM_SPECIAL_MAIN
                );
            }
        }

        if ($mark % static::PMLIMITER != 0 || $mark < 0) {
            $mark = 0;
        }
        $maxcount = $category->getCategoryCount();
        $maxpage = ceil($maxcount / static::PMLIMITER);
        $curpage = floor($mark / static::PMLIMITER);
        $pmNavigation = [];
        if ($curpage != 0) {
            $pmNavigation[] = ["page" => "<<", "mark" => 0, "cssclass" => "pages"];
            $pmNavigation[] = ["page" => "<", "mark" => ($mark - static::PMLIMITER), "cssclass" => "pages"];
        }
        for ($i = $curpage - 1; $i <= $curpage + 3; $i++) {
            if ($i > $maxpage || $i < 1) {
                continue;
            }
            $pmNavigation[] = [
                "page" => $i,
                "mark" => ($i * static::PMLIMITER - static::PMLIMITER),
                "cssclass" => ($curpage + 1 == $i ? "pages selected" : "pages")
            ];
        }
        if ($curpage + 1 != $maxpage) {
            $pmNavigation[] = ["page" => ">", "mark" => ($mark + static::PMLIMITER), "cssclass" => "pages"];
            $pmNavigation[] = ["page" => ">>", "mark" => $maxpage * static::PMLIMITER - static::PMLIMITER, "cssclass" => "pages"];
        }

        $game->setTemplateFile('html/pmcategory.xhtml');
        $game->appendNavigationPart(
            sprintf('pm.php?%s=1&pmcat=%d', static::VIEW_IDENTIFIER, $category->getId()),
            sprintf(_('Ordner: %s'), $category->getDescription())
        );
        $game->setPageTitle(sprintf(_('Ordner: %s'), $category->getDescription()));

        $game->setTemplateVar('CATEGORY', $category);
        $game->setTemplateVar(
            'PM_LIST',
            array_map(
                function (PrivateMessageInterface $message) use ($userId): PrivateMessageListItem {
                    return new PrivateMessageListItem(
                        $this->privateMessageRepository,
                        $this->contactRepository,
                        $this->ignoreListRepository,
                        $message,
                        $userId
                    );
                },
                $this->privateMessageRepository->getByUserAndFolder(
                    $userId,
                    $category->getId(),
                    (int) $mark,
                    static::PMLIMITER
                )
            )
        );
        $game->setTemplateVar('PM_NAVIGATION', $pmNavigation);
        $game->setTemplateVar('PM_CATEGORIES', $this->privateMessageFolderRepository->getOrderedByUser($userId));
    }
}
