<?php

declare(strict_types=1);

namespace Stu\Component\Crew;

use finfo;
use Noodlehaus\ConfigInterface;
use Stu\Module\Control\GameControllerInterface;

final class CrewRaceGraphics
{
    private const int MAX_IMAGE_SIZE = 10240;
    private const int IMAGE_WIDTH = 51;
    private const int IMAGE_HEIGHT = 52;

    public function __construct(private readonly ConfigInterface $config) {}

    public function store(string $define, int $maleRatio, ?string $currentDefine, GameControllerInterface $game): bool
    {
        $baseDirectory = rtrim((string)$this->config->get('game.webroot'), '/\\')
            . '/' . trim((string)$this->config->get('game.user_avatar_path'), '/\\') . '/crew';
        $currentDirectory = $currentDefine === null ? null : $baseDirectory . '/' . $currentDefine;
        $targetDirectory = $baseDirectory . '/' . $define;
        if ($currentDirectory !== $targetDirectory && file_exists($targetDirectory)) {
            $game->getInfo()->addInformation(_('Das Zielverzeichnis für die Grafikdefinition existiert bereits'));
            return false;
        }

        $graphics = $this->getValidatedGraphics($maleRatio, $currentDirectory, $game);
        if ($graphics === null) {
            return false;
        }

        // Prepare a complete replacement before touching any existing graphics.
        $stagingDirectory = $baseDirectory . '/.upload-' . bin2hex(random_bytes(12));
        foreach (['m', 'w'] as $gender) {
            if (!mkdir($stagingDirectory . '/' . $gender, 0o755, true)) {
                $this->removeGraphicsDirectory($stagingDirectory);
                $game->getInfo()->addInformation(_('Das Zielverzeichnis für die Crew-Grafiken konnte nicht erstellt werden'));
                return false;
            }
            foreach (range(1, 6) as $imageType) {
                $relativePath = sprintf('/%s/1_%d.png', $gender, $imageType);
                $file = $graphics[$gender][$imageType] ?? null;
                $stored = true;
                if ($file !== null) {
                    $stored = move_uploaded_file($file['tmp_name'], $stagingDirectory . $relativePath);
                } elseif ($currentDirectory !== null && is_file($currentDirectory . $relativePath)) {
                    $stored = copy($currentDirectory . $relativePath, $stagingDirectory . $relativePath);
                }
                if (!$stored) {
                    $this->removeGraphicsDirectory($stagingDirectory);
                    $game->getInfo()->addInformation(_('Eine Crew-Grafik konnte nicht gespeichert werden'));
                    return false;
                }
            }
        }

        $backupDirectory = $stagingDirectory . '-previous';
        $hasPreviousGraphics = $currentDirectory !== null && is_dir($currentDirectory);
        if ($hasPreviousGraphics && !rename($currentDirectory, $backupDirectory)) {
            $this->removeGraphicsDirectory($stagingDirectory);
            $game->getInfo()->addInformation(_('Die bisherigen Crew-Grafiken konnten nicht ersetzt werden'));
            return false;
        }
        if (!rename($stagingDirectory, $targetDirectory)) {
            if ($hasPreviousGraphics) {
                rename($backupDirectory, $currentDirectory);
            }
            $this->removeGraphicsDirectory($stagingDirectory);
            $game->getInfo()->addInformation(_('Die Crew-Grafiken konnten nicht gespeichert werden'));
            return false;
        }
        if ($hasPreviousGraphics) {
            $this->removeGraphicsDirectory($backupDirectory);
        }

        return true;
    }

    private function removeGraphicsDirectory(string $directory): void
    {
        foreach (['m', 'w'] as $gender) {
            foreach (range(1, 6) as $imageType) {
                $file = sprintf('%s/%s/1_%d.png', $directory, $gender, $imageType);
                if (is_file($file)) {
                    unlink($file);
                }
            }
            if (is_dir($directory . '/' . $gender)) {
                rmdir($directory . '/' . $gender);
            }
        }
        if (is_dir($directory)) {
            rmdir($directory);
        }
    }

    /** @return array<string, array<int, array{name: string, tmp_name: string, size: int, error: int}>>|null */
    private function getValidatedGraphics(int $maleRatio, ?string $currentDirectory, GameControllerInterface $game): ?array
    {
        $graphics = [];
        foreach (['m', 'w'] as $gender) {
            foreach (range(1, 6) as $imageType) {
                $file = $this->getGraphicFile($gender, $imageType);
                $required = $gender === 'm' ? $maleRatio > 0 : $maleRatio < 100;
                if (($file === null || $file['error'] === UPLOAD_ERR_NO_FILE)
                    && (!$required || ($currentDirectory !== null
                        && is_file(sprintf('%s/%s/1_%d.png', $currentDirectory, $gender, $imageType))))
                ) {
                    continue;
                }
                if ($file === null || $file['error'] !== UPLOAD_ERR_OK) {
                    $game->getInfo()->addInformationf(_('Für %s Grafik %d wurde keine Datei hochgeladen'), $gender === 'm' ? _('Männer') : _('Frauen'), $imageType);
                    return null;
                }
                if (!$this->isValidGraphic($file)) {
                    $game->getInfo()->addInformationf(_('Grafik %d für %s muss eine PNG-Datei mit 51x52 Pixeln und höchstens 10 KB sein'), $imageType, $gender === 'm' ? _('Männer') : _('Frauen'));
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

}
