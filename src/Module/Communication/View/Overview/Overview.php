<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\Overview;

use request;
use Stu\Component\Communication\Kn\KnFactoryInterface;
use Stu\Component\Communication\Kn\KnItemInterface;
use Stu\Component\Game\GameEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\KnPostInterface;
use Stu\Orm\Repository\KnPostRepositoryInterface;

final class Overview implements ViewControllerInterface
{
    private OverviewRequestInterface $overviewRequest;

    private KnPostRepositoryInterface $knPostRepository;

    private KnFactoryInterface $knFactory;

    public function __construct(
        OverviewRequestInterface $overviewRequest,
        KnPostRepositoryInterface $knPostRepository,
        KnFactoryInterface $knFactory
    ) {
        $this->overviewRequest = $overviewRequest;
        $this->knPostRepository = $knPostRepository;
        $this->knFactory = $knFactory;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userKnMark = $user->getKNMark();

        $newKnPostCount = $this->knPostRepository->getAmountSince($userKnMark);
        $knPostCount = $this->knPostRepository->getAmount();

        $mark = $knPostCount;
        $lim = floor($mark / GameEnum::KN_PER_SITE) * GameEnum::KN_PER_SITE;
        $knStart = $mark % GameEnum::KN_PER_SITE == 0 ? $lim - GameEnum::KN_PER_SITE : $lim;

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
                "cssclass" => ($curpage + 1 === $i ? "pages selected" : "pages")
            ];
        }
        if ($curpage + 1 !== $maxpage) {
            $knNavigation[] = ["page" => ">", "mark" => ($mark + GameEnum::KN_PER_SITE), "cssclass" => "pages"];
            $knNavigation[] = ["page" => ">>", "mark" => $maxpage * GameEnum::KN_PER_SITE - GameEnum::KN_PER_SITE, "cssclass" => "pages"];
        }

        $game->setPageTitle(_('Kommunikationsnetzwerk'));
        $game->setTemplateFile('html/comm.xhtml');
        $game->appendNavigationPart('comm.php', _('KommNet'));

        $markedPostId = request::getInt('markedPost');

        $game->setTemplateVar(
            'KN_POSTINGS',
            array_map(
                function (KnPostInterface $knPost) use ($user, $markedPostId): KnItemInterface {
                    $knItem = $this->knFactory->createKnItem(
                        $knPost,
                        $user
                    );
                    if ($markedPostId && $knItem->getId() == $markedPostId) {
                        $knItem->setIsHighlighted(true);
                    }
                    return $knItem;
                },
                $this->knPostRepository->getBy($mark, GameEnum::KN_PER_SITE)
            )
        );
        $game->setTemplateVar('HAS_NEW_KN_POSTINGS', $this->knPostRepository->getAmountSince($userKnMark));
        $game->setTemplateVar('KN_START', $knStart);
        $game->setTemplateVar('KN_OFFSET', $mark);
        $game->setTemplateVar('NEW_KN_POSTING_COUNT', $newKnPostCount);
        $game->setTemplateVar('USER_KN_MARK', $userKnMark);
        $game->setTemplateVar('KN_NAVIGATION', $knNavigation);
    }
}
