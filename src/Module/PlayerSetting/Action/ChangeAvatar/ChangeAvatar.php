<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting\Action\ChangeAvatar;

use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;

final class ChangeAvatar implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_CHANGE_AVATAR';


    public function handle(GameControllerInterface $game): void
    {
        $file = $_FILES['avatar'];
        if ($file['type'] !== 'image/png') {
            $game->addInformation(_('Es können nur Bilder im PNG-Format hochgeladen werden'));
            return;
        }
        if ($file['size'] > 200000) {
            $game->addInformation(_('Die maximale Dateigröße liegt bei 200 Kilobyte'));
            return;
        }
        if ($file['size'] === 0) {
            $game->addInformation(_('Die Datei ist leer'));
            return;
        }

        $user = $game->getUser();

        if ($user->getAvatar() !== '') {
            @unlink($user->getFullAvatarPath());
        }
        $imageName = md5($user->getId() . "_" . time());
        $img = imagecreatefrompng($file['tmp_name']);
        $newImage = imagecreatetruecolor(150, 150);
        imagecopy($newImage, $img, 0, 0, 0, 0, 150, 150);
        imagepng($newImage, AVATAR_USER_PATH_INTERNAL . $imageName . ".png");

        $user->setAvatar($imageName);
        $user->save();

        $game->addInformation(_('Das Bild wurde erfolgreich hochgeladen'));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
