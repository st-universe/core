<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowPmCategory;

use AccessViolation;
use PM;
use PMCategory;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class ShowPmCategory implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_PM_CAT';

    private const PMLIMITER = 6;

    private $showPmCategoryRequest;

    public function __construct(
        ShowPmCategoryRequestInterface $showPmCategoryRequest
    ) {
        $this->showPmCategoryRequest = $showPmCategoryRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();
        $category_id = $this->showPmCategoryRequest->getCategoryId();

        $mark = $this->showPmCategoryRequest->getListOffset();

        if ($category_id === 0) {
            $category = PMCategory::getOrGenSpecialCategory(PM_SPECIAL_MAIN, $userId);
        } else {
            $category = new PMCategory($category_id);
            if ($category->getUserId() != $userId) {
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
            sprintf(_('Ordner: %s'), $category->getDescriptionDecoded())
        );
        $game->setPageTitle(sprintf(_('Ordner: %s'), $category->getDescriptionDecoded()));

        $game->setTemplateVar('CATEGORY', $category);
        $game->setTemplateVar('PM_LIST', PM::getPMsBy($userId, (int) $category->getId(), $mark, static::PMLIMITER));
        $game->setTemplateVar('PM_NAVIGATION', $pmNavigation);
        $game->setTemplateVar('PM_CATEGORIES', PMCategory::getCategoryTree($userId));
    }
}
