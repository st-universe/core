<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting\Action\ChangeAvatar;

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
        $directory = sprintf('%s/%s', rtrim($this->config->get('game.webroot'), '/'), trim($this->config->get('game.user_avatar_path'), '/'));
        $imageName = hash('sha256', 'user-avatar-' . $user->getId());
        $path = $directory . '/' . $imageName . '.png';
        $temporaryPath = tempnam($directory, 'avatar-');
        if ($temporaryPath === false) {
            throw new RuntimeException('Avatar could not be written');
        }
        $newImage = imagecreatetruecolor(150, 150);
        if ($newImage === false) {
            unlink($temporaryPath);
            return;
        }
        imagecopy($newImage, $img, 0, 0, 0, 0, 150, 150);
        if (!imagepng($newImage, $temporaryPath) || !chmod($temporaryPath, 0o644) || !rename($temporaryPath, $path)) {
            unlink($temporaryPath);
            throw new RuntimeException('Avatar could not be replaced');
        }
        if ($avatar !== '' && $avatar !== $imageName && preg_match('/^(?:[a-f0-9]{32}|[a-f0-9]{64})$/D', $avatar) === 1) {
            $oldPath = $directory . '/' . $avatar . '.png';
            if (is_file($oldPath) && !@unlink($oldPath) && is_file($oldPath)) {
                throw new RuntimeException('Old avatar could not be deleted');
            }
        }

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
