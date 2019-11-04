<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\RateKnPost;

use Stu\Module\Communication\View\ShowKnRating\ShowKnRating;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\KnPostInterface;
use Stu\Orm\Repository\KnPostRepositoryInterface;

final class RateKnPost implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_RATE_KN_POST';

    private $knPostRepository;

    private $knPostRequest;

    public function __construct(
        KnPostRepositoryInterface $knPostRepository,
        RateKnPostRequestInterface $knPostRequest
    ) {
        $this->knPostRepository = $knPostRepository;
        $this->knPostRequest = $knPostRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        /** @var KnPostInterface $post */
        $post = $this->knPostRepository->find($this->knPostRequest->getPostId());

        $game->setView(ShowKnRating::VIEW_IDENTIFIER, ['knPost' => $post]);

        if ($post === null) {
            return;
        }

        $userId = $game->getUser()->getId();

        $ratings = $post->getRatings();

        if (array_key_exists($userId, $ratings)) {
            return;
        }
        $ratings[$userId] = $this->knPostRequest->getRating();

        $post->setRatings($ratings);

        $this->knPostRepository->save($post);
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
