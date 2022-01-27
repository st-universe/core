<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\Overview;

use Stu\Component\Communication\Kn\KnFactoryInterface;
use Stu\Component\Communication\Kn\KnItemInterface;
use Stu\Component\Game\GameEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\KnPostInterface;
use Stu\Orm\Repository\KnPostRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;

final class Overview implements ViewControllerInterface
{
    private OverviewRequestInterface $overviewRequest;

    private KnPostRepositoryInterface $knPostRepository;

    private PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository;

    private KnFactoryInterface $knFactory;

    public function __construct(
        OverviewRequestInterface $overviewRequest,
        KnPostRepositoryInterface $knPostRepository,
        PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository,
        KnFactoryInterface $knFactory
    ) {
        $this->overviewRequest = $overviewRequest;
        $this->knPostRepository = $knPostRepository;
        $this->privateMessageFolderRepository = $privateMessageFolderRepository;
        $this->knFactory = $knFactory;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userKnMark = (int) $user->getKNMark();

        $newKnPostCount = $this->knPostRepository->getAmountSince($userKnMark);
        $knPostCount = $this->knPostRepository->getAmount();

        $mark = $knPostCount;
        $lim = floor($mark / GameEnum::KN_PER_SITE) * GameEnum::KN_PER_SITE;
        if ($mark % GameEnum::KN_PER_SITE == 0) {
            $knStart = $lim - GameEnum::KN_PER_SITE;
        } else {
            $knStart = $lim;
        }

        $mark = $this->overviewRequest->getKnOffset();
        if ($mark % GameEnum::KN_PER_SITE != 0 || $mark < 0) {
            $mark = 0;
        }

        if ($this->overviewRequest->startAtUserMark() === true) {
            $mark = (int) floor($newKnPostCount / GameEnum::KN_PER_SITE) * GameEnum::KN_PER_SITE;
        }

        $maxpage = ceil($knPostCount / GameEnum::KN_PER_SITE);
        $curpage = floor($mark / GameEnum::KN_PER_SITE);
        $knNavigation = [];
        if ($curpage != 0) {
            $knNavigation[] = ["page" => "<<", "mark" => 0, "cssclass" => "pages"];
            $knNavigation[] = ["page" => "<", "mark" => ($mark - GameEnum::KN_PER_SITE), "cssclass" => "pages"];
        }
        for ($i = $curpage - 1; $i <= $curpage + 3; $i++) {
            if ($i > $maxpage || $i < 1) {
                continue;
            }
            $knNavigation[] = [
                "page" => $i,
                "mark" => ($i * GameEnum::KN_PER_SITE - GameEnum::KN_PER_SITE),
                "cssclass" => ($curpage + 1 == $i ? "pages selected" : "pages")
            ];
        }
        if ($curpage + 1 != $maxpage) {
            $knNavigation[] = ["page" => ">", "mark" => ($mark + GameEnum::KN_PER_SITE), "cssclass" => "pages"];
            $knNavigation[] = ["page" => ">>", "mark" => $maxpage * GameEnum::KN_PER_SITE - GameEnum::KN_PER_SITE, "cssclass" => "pages"];
        }

        $game->setPageTitle(_('Kommunikationsnetzwerk'));
        $game->setTemplateFile('html/comm.xhtml');
        $game->appendNavigationPart('comm.php', _('KommNet'));

        $game->setTemplateVar(
            'KN_POSTINGS',
            array_map(
                function (KnPostInterface $knPost) use ($user): KnItemInterface {
                    return $this->knFactory->createKnItem(
                        $knPost,
                        $user
                    );
                },
                $this->knPostRepository->getBy($mark, GameEnum::KN_PER_SITE)
            )
        );
        $game->setTemplateVar('HAS_NEW_KN_POSTINGS', $this->knPostRepository->getAmountSince($userKnMark));
        $game->setTemplateVar('KN_START', $knStart);
        $game->setTemplateVar('KN_OFFSET', $mark);
        $game->setTemplateVar('NEW_KN_POSTING_COUNT', $newKnPostCount);
        $game->setTemplateVar('USER_KN_MARK', $userKnMark);
        $game->setTemplateVar(
            'PM_CATEGORIES',
            $this->privateMessageFolderRepository->getOrderedByUser($game->getUser()->getId())
        );
        $game->setTemplateVar('KN_NAVIGATION', $knNavigation);
    }
}
