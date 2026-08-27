<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting\Action\CreateCrewRace;

use finfo;
use Noodlehaus\ConfigInterface;
use request;
use Stu\Component\Crew\CrewRaceInput;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\PlayerSetting\View\ShowCrewRaceManagement\ShowCrewRaceManagement;
use Stu\Orm\Entity\CrewRace;
use Stu\Orm\Entity\Faction;
use Stu\Orm\Repository\CrewRaceRepositoryInterface;
use Stu\Orm\Repository\FactionRepositoryInterface;

final class CreateCrewRace implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_CREATE_CREW_RACE';

    private const int MAX_IMAGE_SIZE = 5120;
    private const int IMAGE_WIDTH = 51;
    private const int IMAGE_HEIGHT = 52;

    public function __construct(
        private readonly CrewRaceRepositoryInterface $crewRaceRepository,
        private readonly FactionRepositoryInterface $factionRepository,
        private readonly ConfigInterface $config
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowCrewRaceManagement::VIEW_IDENTIFIER);

        $user = $game->getUser();
        if (!$game->isAdmin() && count($this->crewRaceRepository->getByCreatorUserId($user->getId())) >= 3) {
            $game->getInfo()->addInformation(_('Du kannst maximal drei eigene Crew-Rassen erstellen'));
            return;
        }

        $description = trim((string)request::postString('crew_race_name'));
        if (!CrewRaceInput::isValidDescription($description)) {
            $game->getInfo()->addInformation(_('Der Name muss mit einem Großbuchstaben beginnen und darf nur Buchstaben, einzelne Leerzeichen sowie einzelne Apostrophe oder Backticks enthalten'));
            return;
        }
        $maleRatio = filter_var(request::postString('crew_race_male_ratio'), FILTER_VALIDATE_INT);
        if ($maleRatio === false || $maleRatio < 0 || $maleRatio > 100) {
            $game->getInfo()->addInformation(_('Das Männerverhältnis muss eine Zahl zwischen 0 und 100 sein'));
            return;
        }

        $gfxPath = CrewRaceInput::normalizeDefine((string)(request::postString('crew_race_define') ?: $description));
        if (!CrewRaceInput::isValidDefine($gfxPath)) {
            $game->getInfo()->addInformation(_('Die Grafikdefinition darf nur Großbuchstaben und einzelne Unterstriche enthalten'));
            return;
        }
        if ($this->crewRaceRepository->getByGfxPath($gfxPath) !== null) {
            $game->getInfo()->addInformation(_('Eine Crew-Rasse mit dieser Grafikdefinition existiert bereits'));
            return;
        }

        $chance = filter_var(request::postString('crew_race_chance'), FILTER_VALIDATE_INT);
        if ($chance === false || $chance < 1 || $chance > 100) {
            $game->getInfo()->addInformation(_('Die Zufallsrate muss eine Zahl zwischen 1 und 100 sein'));
            return;
        }

        $shared = request::postString('crew_race_shared') === '1';
        $graphics = $this->getValidatedGraphics((int)$maleRatio, $game);
        if ($graphics === null) {
            return;
        }

        $crewRace = $this->crewRaceRepository->prototype();
        $crewRace
            ->setDescription($description)
            ->setGfxPath($gfxPath)
            ->setMaleRatio((int)$maleRatio)
            ->setChance((int)$chance)
            ->setCreatorUserId($user->getId())
            ->setShared($shared)
            ->setCivil(true)
            ->setFactionIds($this->getFactionIds($user->getFactionId(), $shared));

        if (!$this->storeGraphics($crewRace, $graphics, $game)) {
            return;
        }

        $this->crewRaceRepository->save($crewRace);
        $game->getInfo()->addInformation(_('Die Crew-Rasse wurde zur Freigabe eingereicht'));
    }

    /** @return list<int> */
    private function getFactionIds(int $ownFactionId, bool $shared): array
    {
        $factionIds = [$ownFactionId];
        if (!$shared) {
            return $factionIds;
        }

        $playableFactionIds = array_map(
            static fn (Faction $faction): int => $faction->getId(),
            $this->factionRepository->getByChooseable(true)
        );
        foreach (request::postArray('crew_race_factions') as $factionId) {
            $factionId = (int)$factionId;
            if (in_array($factionId, $playableFactionIds, true)) {
                $factionIds[] = $factionId;
            }
        }

        return array_values(array_unique($factionIds));
    }

    /** @return array<string, array<int, array{name: string, tmp_name: string, size: int, error: int}>>|null */
    private function getValidatedGraphics(int $maleRatio, GameControllerInterface $game): ?array
    {
        $genders = [];
        if ($maleRatio > 0) {
            $genders[] = 'm';
        }
        if ($maleRatio < 100) {
            $genders[] = 'w';
        }

        $graphics = [];
        foreach ($genders as $gender) {
            foreach (range(1, 6) as $imageType) {
                $file = $this->getGraphicFile($gender, $imageType);
                if ($file === null || $file['error'] !== UPLOAD_ERR_OK) {
                    $game->getInfo()->addInformationf(_('Für %s Grafik %d wurde keine Datei hochgeladen'), $gender === 'm' ? _('Männer') : _('Frauen'), $imageType);
                    return null;
                }
                if (!$this->isValidGraphic($file)) {
                    $game->getInfo()->addInformationf(_('Grafik %d für %s muss eine PNG-Datei mit 51x52 Pixeln und höchstens 5 KB sein'), $imageType, $gender === 'm' ? _('Männer') : _('Frauen'));
                    return null;
                }
                $graphics[$gender][$imageType] = $file;
            }
        }

        return $graphics;
    }

    /** @return array{name: string, tmp_name: string, size: int, error: int}|null */
    private function getGraphicFile(string $gender, int $imageType): ?array
    {
        $files = $_FILES['crew_graphics'] ?? null;
        if (!is_array($files)) {
            return null;
        }

        $name = $files['name'][$gender][$imageType] ?? null;
        $tmpName = $files['tmp_name'][$gender][$imageType] ?? null;
        $size = $files['size'][$gender][$imageType] ?? null;
        $error = $files['error'][$gender][$imageType] ?? null;
        if (!is_string($name) || !is_string($tmpName) || !is_int($size) || !is_int($error)) {
            return null;
        }

        return [
            'name' => $name,
            'tmp_name' => $tmpName,
            'size' => $size,
            'error' => $error
        ];
    }

    /** @param array{name: string, tmp_name: string, size: int, error: int} $file */
    private function isValidGraphic(array $file): bool
    {
        if ($file['size'] === 0 || $file['size'] > self::MAX_IMAGE_SIZE || strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) !== 'png') {
            return false;
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        if ($finfo->file($file['tmp_name']) !== 'image/png') {
            return false;
        }

        $imageSize = @getimagesize($file['tmp_name']);

        return is_array($imageSize)
            && $imageSize[0] === self::IMAGE_WIDTH
            && $imageSize[1] === self::IMAGE_HEIGHT
            && $imageSize[2] === IMAGETYPE_PNG;
    }

    /** @param array<string, array<int, array{name: string, tmp_name: string, size: int, error: int}>> $graphics */
    private function storeGraphics(CrewRace $crewRace, array $graphics, GameControllerInterface $game): bool
    {
        $basePath = rtrim((string)$this->config->get('game.webroot'), '/\\')
            . DIRECTORY_SEPARATOR
            . trim((string)$this->config->get('game.user_avatar_path'), '/\\')
            . DIRECTORY_SEPARATOR
            . 'crew'
            . DIRECTORY_SEPARATOR
            . $crewRace->getGfxPath();

        foreach ($graphics as $gender => $images) {
            $directory = $basePath . DIRECTORY_SEPARATOR . $gender;
            if (!is_dir($directory) && !mkdir($directory, 0o755, true) && !is_dir($directory)) {
                $game->getInfo()->addInformation(_('Das Zielverzeichnis für die Crew-Grafiken konnte nicht erstellt werden'));
                return false;
            }
            foreach ($images as $imageType => $file) {
                if (!move_uploaded_file($file['tmp_name'], sprintf('%s/1_%d.png', $directory, $imageType))) {
                    $game->getInfo()->addInformation(_('Eine Crew-Grafik konnte nicht gespeichert werden'));
                    return false;
                }
            }
        }

        return true;
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
