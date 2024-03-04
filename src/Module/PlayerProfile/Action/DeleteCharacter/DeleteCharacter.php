<?php

declare(strict_types=1);

namespace Stu\Module\PlayerProfile\Action\DeleteCharacter;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\UserCharactersRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class DeleteCharacter implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DELETE_CHARACTER';

    private DeleteCharacterRequestInterface $deleteCharacterRequest;
    private UserCharactersRepositoryInterface $userCharactersRepository;
    private UserRepositoryInterface $userRepository;

    public function __construct(
        DeleteCharacterRequestInterface $deleteCharacterRequest,
        UserRepositoryInterface $userRepository,
        UserCharactersRepositoryInterface $userCharactersRepository
    ) {
        $this->deleteCharacterRequest = $deleteCharacterRequest;
        $this->userCharactersRepository = $userCharactersRepository;
        $this->userRepository = $userRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $characterId = $this->deleteCharacterRequest->getCharacterId();
        $character = $this->userCharactersRepository->find($characterId);
        $fallbackUser = $this->userRepository->getFallbackUser();

        if (!$character || $character->getUser() !== $game->getUser()) {
            $game->addInformation(_('Charakter nicht gefunden oder kein Zugriff.'));
            return;
        }

        $character->setUser($fallbackUser);
        $character->setFormerUserId($game->getUser()->getId());
        $this->userCharactersRepository->save($character);

        $game->addInformation(_('Der Charakter wurde erfolgreich entfernt.'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
