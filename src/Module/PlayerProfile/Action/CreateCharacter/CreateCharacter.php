<?php

declare(strict_types=1);

namespace Stu\Module\PlayerProfile\Action\CreateCharacter;

use Noodlehaus\ConfigInterface;
use RuntimeException;
use Stu\Lib\Upload\PngUpload;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Orm\Repository\UserCharacterRepositoryInterface;

final class CreateCharacter implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_CREATE_CHARACTER';

    public function __construct(private CreateCharacterRequestInterface $request, private UserCharacterRepositoryInterface $userCharactersRepository, private ConfigInterface $config, private PngUpload $pngUpload) {}

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

        $image = $this->pngUpload->readUploaded(
            $avatarFile['tmp_name'],
            $avatarFile['error'],
            1000000,
            2048,
            2048,
            4000000
        );
        if ($image === null) {
            $context->getInfo()->addInformation(_('Es können nur gültige PNG-Bilder bis 1 Megabyte und 2048 Pixel je Seite hochgeladen werden'));
            return;
        }

        $imageName = bin2hex(random_bytes(16));
        $imageNameWithExtension = $imageName . '.png';
        $uploadDir = $this->config->get('game.character_avatar_path');
        $uploadPath = $uploadDir . '/' . $imageNameWithExtension;

        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0o777, true) && !is_dir($uploadDir)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $uploadDir));
        }

        if (!$this->pngUpload->write($image, $uploadPath, 1000000)) {
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
