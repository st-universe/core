<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\Overview;

use Stu\Component\Communication\Kn\KnFactoryInterface;
use Stu\Component\Communication\Kn\KnItemInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\KnPostInterface;
use Stu\Orm\Repository\KnPostRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;

final class Overview implements ViewControllerInterface
{
    private const KNLIMITER = 6;

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

        if ($this->overviewRequest->startAtUserMark() === true) {
            $mark = (int) floor($newKnPostCount / static::KNLIMITER) * static::KNLIMITER;
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
            array_map(
                function (KnPostInterface $knPost) use ($user): KnItemInterface {
                    return $this->knFactory->createKnItem(
                        $knPost,
                        $user
                    );
                },
                $this->knPostRepository->getBy($mark, static::KNLIMITER)
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
