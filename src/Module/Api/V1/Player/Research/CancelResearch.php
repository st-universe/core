<?php

declare(strict_types=1);

namespace Stu\Module\Api\V1\Player\Research;

use Psr\Http\Message\ServerRequestInterface;
use Stu\Module\Api\Middleware\Action;
use Stu\Module\Api\Middleware\Response\JsonResponseInterface;
use Stu\Module\Api\Middleware\SessionInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;

final class CancelResearch extends Action
{
    private SessionInterface $session;

    private ResearchedRepositoryInterface $researchedRepository;

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
        $researchState = $this->researchedRepository->getCurrentResearch(
            $this->session->getUser()->getId()
        );

        if ($researchState !== null) {
            $this->researchedRepository->delete($researchState);
        }

        return $response->withData(true);
    }
}
