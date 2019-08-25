<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\Overview;

use KNPosting;
use PMCategory;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;

final class Overview implements ViewControllerInterface
{
    private const KNLIMITER = 6;

    private $overviewRequest;

    public function __construct(
        OverviewRequestInterface $overviewRequest
    ) {
        $this->overviewRequest = $overviewRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userKnMark = $game->getUser()->getKNMark();

        $newKnPostCount = KNPosting::countInstances(sprintf('id > %d', $userKnMark));
        $knPostCount = KNPosting::countInstances('1=1');

        $mark = $knPostCount;
        $lim = floor($mark / static::KNLIMITER) * static::KNLIMITER;
        if ($mark % static::KNLIMITER == 0) {
            $knStart = $lim - static::KNLIMITER;
        } else {
            $knStart = $lim;
        }

        $mark = $this->overviewRequest->getKnOffset();
        if ($mark % static::KNLIMITER != 0 || $mark < 0) {
            $mark = 0;
        }
        $maxpage = ceil($knPostCount/ static::KNLIMITER);
        $curpage = floor($mark / static::KNLIMITER);
        $knNavigation = [];
        if ($curpage != 0) {
            $knNavigation[] = ["page" => "<<", "mark" => 0, "cssclass" => "pages"];
            $knNavigation[] = ["page" => "<", "mark" => ($mark - static::KNLIMITER), "cssclass" => "pages"];
        }
        for ($i = $curpage - 1; $i <= $curpage + 3; $i++) {
            if ($i > $maxpage || $i < 1) {
                continue;
            }
            $knNavigation[] = [
                "page" => $i,
                "mark" => ($i * static::KNLIMITER - static::KNLIMITER),
                "cssclass" => ($curpage + 1 == $i ? "pages selected" : "pages")
            ];
        }
        if ($curpage + 1 != $maxpage) {
            $knNavigation[] = ["page" => ">", "mark" => ($mark + static::KNLIMITER), "cssclass" => "pages"];
            $knNavigation[] = ["page" => ">>", "mark" => $maxpage * static::KNLIMITER - static::KNLIMITER, "cssclass" => "pages"];
        }

        $game->setPageTitle(_('Kommunikationsnetzwerk'));
        $game->setTemplateFile('html/comm.xhtml');
        $game->appendNavigationPart('comm.php', _('KommNet'));

        $game->setTemplateVar(
            'KN_POSTINGS',
            KNPosting::getBy(
                sprintf(
                    'ORDER BY date DESC LIMIT %d,%d',
                    $this->overviewRequest->getKnOffset(),
                static::KNLIMITER)
            )
        );
        $game->setTemplateVar(
            'HAS_NEW_KN_POSTINGS',
            KNPosting::countInstances(sprintf('id > %d', $userKnMark))
        );
        $game->setTemplateVar('KN_START', $knStart);
        $game->setTemplateVar('KN_OFFSET', $mark);
        $game->setTemplateVar('NEW_KN_POSTING_COUNT', $newKnPostCount);
        $game->setTemplateVar('USER_KN_MARK', $userKnMark);
        $game->setTemplateVar('PM_CATEGORIES', PMCategory::getCategoryTree($game->getUser()->getId()));
        $game->setTemplateVar('KN_NAVIGATION', $knNavigation);
    }
}
