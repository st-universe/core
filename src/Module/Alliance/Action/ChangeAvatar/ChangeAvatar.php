<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\ChangeAvatar;

use AccessViolation;
use Noodlehaus\ConfigInterface;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Alliance\View\Edit\Edit;
use Stu\Orm\Repository\AllianceRepositoryInterface;

final class ChangeAvatar implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_CHANGE_AVATAR';

    private $allianceActionManager;

    private $allianceRepository;

    private $config;

    public function __construct(
        AllianceActionManagerInterface $allianceActionManager,
        AllianceRepositoryInterface $allianceRepository,
        ConfigInterface $config
    ) {
        $this->allianceActionManager = $allianceActionManager;
        $this->allianceRepository = $allianceRepository;
        $this->config = $config;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $alliance = $user->getAlliance();

        if (!$this->allianceActionManager->mayEdit((int) $alliance->getId(), $user->getId())) {
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
            @unlink(
                sprintf(
                    '%s/%s/%s.png',
                    $this->config->get('game.webroot'),
                    $this->config->get('game.user_avatar_path'),
                    $alliance->getAvatar()
                )
            );
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
        imagepng(
            $newImage,
            sprintf(
                '%s/%s/%s.png',
                $this->config->get('game.webroot'),
                $this->config->get('game.user_avatar_path'),
                $imageName
            )
        );
        $alliance->setAvatar($imageName);

        $this->allianceRepository->save($alliance);

        $game->addInformation(_('Das Bild wurde erfolgreich hochgeladen'));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
