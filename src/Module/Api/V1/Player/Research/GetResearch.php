<?php

declare(strict_types=1);

namespace Stu\Module\Api\V1\Player\Research;

use Psr\Http\Message\ServerRequestInterface;
use Stu\Component\ErrorHandling\ErrorCodeEnum;
use Stu\Module\Api\Middleware\Action;
use Stu\Module\Api\Middleware\Response\JsonResponseInterface;
use Stu\Module\Api\Middleware\SessionInterface;
use Stu\Module\Research\TechlistRetrieverInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;

final class GetResearch extends Action
{
    private SessionInterface $session;

    private TechlistRetrieverInterface $techlistRetriever;

    private ResearchedRepositoryInterface $researchedRepository;

    public function __construct(
        SessionInterface $session,
        TechlistRetrieverInterface $techlistRetriever,
        ResearchedRepositoryInterface $researchedRepository
    ) {
        $this->session = $session;
        $this->techlistRetriever = $techlistRetriever;
        $this->researchedRepository = $researchedRepository;
    }

    protected function action(
        ServerRequestInterface $request,
        JsonResponseInterface $response,
        array $args
    ): JsonResponseInterface {
        $user = $this->session->getUser();
        $researchId = (int)$args['researchId'];

        $tech = $this->techlistRetriever->getResearchList($user)[$researchId] ?? null;

        if ($tech === null) {
            $researched = $this->researchedRepository->getFor($researchId, $user->getId());

            if ($researched === null) {
                return $response->withError(
                    ErrorCodeEnum::NOT_FOUND,
                    'Research not found'
                );
            }

            $tech = $researched->getResearch();
        }

        return $response->withData([
            'researchId' => $tech->getId(),
            'name' => $tech->getName(),
            'description' => $tech->getDescription(),
            'points' => $tech->getPoints(),
            'commodity' => [
                'commodityId' => $tech->getCommodity()->getId(),
                'name' => $tech->getCommodity()->getName()
            ]
        ]);
    }
}
