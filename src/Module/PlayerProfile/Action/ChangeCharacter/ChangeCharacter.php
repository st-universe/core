<?php

declare(strict_types=1);

namespace Stu\Module\PlayerProfile\Action\ChangeCharacter;

use Noodlehaus\ConfigInterface;
use RuntimeException;
use Stu\Lib\Upload\PngUpload;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Orm\Repository\UserCharacterRepositoryInterface;

final class ChangeCharacter implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_CHANGE_CHARACTER';

    public function __construct(private ChangeCharacterRequestInterface $request, private UserCharacterRepositoryInterface $userCharactersRepository, private ConfigInterface $config, private PngUpload $pngUpload) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $characterId = $this->request->getCharacterId();
        $character = $this->userCharactersRepository->find($characterId);

        if (!$character || $character->getUser()->getId() !== $context->getUser()->getId()) {
            $context->getInfo()->addInformation(_('Charakter nicht gefunden oder kein Zugriff.'));
            return;
        }


        $name = $this->request->getName();
        $description = $this->request->getDescription();
        $avatarFile = $this->request->getAvatar();


        if ($name === '' || $name === '0' || ($description === '' || $description === '0')) {
            $context->getInfo()->addInformation(_('Name und Beschreibung dürfen nicht leer sein.'));
            return;
        }

        if (!empty($avatarFile['name'])) {
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

        $context->getInfo()->addInformation(_('Der Charakter wurde erfolgreich bearbeitet.'));
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
