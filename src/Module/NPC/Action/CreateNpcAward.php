<?php

declare(strict_types=1);

namespace Stu\Module\NPC\Action;

use Noodlehaus\ConfigInterface;
use request;
use RuntimeException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\NPC\View\ShowTools\ShowTools;
use Stu\Orm\Repository\AwardRepositoryInterface;
use Stu\Orm\Repository\NPCLogRepositoryInterface;

final class CreateNpcAward implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_CREATE_NPC_AWARD';

    private const int MAX_FILE_SIZE = 1000000;
    private const int AWARD_IMAGE_SIZE = 100;

    public function __construct(
        private AwardRepositoryInterface $awardRepository,
        private NPCLogRepositoryInterface $npcLogRepository,
        private ConfigInterface $config
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowTools::VIEW_IDENTIFIER);
        $currentUser = $game->getUser();

        if (!$game->isAdmin() && !$game->isNpc()) {
            $game->getInfo()->addInformation(_(
                '[b][color=#ff2626]Aktion nicht möglich, Spieler ist kein Admin/NPC![/color][/b]'
            ));
            return;
        }

        $description = trim((string) request::postString('award_description'));
        if ($description === '' || $description === '0') {
            $game->getInfo()->addInformation('Beschreibung fehlt');
            return;
        }

        $prestigeInput = trim((string) request::postString('award_prestige'));
        $prestige = filter_var(
            $prestigeInput,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        if ($prestige === false) {
            $game->getInfo()->addInformation('Prestigewert muss eine positive Zahl sein');
            return;
        }

        $reason = trim((string) request::postString('reason'));
        if (!$game->isAdmin() && $currentUser->isNpc() && $reason === '') {
            $game->getInfo()->addInformation('Grund fehlt');
            return;
        }

        $file = $_FILES['award_image'] ?? null;
        if (
            !is_array($file)
            || !isset($file['error'], $file['name'], $file['size'], $file['tmp_name'])
        ) {
            $game->getInfo()->addInformation('Es wurde keine Datei hochgeladen');
            return;
        }

        if ((int) $file['error'] !== UPLOAD_ERR_OK) {
            $game->getInfo()->addInformation('Fehler beim Dateiupload');
            return;
        }

        if ((string) $file['name'] === '') {
            $game->getInfo()->addInformation('Es wurde keine Datei hochgeladen');
            return;
        }

        $fileSize = (int) $file['size'];
        if ($fileSize > self::MAX_FILE_SIZE) {
            $game->getInfo()->addInformation('Die maximale Dateigröße liegt bei 1 Megabyte');
            return;
        }
        if ($fileSize === 0) {
            $game->getInfo()->addInformation('Die Datei ist leer');
            return;
        }

        $imageInfo = @getimagesize((string) $file['tmp_name']);
        if ($imageInfo === false || $imageInfo['mime'] !== 'image/png') {
            $game->getInfo()->addInformation('Es können nur Bilder im PNG-Format hochgeladen werden');
            return;
        }

        if (
            $imageInfo[0] !== self::AWARD_IMAGE_SIZE
            || $imageInfo[1] !== self::AWARD_IMAGE_SIZE
        ) {
            $game->getInfo()->addInformation('Das Bild muss exakt 100x100 Pixel groß sein');
            return;
        }

        $awardId = $this->awardRepository->getNextNpcAwardId();

        $uploadDirectory = sprintf(
            '%s/%s',
            rtrim((string) $this->config->get('game.webroot'), '/\\'),
            'npcawards'
        );
        if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0o777, true) && !is_dir($uploadDirectory)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $uploadDirectory));
        }

        $uploadPath = sprintf('%s/%d.png', $uploadDirectory, $awardId);
        if (file_exists($uploadPath)) {
            $game->getInfo()->addInformation('Dateiname für den Award ist bereits belegt');
            return;
        }

        if (!move_uploaded_file((string) $file['tmp_name'], $uploadPath)) {
            $game->getInfo()->addInformation('Fehler beim Speichern des Awards');
            return;
        }

        $award = $this->awardRepository->prototype();
        $award
            ->setId($awardId)
            ->setPrestige($prestige)
            ->setDescription($description)
            ->setIsNpc(true)
            ->setUser($currentUser);

        $this->awardRepository->save($award);

        $logText = sprintf(
            '%s hat den NPC Award "%s" (%d) mit %d Prestige erstellt. Grund: %s',
            $currentUser->getName(),
            $description,
            $awardId,
            $prestige,
            $reason !== '' ? $reason : '-'
        );

        if ($currentUser->isNpc()) {
            $this->createEntry($logText, $currentUser->getId());
        }

        $game->getInfo()->addInformation(sprintf(
            'NPC Award %d wurde erfolgreich erstellt und hochgeladen',
            $awardId
        ));
    }

    private function createEntry(string $text, int $userId): void
    {
        $entry = $this->npcLogRepository->prototype();
        $entry->setText($text);
        $entry->setSourceUserId($userId);
        $entry->setDate(time());

        $this->npcLogRepository->save($entry);
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
