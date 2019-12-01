<?php

declare(strict_types=1);

namespace Stu\Module\Api\V1\Common\Faction;

use Psr\Http\Message\ServerRequestInterface;
use Stu\Module\Api\Middleware\Action;
use Stu\Module\Api\Middleware\Response\JsonResponseInterface;
use Stu\Orm\Entity\FactionInterface;
use Stu\Orm\Repository\FactionRepositoryInterface;

final class GetFactions extends Action
{
    private FactionRepositoryInterface $factionRepository;

    public function __construct(
        FactionRepositoryInterface $factionRepository
    ) {
        $this->factionRepository = $factionRepository;
    }

    public function action(
        ServerRequestInterface $request,
        JsonResponseInterface $response,
        array $args
    ): JsonResponseInterface {
        return $response->withData(
            array_map(
                function (FactionInterface $faction): array {
                    return [
                        'id' => $faction->getId(),
                        'name' => $faction->getName(),
                        'description' => $faction->getDescription(),
                        'player_limit' => $faction->getPlayerLimit(),
                        'player_amount' => $faction->getPlayerAmount(),
                    ];
                },
                $this->factionRepository->getByChooseable(true)
            )
        );
    }
}
