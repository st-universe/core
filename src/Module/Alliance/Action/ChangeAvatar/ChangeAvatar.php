<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\ChangeAvatar;

use Noodlehaus\ConfigInterface;
use RuntimeException;
use Stu\Component\Alliance\Enum\AllianceJobPermissionEnum;
use Stu\Exception\AccessViolationException;
use Stu\Lib\Upload\PngUpload;
use Stu\Module\Alliance\Lib\AllianceJobManagerInterface;
use Stu\Module\Alliance\View\Edit\Edit;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Orm\Repository\AllianceRepositoryInterface;

final class ChangeAvatar implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_CHANGE_AVATAR';

    public function __construct(private AllianceJobManagerInterface $allianceJobManager, private AllianceRepositoryInterface $allianceRepository, private ConfigInterface $config, private PngUpload $pngUpload) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $user = $context->getUser();
        $alliance = $user->getAlliance();

        if ($alliance === null) {
            throw new AccessViolationException();
        }

        if (!$this->allianceJobManager->hasUserPermission($user, $alliance, AllianceJobPermissionEnum::EDIT_ALLIANCE)) {
            throw new AccessViolationException();
        }

        $context->setView(Edit::VIEW_IDENTIFIER);

        $file = $_FILES['avatar'];
        $img = $this->pngUpload->readUploaded($file['tmp_name'], $file['error'], 200000, 600, 150, 90000);
        if ($img === null) {
            $context->getInfo()->addInformation(_('Es können nur gültige PNG-Bilder bis 200 Kilobyte, 600 Pixel Breite und 150 Pixel Höhe hochgeladen werden'));
            return;
        }

        $imageName = md5($alliance->getId() . "_" . time());

        $newImage = imagecreatetruecolor(imagesx($img), imagesy($img));
        if ($newImage === false) {
            return;
        }

        if ($alliance->hasAvatar()) {
            $result = @unlink(
                sprintf(
                    '%s/%s/%s.png',
                    $this->config->get('game.webroot'),
                    $this->config->get('game.alliance_avatar_path'),
                    $alliance->getAvatar()
                )
            );

            if ($result === false) {
                throw new RuntimeException('old alliance avatar could not be deleted');
            }
        }

        imagecopy($newImage, $img, 0, 0, 0, 0, imagesx($img), imagesy($img));
        imagepng(
            $newImage,
            sprintf(
                '%s/%s/%s.png',
                $this->config->get('game.webroot'),
                $this->config->get('game.alliance_avatar_path'),
                $imageName
            )
        );
        $alliance->setAvatar($imageName);

        $this->allianceRepository->save($alliance);

        $context->getInfo()->addInformation(_('Das Bild wurde erfolgreich hochgeladen'));
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
