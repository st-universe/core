<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\ChangeAvatar;

use AccessViolation;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Alliance\View\Edit\Edit;

final class ChangeAvatar implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_CHANGE_AVATAR';

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $alliance = $user->getAlliance();

        if (!$alliance->currentUserMayEdit()) {
            new AccessViolation;
        }

        $game->setView(Edit::VIEW_IDENTIFIER);

        $file = $_FILES['avatar'];
        if ($file['type'] != 'image/png') {
            $game->addInformation(_('Es können nur Bilder im PNG-Format hochgeladen werden'));
            return;
        }
        if ($file['size'] > 200000) {
            $game->addInformation(_('Die maximale Dateigröße liegt bei 200 Kilobyte'));
            return;
        }
        if ($file['size'] == 0) {
            $game->addInformation(_('Die Datei ist leer'));
            return;
        }
        if ($alliance->getAvatar()) {
            @unlink(AVATAR_ALLIANCE_PATH_INTERNAL . $alliance->getAvatar() . '.png');
        }
        $imageName = md5($alliance->getId() . "_" . time());

        $img = imagecreatefrompng($file['tmp_name']);

        if (imagesx($img) > 600) {
            $game->addInformation(_('Das Bild darf maximal 600 Pixel breit sein'));
            return;
        }
        if (imagesy($img) > 150) {
            $game->addInformation(_('Das Bild darf maximal 150 Pixel hoch sein'));
            return;
        }
        $newImage = imagecreatetruecolor(imagesx($img), imagesy($img));
        imagecopy($newImage, $img, 0, 0, 0, 0, imagesx($img), imagesy($img));
        imagepng($newImage, AVATAR_ALLIANCE_PATH_INTERNAL . $imageName . ".png");
        $alliance->setAvatar($imageName);
        $alliance->save();

        $game->addInformation(_('Das Bild wurde erfolgreich hochgeladen'));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
