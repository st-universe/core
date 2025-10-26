<?php

declare(strict_types=1);

namespace Stu\Module\PlayerProfile\Action\DeleteCharacter;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\UserCharacterRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class DeleteCharacter implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_DELETE_CHARACTER';

    public function __construct(private DeleteCharacterRequestInterface $deleteCharacterRequest, private UserRepositoryInterface $userRepository, private UserCharacterRepositoryInterface $userCharactersRepository) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $characterId = $this->deleteCharacterRequest->getCharacterId();
        $character = $this->userCharactersRepository->find($characterId);
        $fallbackUser = $this->userRepository->getFallbackUser();

        if (!$character || $character->getUser() !== $game->getUser()) {
            $game->getInfo()->addInformation(_('Charakter nicht gefunden oder kein Zugriff.'));
            return;
        }

        $character->setUser($fallbackUser);
        $character->setFormerUserId($game->getUser()->getId());
        $this->userCharactersRepository->save($character);

        $game->getInfo()->addInformation(_('Der Charakter wurde erfolgreich entfernt.'));
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
