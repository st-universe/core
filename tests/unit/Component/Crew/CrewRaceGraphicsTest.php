<?php

declare(strict_types=1);

namespace Stu\Component\Crew;

use Noodlehaus\ConfigInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\StuTestCase;

final class CrewRaceGraphicsTest extends StuTestCase
{
    private string $directory;
    private CrewRaceGraphics $subject;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/stu-crew-graphics-' . bin2hex(random_bytes(8));
        mkdir($this->directory . '/crew/OLD/m', 0o755, true);
        foreach (range(1, 6) as $imageType) {
            file_put_contents($this->directory . '/crew/OLD/m/1_' . $imageType . '.png', 'old-' . $imageType);
        }
        $_FILES = [];
        $config = $this->mock(ConfigInterface::class);
        $config->shouldReceive('get')->with('game.webroot')->andReturn($this->directory);
        $config->shouldReceive('get')->with('game.user_avatar_path')->andReturn('');
        $this->subject = new CrewRaceGraphics($config);
    }

    protected function tearDown(): void
    {
        $_FILES = [];
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($files as $file) {
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }
        rmdir($this->directory);
        parent::tearDown();
    }

    public function testKeepsExistingGraphicsWhenRenamingDefinition(): void
    {
        self::assertTrue($this->subject->store('NEW', 100, 'OLD', $this->mock(GameControllerInterface::class)));
        foreach (range(1, 6) as $imageType) {
            self::assertSame('old-' . $imageType, file_get_contents($this->directory . '/crew/NEW/m/1_' . $imageType . '.png'));
        }
        self::assertDirectoryDoesNotExist($this->directory . '/crew/OLD');
        self::assertSame(['NEW'], array_values(array_diff(scandir($this->directory . '/crew'), ['.', '..'])));
    }

    public function testMissingNewGenderDoesNotTouchExistingGraphics(): void
    {
        $game = $this->mock(GameControllerInterface::class);
        $game->shouldReceive('getInfo->addInformationf')->with('Für %s Grafik %d wurde keine Datei hochgeladen', 'Frauen', 1)->once();
        self::assertFalse($this->subject->store('NEW', 50, 'OLD', $game));
        self::assertSame('old-1', file_get_contents($this->directory . '/crew/OLD/m/1_1.png'));
        self::assertDirectoryDoesNotExist($this->directory . '/crew/NEW');
    }

    public function testExistingTargetDirectoryIsNotOverwritten(): void
    {
        mkdir($this->directory . '/crew/NEW');
        $game = $this->mock(GameControllerInterface::class);
        $game->shouldReceive('getInfo->addInformation')->with('Das Zielverzeichnis für die Grafikdefinition existiert bereits')->once();
        self::assertFalse($this->subject->store('NEW', 100, 'OLD', $game));
        self::assertFileExists($this->directory . '/crew/OLD/m/1_1.png');
    }

    public function testFailedUploadLeavesEntirePreviousImageSetIntact(): void
    {
        $temporaryFile = $this->directory . '/replacement.png';
        $image = imagecreatetruecolor(51, 52);
        imagepng($image, $temporaryFile);
        $_FILES = ['crew_graphics' => [
            'name' => ['m' => [3 => 'replacement.png']],
            'tmp_name' => ['m' => [3 => $temporaryFile]],
            'size' => ['m' => [3 => filesize($temporaryFile)]],
            'error' => ['m' => [3 => UPLOAD_ERR_OK]]
        ]];
        $game = $this->mock(GameControllerInterface::class);
        $game->shouldReceive('getInfo->addInformation')->with('Eine Crew-Grafik konnte nicht gespeichert werden')->once();
        // A valid PNG outside an HTTP upload must not be accepted by move_uploaded_file.
        self::assertFalse($this->subject->store('OLD', 100, 'OLD', $game));
        foreach (range(1, 6) as $imageType) {
            self::assertSame('old-' . $imageType, file_get_contents($this->directory . '/crew/OLD/m/1_' . $imageType . '.png'));
        }
        self::assertSame(['OLD'], array_values(array_diff(scandir($this->directory . '/crew'), ['.', '..'])));
    }
}
