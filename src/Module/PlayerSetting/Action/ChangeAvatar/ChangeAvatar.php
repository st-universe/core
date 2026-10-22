<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting\Action\ChangeAvatar;

use GdImage;
use Noodlehaus\ConfigInterface;
use request;
use RuntimeException;
use Stu\Component\Player\Settings\UserSettingsProviderInterface;
use Stu\Lib\Upload\PngUpload;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\PlayerSetting\Lib\ChangeUserSettingInterface;
use Stu\Module\PlayerSetting\Lib\UserSettingEnum;

final class ChangeAvatar implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_CHANGE_AVATAR';

    public function __construct(
        private readonly ChangeUserSettingInterface $changerUserSetting,
        private readonly UserSettingsProviderInterface $userSettingsProvider,
        private readonly ConfigInterface $config,
        private readonly PngUpload $pngUpload
    ) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        if (!request::isPost()) {
            return;
        }

        $file = $_FILES['avatar'];
        $img = $this->pngUpload->readUploaded($file['tmp_name'], $file['error'], 200000, 2048, 2048, 4000000);
        if ($img === null) {
            $context->getInfo()->addInformation(_('Es können nur gültige PNG-Bilder bis 200 Kilobyte und 2048 Pixel je Seite hochgeladen werden'));
            return;
        }

        $user = $context->getUser();

        $avatar = $this->userSettingsProvider->getAvatar($user);
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

        $context->getInfo()->addInformation(_('Das Bild wurde erfolgreich hochgeladen'));
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
