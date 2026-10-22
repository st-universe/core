<?php

declare(strict_types=1);

namespace Stu\Module\PlayerProfile;

use Mockery;
use Noodlehaus\ConfigInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionProperty;
use Stu\Lib\Upload\PngUpload;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\PlayerProfile\Action\ChangeCharacter\ChangeCharacter;
use Stu\Module\PlayerProfile\Action\ChangeCharacter\ChangeCharacterRequestInterface;
use Stu\Module\PlayerProfile\Action\CreateCharacter\CreateCharacter;
use Stu\Module\PlayerProfile\Action\CreateCharacter\CreateCharacterRequestInterface;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserCharacter;
use Stu\Orm\Repository\UserCharacterRepositoryInterface;
use Stu\StuTestCase;

final class CharacterUploadSecurityTest extends StuTestCase
{
    #[DataProvider('routes')]
    public function testCharacterUploadsAreValidatedBeforePersisting(bool $change, bool $valid): void
    {
        $directory = sys_get_temp_dir() . '/stu-character-' . bin2hex(random_bytes(8));
        mkdir($directory);
        $user = new User();
        new ReflectionProperty(User::class, 'id')->setValue($user, 100);
        $character = new UserCharacter()->setUser($user)->setAvatar(null);
        $request = $this->mock($change ? ChangeCharacterRequestInterface::class : CreateCharacterRequestInterface::class);
        $request->shouldReceive('getName')->once()->andReturn('Testcharakter');
        $request->shouldReceive('getDescription')->once()->andReturn('Testbeschreibung');
        $request->shouldReceive('getAvatar')->once()->andReturn(['name' => 'avatar.png', 'tmp_name' => '/tmp/upload', 'type' => 'image/png', 'size' => 99, 'error' => UPLOAD_ERR_OK]);
        $repository = $this->mock(UserCharacterRepositoryInterface::class);
        if ($change) {
            $request->shouldReceive('getCharacterId')->once()->andReturn(1);
            $repository->shouldReceive('find')->once()->with(1)->andReturn($character);
        } elseif ($valid) {
            $repository->shouldReceive('prototype')->once()->andReturn($character);
        }
        $context = $this->mock(ActionControllerContext::class);
        $context->shouldReceive('getUser')->andReturn($user);
        $context->shouldReceive('getInfo->addInformation')->once();
        $config = $this->mock(ConfigInterface::class);
        $upload = Mockery::mock(PngUpload::class)->makePartial();
        $image = imagecreatetruecolor(2, 2);
        self::assertNotFalse($image);
        $upload->shouldReceive('readUploaded')->once()->with('/tmp/upload', UPLOAD_ERR_OK, 1000000, 2048, 2048, 4000000)->andReturn($valid ? $image : null);
        if ($valid) {
            $config->shouldReceive('get')->with('game.character_avatar_path')->andReturn($directory);
            $repository->shouldReceive('save')->once()->with($character);
        } else {
            $repository->shouldNotReceive('save', 'prototype');
            $config->shouldNotReceive('get');
        }
        try {
            $subject = $change ? new ChangeCharacter($request, $repository, $config, $upload) : new CreateCharacter($request, $repository, $config, $upload);
            $subject->handle($context);
            $files = glob($directory . '/*') ?: [];
            self::assertCount($valid ? 1 : 0, $files);
            if ($valid) {
                self::assertSame($directory . '/' . $character->getAvatar() . '.png', $files[0]);
                self::assertSame(IMAGETYPE_PNG, getimagesize($files[0])[2]);
                self::assertSame('Testcharakter', $character->getName());
            }
        } finally {
            foreach (glob($directory . '/*') ?: [] as $path) {
                unlink($path);
            }
            rmdir($directory);
        }
    }

    public static function routes(): iterable
    {
        yield 'create invalid' => [false, false];
        yield 'create valid' => [false, true];
        yield 'change invalid' => [true, false];
        yield 'change valid' => [true, true];
    }

    public function testAnotherPlayersCharacterCannotBeChanged(): void
    {
        $owner = new User();
        new ReflectionProperty(User::class, 'id')->setValue($owner, 100);
        $other = new User();
        new ReflectionProperty(User::class, 'id')->setValue($other, 101);
        $request = $this->mock(ChangeCharacterRequestInterface::class);
        $request->shouldReceive('getCharacterId')->once()->andReturn(1);
        $request->shouldNotReceive('getAvatar');
        $repository = $this->mock(UserCharacterRepositoryInterface::class);
        $repository->shouldReceive('find')->once()->with(1)->andReturn(new UserCharacter()->setUser($owner));
        $repository->shouldNotReceive('save');
        $context = $this->mock(ActionControllerContext::class);
        $context->shouldReceive('getUser')->once()->andReturn($other);
        $context->shouldReceive('getInfo->addInformation')->once();
        $upload = Mockery::mock(PngUpload::class);
        $upload->shouldNotReceive('readUploaded', 'write');
        new ChangeCharacter($request, $repository, $this->mock(ConfigInterface::class), $upload)->handle($context);
        self::assertTrue(true);
    }
}
