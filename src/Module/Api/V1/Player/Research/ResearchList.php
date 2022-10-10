<?php

declare(strict_types=1);

namespace Stu\Module\Api\V1\Player\Research;

use Psr\Http\Message\ServerRequestInterface;
use Stu\Module\Api\Middleware\Action;
use Stu\Module\Api\Middleware\Response\JsonResponseInterface;
use Stu\Module\Api\Middleware\SessionInterface;
use Stu\Module\Research\TechlistRetrieverInterface;
use Stu\Orm\Entity\ResearchedInterface;
use Stu\Orm\Entity\ResearchInterface;

final class ResearchList extends Action
{
    private SessionInterface $session;

    private TechlistRetrieverInterface $techlistRetriever;

    public function __construct(
        SessionInterface $session,
        TechlistRetrieverInterface $techlistRetriever
    ) {
        $this->session = $session;
        $this->techlistRetriever = $techlistRetriever;
    }

    protected function action(
        ServerRequestInterface $request,
        JsonResponseInterface $response,
        array $args
    ): JsonResponseInterface {
        $user = $this->session->getUser();

        return $response->withData([
            'available' => array_values(
                array_map(
                    function (ResearchInterface $tech): array {
                        return [
                            'researchId' => $tech->getId(),
                            'name' => $tech->getName(),
                            'description' => $tech->getDescription(),
                            'points' => $tech->getPoints(),
                            'commodity' => [
                                'commodityId' => $tech->getCommodity()->getId(),
                                'name' => $tech->getCommodity()->getName()
                            ],
                        ];
                    },
                    $this->techlistRetriever->getResearchList($user)
                )
            ),
            'finished' => array_map(
                function (ResearchedInterface $researchedTech): array {
                    return [
                        'researchId' => $researchedTech->getResearch()->getId(),
                        'name' => $researchedTech->getResearch()->getName(),
                        'finishDate' => $researchedTech->getFinished()
                    ];
                },
                $this->techlistRetriever->getFinishedResearchList($user)
            )
        ]);
    }
}
