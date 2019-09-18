<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowPmCategory;

use AccessViolation;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageRepositoryInterface;

final class ShowPmCategory implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_PM_CAT';

    private const PMLIMITER = 6;

    private $showPmCategoryRequest;

    private $privateMessageFolderRepository;

    private $privateMessageRepository;

    public function __construct(
        ShowPmCategoryRequestInterface $showPmCategoryRequest,
        PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository,
        PrivateMessageRepositoryInterface $privateMessageRepository
    ) {
        $this->showPmCategoryRequest = $showPmCategoryRequest;
        $this->privateMessageFolderRepository = $privateMessageFolderRepository;
        $this->privateMessageRepository = $privateMessageRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();
        $categoryId = $this->showPmCategoryRequest->getCategoryId();

        $mark = $this->showPmCategoryRequest->getListOffset();

        if ($categoryId === 0) {
            $category = $this->privateMessageFolderRepository->getByUserAndSpecial($userId, PM_SPECIAL_MAIN);
        } else {
            $category = $this->privateMessageFolderRepository->find($categoryId);
            if ($category === null || $category->getUserId() !== $userId) {
                throw new AccessViolation();
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
            sprintf('comm.php?%s=1&pmcat=%d', static::VIEW_IDENTIFIER, $category->getId()),
            sprintf(_('Ordner: %s'), $category->getDescription())
        );
        $game->setPageTitle(sprintf(_('Ordner: %s'), $category->getDescription()));

        $game->setTemplateVar('CATEGORY', $category);
        $game->setTemplateVar(
            'PM_LIST',
            $this->privateMessageRepository->getByUserAndFolder($userId, $categoryId, (int) $mark, static::PMLIMITER)
        );
        $game->setTemplateVar('PM_NAVIGATION', $pmNavigation);
        $game->setTemplateVar('PM_CATEGORIES', $this->privateMessageFolderRepository->getOrderedByUser($userId));
    }
}
