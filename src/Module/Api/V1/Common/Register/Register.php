<?php

declare(strict_types=1);

namespace Stu\Module\Api\V1\Common\Register;

use Noodlehaus\ConfigInterface;
use Psr\Http\Message\ServerRequestInterface;
use Stu\Component\ErrorHandling\ErrorCodeEnum;
use Stu\Component\Player\Register\PlayerCreatorInterface;
use Stu\Component\Player\Register\Exception\RegistrationException;
use Stu\Module\Api\Middleware\Action;
use Stu\Module\Api\Middleware\Request\JsonSchemaRequestInterface;
use Stu\Module\Api\Middleware\Response\JsonResponseInterface;
use Stu\Orm\Entity\FactionInterface;
use Stu\Orm\Repository\FactionRepositoryInterface;

final class Register extends Action
{
    public const JSON_SCHEMA_FILE = __DIR__ . '/register.json';

    private JsonSchemaRequestInterface $jsonSchemaRequest;

    private PlayerCreatorInterface $playerCreator;

    private FactionRepositoryInterface $factionRepository;

    private ConfigInterface $config;

    public function __construct(
        JsonSchemaRequestInterface $jsonSchemaRequest,
        PlayerCreatorInterface $playerCreator,
        FactionRepositoryInterface $factionRepository,
        ConfigInterface $config
    ) {
        $this->jsonSchemaRequest = $jsonSchemaRequest;
        $this->playerCreator = $playerCreator;
        $this->factionRepository = $factionRepository;
        $this->config = $config;
    }

    public function action(
        ServerRequestInterface $request,
        JsonResponseInterface $response,
        array $args
    ): JsonResponseInterface {
        if ($this->config->get('game.registration.enabled') === false) {
            return $response->withError(
                ErrorCodeEnum::REGISTRATION_NOT_PERMITTED,
                'The registration of new player is disabled'
            );
        }

        $data = $this->jsonSchemaRequest->getData($this);

        $factions = array_filter(
            $this->factionRepository->getByChooseable(true),
            function (FactionInterface $faction) use ($data): bool {
                return $data->factionId === $faction->getId() && $faction->hasFreePlayerSlots();
            }
        );
        if ($factions === []) {
            return $response->withError(
                ErrorCodeEnum::INVALID_FACTION,
                'No suitable faction transmitted'
            );
        }

        try {
            $this->playerCreator->createViaToken(
                $data->loginName,
                $data->emailAddress,
                current($factions),
                $data->token
            );
        } catch (RegistrationException $e) {
            return $response->withError(
                $e->getCode(),
                $e->getMessage()
            );
        }

        return $response->withData(true);
    }
}
