<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting\Action\ChangeAvatar;

use Override;
use Exception;
use GdImage;
use Noodlehaus\ConfigInterface;
use RuntimeException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\PlayerSetting\Lib\ChangeUserSettingInterface;
use Stu\Module\PlayerSetting\Lib\UserSettingEnum;

final class ChangeAvatar implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_CHANGE_AVATAR';

    public function __construct(private ChangeUserSettingInterface $changerUserSetting, private ConfigInterface $config)
    {
    }

    #[Override]
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

        $avatar = $user->getAvatar();
        if ($avatar !== '') {
            $path = sprintf(
                '/%s/%s.png',
                $this->config->get('game.user_avatar_path'),
                $avatar
            );
            if (file_exists($path)) {
                $result = @unlink($path);

                if ($result === false) {
                    throw new RuntimeException('old alliance avatar could not be deleted');
                }
            }
        }
        $imageName = md5($user->getId() . "_" . time());

        try {
            $img = imagecreatefrompng($file['tmp_name']);
        } catch (Exception) {
            $game->addInformation(_('Fehler: Das Bild konnte nicht als PNG geladen werden!'));
            return;
        }

        if (!$img) {
            $game->addInformation(_('Fehler: Das Bild konnte nicht als PNG geladen werden!'));
            return;
        }

        /** @var GdImage $newImage */
        $newImage = imagecreatetruecolor(150, 150);
        imagecopy($newImage, $img, 0, 0, 0, 0, 150, 150);
        imagepng(
            $newImage,
            sprintf(
                '%s/%s/%s.png',
                $this->config->get('game.webroot'),
                $this->config->get('game.user_avatar_path'),
                $imageName
            )
        );

        $this->changerUserSetting->change(
            $user,
            UserSettingEnum::AVATAR,
            $imageName
        );

        $game->addInformation(_('Das Bild wurde erfolgreich hochgeladen'));
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
