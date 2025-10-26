<?php

declare(strict_types=1);

namespace Stu\Module\Game\Lib\View\Provider;

use request;
use Stu\Component\Communication\Kn\KnFactoryInterface;
use Stu\Component\Communication\Kn\KnItemInterface;
use Stu\Component\Game\GameEnum;
use Stu\Component\Game\JavascriptExecutionTypeEnum;
use Stu\Module\Communication\View\ShowKnArchive\ShowKnArchive;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\KnPost;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\KnPostArchivRepositoryInterface;
use Stu\Orm\Repository\KnPostRepositoryInterface;

final class CommunicationProvider implements ViewComponentProviderInterface
{
    public function __construct(
        private KnPostRepositoryInterface $knPostRepository,
        private KnFactoryInterface $knFactory,
        private KnPostArchivRepositoryInterface $knPostArchivRepository
    ) {}

    #[\Override]
    public function setTemplateVariables(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userKnMark = $user->getKnMark();

        $newKnPostCount = $this->knPostRepository->getAmountSince($userKnMark);
        $knPostCount = $this->knPostRepository->getAmount();

        $mark = $knPostCount;
        $lim = floor($mark / GameEnum::KN_PER_SITE) * GameEnum::KN_PER_SITE;
        $knStart = $mark % GameEnum::KN_PER_SITE == 0 ? $lim - GameEnum::KN_PER_SITE : $lim;

        $mark = request::getInt('mark');
        if ($mark % GameEnum::KN_PER_SITE != 0 || $mark < 0) {
            $mark = 0;
        }
        if (request::getInt('user_mark') !== 0) {
            $mark = max(0, (int) floor(($newKnPostCount - 1) / GameEnum::KN_PER_SITE) * GameEnum::KN_PER_SITE);
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

        $markedPostId = $this->getMarkedKnId($user);

        $game->setTemplateVar(
            'KN_POSTINGS',
            array_map(
                function (KnPost $knPost) use ($user, $markedPostId): KnItemInterface {
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
        $game->setTemplateVar('KN_NAVIGATION', $knNavigation);

        $availableVersions = $this->knPostArchivRepository->getAvailableVersions();
        $formattedVersions = array_map(function ($version) {
            return [
                'version' => $version,
                'display' => $this->formatVersion($version)
            ];
        }, $availableVersions);

        $game->setTemplateVar('AVAILABLE_ARCHIVE_VERSIONS', $formattedVersions);
        $game->setTemplateVar('SHOW_ARCHIVE_VIEW', ShowKnArchive::VIEW_IDENTIFIER);

        $game->addExecuteJS("initTranslations();", JavascriptExecutionTypeEnum::AFTER_RENDER);
    }

    private function getMarkedKnId(User $user): ?int
    {
        $markedPostId = request::getInt('markedPost');
        if ($markedPostId !== 0) {
            return $markedPostId;
        }

        $newerKnPosts = $this->knPostRepository->getNewerThenMark($user->getKnMark());
        if ($newerKnPosts !== []) {
            return $newerKnPosts[0]->getId();
        }

        return null;
    }

    private function formatVersion(string $version): string
    {
        $cleanVersion = ltrim($version, 'v');

        if (str_contains($cleanVersion, 'alpha')) {
            return 'v' . str_replace('alpha', 'α', $cleanVersion);
        }

        if (preg_match('/^(\d)(\d)$/', $cleanVersion, $matches)) {
            return 'v' . $matches[1] . '.' . $matches[2];
        }

        return $version;
    }
}
