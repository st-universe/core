<?php

declare(strict_types=1);

namespace Stu\Module\Api\V1\Player\Research;

use Psr\Http\Message\ServerRequestInterface;
use Stu\Module\Api\Middleware\Action;
use Stu\Module\Api\Middleware\Response\JsonResponseInterface;
use Stu\Module\Api\Middleware\SessionInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;

final class CurrentResearch extends Action
{
    private $session;

    private $researchedRepository;

    public function __construct(
        SessionInterface $session,
        ResearchedRepositoryInterface $researchedRepository
    ) {
        $this->session = $session;
        $this->researchedRepository = $researchedRepository;
    }

    protected function action(
        ServerRequestInterface $request,
        JsonResponseInterface $response,
        array $args
    ): JsonResponseInterface {
        $user = $this->session->getUser();

        $researchState = $this->researchedRepository->getCurrentResearch($user->getId());

        if ($researchState === null) {
            return $response->withData(null);
        }

        return $response->withData([
            'tech' => [
                'id' => $researchState->getResearch()->getId(),
                'name' => $researchState->getResearch()->getName(),
                'points' => $researchState->getResearch()->getPoints()
            ],
            'pointsLeft' => $researchState->getActive()
        ]);
    }
}
