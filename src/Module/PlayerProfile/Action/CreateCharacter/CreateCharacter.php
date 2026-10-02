<?php

declare(strict_types=1);

namespace Stu\Module\PlayerProfile\Action\CreateCharacter;

use Noodlehaus\ConfigInterface;
use RuntimeException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Orm\Repository\UserCharacterRepositoryInterface;

final class CreateCharacter implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_CREATE_CHARACTER';

    public function __construct(private CreateCharacterRequestInterface $request, private UserCharacterRepositoryInterface $userCharactersRepository, private ConfigInterface $config) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $userId = $context->getUser();
        $name = $this->request->getName();
        $description = $this->request->getDescription();
        $avatarFile = $this->request->getAvatar();

        if ($name === '' || $name === '0') {
            $context->getInfo()->addInformation(_('Der Charaktername darf nicht leer sein.'));
            return;
        }

        if ($description === '' || $description === '0') {
            $context->getInfo()->addInformation(_('Die Beschreibung darf nicht leer sein.'));
            return;
        }

        if (empty($avatarFile['name'])) {
            $context->getInfo()->addInformation(_('Ein Bild muss hochgeladen werden.'));
            return;
        }

        if ($avatarFile['type'] !== 'image/png') {
            $context->getInfo()->addInformation(_('Es können nur Bilder im PNG-Format hochgeladen werden'));
            return;
        }
        if ($avatarFile['size'] > 1000000) {
            $context->getInfo()->addInformation(_('Die maximale Dateigröße liegt bei 1 Megabyte'));
            return;
        }
        if ($avatarFile['size'] === 0) {
            $context->getInfo()->addInformation(_('Die Datei ist leer'));
            return;
        }

        $imageNameWithExtension = substr(md5(time() . "_" . $avatarFile['name']), 0, 32) . '.png';


        $imageName = substr($imageNameWithExtension, 0, -4);



        $uploadPath = $this->config->get('game.character_avatar_path') . $imageNameWithExtension;

        $uploadDir = $this->config->get('game.character_avatar_path');
        $uploadPath = $uploadDir . '/' . $imageNameWithExtension;

        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0o777, true) && !is_dir($uploadDir)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $uploadDir));
        }

        if (!move_uploaded_file($avatarFile['tmp_name'], $uploadPath)) {
            $context->getInfo()->addInformation(_('Fehler beim Speichern des Avatars.'));
            return;
        }


        $character = $this->userCharactersRepository->prototype();
        $character->setAvatar($imageName);
        $character->setUser($userId);
        $character->setName($name);
        $character->setDescription($description);

        $this->userCharactersRepository->save($character);

        $context->getInfo()->addInformation(_('Der Charakter wurde erfolgreich erstellt.'));
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
