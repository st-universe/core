<?php

declare(strict_types=1);

namespace Stu\Module\PlayerProfile\Action\CreateCharacter;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\UserCharactersRepositoryInterface;
use Noodlehaus\ConfigInterface;

final class CreateCharacter implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_CREATE_CHARACTER';

    private CreateCharacterRequestInterface $request;
    private UserCharactersRepositoryInterface $userCharactersRepository;
    private ConfigInterface $config;

    public function __construct(
        CreateCharacterRequestInterface $request,
        UserCharactersRepositoryInterface $userCharactersRepository,
        ConfigInterface $config
    ) {
        $this->request = $request;
        $this->userCharactersRepository = $userCharactersRepository;
        $this->config = $config;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser();
        $name = $this->request->getName();
        $description = $this->request->getDescription();
        $avatarFile = $this->request->getAvatar();

        if (empty($name)) {
            $game->addInformation(_('Der Charaktername darf nicht leer sein.'));
            return;
        }

        if (empty($description)) {
            $game->addInformation(_('Die Beschreibung darf nicht leer sein.'));
            return;
        }

        if (empty($avatarFile['name'])) {
            $game->addInformation(_('Ein Bild muss hochgeladen werden.'));
            return;
        }

        if ($avatarFile['type'] !== 'image/png') {
            $game->addInformation(_('Es können nur Bilder im PNG-Format hochgeladen werden'));
            return;
        }
        if ($avatarFile['size'] > 200000) {
            $game->addInformation(_('Die maximale Dateigröße liegt bei 200 Kilobyte'));
            return;
        }
        if ($avatarFile['size'] === 0) {
            $game->addInformation(_('Die Datei ist leer'));
            return;
        }

        $imageNameWithExtension = substr(md5(time() . "_" . $avatarFile['name']), 0, 32) . '.png';


        $imageName = substr($imageNameWithExtension, 0, -4);



        $uploadPath = $this->config->get('game.character_avatar_path') . $imageNameWithExtension;

        $uploadDir = $this->config->get('game.character_avatar_path');
        $uploadPath = $uploadDir . '/' . $imageNameWithExtension;

        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true) && !is_dir($uploadDir)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $uploadDir));
        }

        if (!move_uploaded_file($avatarFile['tmp_name'], $uploadPath)) {
            $game->addInformation(_('Fehler beim Speichern des Avatars.'));
            return;
        }


        $character = $this->userCharactersRepository->prototype();
        $character->setAvatar($imageName);
        $character->setUser($userId);
        $character->setName($name);
        $character->setDescription($description);

        $this->userCharactersRepository->save($character);

        $game->addInformation(_('Der Charakter wurde erfolgreich erstellt.'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
