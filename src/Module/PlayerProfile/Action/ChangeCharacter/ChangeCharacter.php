<?php

declare(strict_types=1);

namespace Stu\Module\PlayerProfile\Action\ChangeCharacter;

use Laminas\Mail\Exception\RuntimeException;
use Noodlehaus\ConfigInterface;
use Override;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\UserCharactersRepositoryInterface;

final class ChangeCharacter implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_CHANGE_CHARACTER';

    public function __construct(private ChangeCharacterRequestInterface $request, private UserCharactersRepositoryInterface $userCharactersRepository, private ConfigInterface $config)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $characterId = $this->request->getCharacterId();
        $character = $this->userCharactersRepository->find($characterId);

        if (!$character || $character->getUser() !== $game->getUser()) {
            $game->addInformation(_('Charakter nicht gefunden oder kein Zugriff.'));
            return;
        }


        $name = $this->request->getName();
        $description = $this->request->getDescription();
        $avatarFile = $this->request->getAvatar();


        if ($name === '' || $name === '0' || ($description === '' || $description === '0')) {
            $game->addInformation(_('Name und Beschreibung dürfen nicht leer sein.'));
            return;
        }

        if (!empty($avatarFile['name'])) {
            if ($avatarFile['type'] !== 'image/png') {
                $game->addInformation(_('Es können nur Bilder im PNG-Format hochgeladen werden'));
                return;
            }
            if ($avatarFile['size'] > 1000000) {
                $game->addInformation(_('Die maximale Dateigröße liegt bei 1 Megabyte'));
                return;
            }
            if ($avatarFile['size'] === 0) {
                $game->addInformation(_('Die Datei ist leer'));
                return;
            }

            $imageNameWithExtension = substr(md5(time() . "_" . $avatarFile['name']), 0, 32) . '.png';


            $imageName = substr($imageNameWithExtension, 0, -4);
            $uploadDir = $this->config->get('game.character_avatar_path');
            $uploadPath = $uploadDir . '/' . $imageNameWithExtension;

            if (!is_dir($uploadDir) && !mkdir($uploadDir, 0o777, true) && !is_dir($uploadDir)) {
                throw new RuntimeException(sprintf('Directory "%s" was not created', $uploadDir));
            }

            if (!move_uploaded_file($avatarFile['tmp_name'], $uploadPath)) {
                $game->addInformation(_('Fehler beim Speichern des Avatars.'));
                return;
            }

            if ($character->getAvatar()) {
                $oldAvatarPath = $uploadDir . '/' . $character->getAvatar() . '.png';
                if (file_exists($oldAvatarPath)) {
                    unlink($oldAvatarPath);
                }
            }

            $character->setAvatar($imageName);
        }

        $character->setName($name);
        $character->setDescription($description);

        $this->userCharactersRepository->save($character);

        $game->addInformation(_('Der Charakter wurde erfolgreich bearbeitet.'));
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
