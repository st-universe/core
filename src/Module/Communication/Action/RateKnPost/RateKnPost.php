<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\RateKnPost;

use Stu\Module\Communication\View\ShowKnRating\ShowKnRating;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Prestige\Lib\CreatePrestigeLogInterface;
use Stu\Orm\Entity\KnPostInterface;
use Stu\Orm\Repository\KnPostRepositoryInterface;

final class RateKnPost implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_RATE_KN_POST';

    private const PRESTIGE_PER_POSITIVE_VOTE = 5;

    private KnPostRepositoryInterface $knPostRepository;

    private RateKnPostRequestInterface $knPostRequest;

    private CreatePrestigeLogInterface $createPrestigeLog;

    public function __construct(
        KnPostRepositoryInterface $knPostRepository,
        RateKnPostRequestInterface $knPostRequest,
        CreatePrestigeLogInterface $createPrestigeLog
    ) {
        $this->knPostRepository = $knPostRepository;
        $this->knPostRequest = $knPostRequest;
        $this->createPrestigeLog = $createPrestigeLog;
    }

    public function handle(GameControllerInterface $game): void
    {
        /** @var KnPostInterface $post */
        $post = $this->knPostRepository->find($this->knPostRequest->getPostId());

        if ($post === null) {
            return;
        }

        $game->setView(ShowKnRating::VIEW_IDENTIFIER, ['knPost' => $post]);

        $userId = $game->getUser()->getId();

        $ratings = $post->getRatings();

        if (array_key_exists($userId, $ratings) || $post->getUser() === $game->getUser()) {
            return;
        }
        $rating = $this->knPostRequest->getRating();
        $ratings[$userId] = $rating;

        $post->setRatings($ratings);

        $this->knPostRepository->save($post);

        // create prestige log
        $this->checkForPrestige($post, $rating);
    }

    private function checkForPrestige(KnPostInterface $post, int $rating): void
    {
        // nothing to do
        if ($rating < 0) {
            return;
        }

        $description = sprintf(
            '%d Prestige erhalten für einen positiven Vote deines KN-Beitrags "%s" mit der ID %d',
            self::PRESTIGE_PER_POSITIVE_VOTE,
            $this->getTitle($post),
            $post->getId()
        );
        $this->createPrestigeLog->createLog(self::PRESTIGE_PER_POSITIVE_VOTE, $description, $post->getUser(), time());
    }

    private function getTitle(KnPostInterface $post): string
    {
        $title = (string) $post->getTitle();

        if ($title !== '') {
            return $title;
        }
        if ($post->getRpgPlot() !== null) {
            return $post->getRpgPlot()->getTitle();
        }

        return '';
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
