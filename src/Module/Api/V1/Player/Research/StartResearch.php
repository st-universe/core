<?php

declare(strict_types=1);

namespace Stu\Module\Api\V1\Player\Research;

use Psr\Http\Message\ServerRequestInterface;
use Stu\Component\ErrorHandling\ErrorCodeEnum;
use Stu\Module\Api\Middleware\Action;
use Stu\Module\Api\Middleware\Request\JsonSchemaRequestInterface;
use Stu\Module\Api\Middleware\Response\JsonResponseInterface;
use Stu\Module\Api\Middleware\SessionInterface;
use Stu\Module\Research\TechlistRetrieverInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;

final class StartResearch extends Action
{
    public const JSON_SCHEMA_FILE = __DIR__ . '/StartResearch.json';

    private SessionInterface $session;

    private ResearchedRepositoryInterface $researchedRepository;

    private JsonSchemaRequestInterface $jsonSchemaRequest;

    private TechlistRetrieverInterface $techlistRetriever;

    public function __construct(
        SessionInterface $session,
        ResearchedRepositoryInterface $researchedRepository,
        JsonSchemaRequestInterface $jsonSchemaRequest,
        TechlistRetrieverInterface $techlistRetriever
    ) {
        $this->session = $session;
        $this->researchedRepository = $researchedRepository;
        $this->jsonSchemaRequest = $jsonSchemaRequest;
        $this->techlistRetriever = $techlistRetriever;
    }

    protected function action(
        ServerRequestInterface $request,
        JsonResponseInterface $response,
        array $args
    ): JsonResponseInterface {
        $user = $this->session->getUser();

        $researchId = $this->jsonSchemaRequest->getData($this)->researchId;

        $research = $this->techlistRetriever->getResearchList($user)[$researchId] ?? null;
        if ($research === null) {
            return $response->withError(
                ErrorCodeEnum::NOT_FOUND,
                'Research not found'
            );
        }
        $current_research = $this->researchedRepository->getCurrentResearch($user->getId());

        if ($current_research !== null) {
            $this->researchedRepository->delete($current_research);
        }

        $researched = $this->researchedRepository->prototype()
            ->setActive($research->getPoints())
            ->setUser($user)
            ->setResearch($research);

        $this->researchedRepository->save($researched);

        return $response->withData(true);
    }
}
