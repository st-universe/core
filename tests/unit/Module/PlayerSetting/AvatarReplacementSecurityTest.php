<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting;

use Mockery;
use Noodlehaus\ConfigInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionProperty;
use Stu\Component\Player\Settings\UserSettingsProviderInterface;
use Stu\Lib\Upload\PngUpload;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\PlayerSetting\Action\ChangeAvatar\ChangeAvatar;
use Stu\Module\PlayerSetting\Lib\ChangeUserSettingInterface;
use Stu\Module\PlayerSetting\Lib\UserSettingEnum;
use Stu\Orm\Entity\User;
use Stu\StuTestCase;

final class AvatarReplacementSecurityTest extends StuTestCase
{
    #[DataProvider('previousNames')]
    public function testRepeatedReplacementUsesTheSameStorageRootAndLeavesOnlyTheCurrentImage(string $oldName): void
    {
        $previousMethod = $_SERVER['REQUEST_METHOD'] ?? null;
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $root = sys_get_temp_dir() . '/stu-avatar-replace-' . bin2hex(random_bytes(8));
        mkdir($root . '/avatars', 0o755, true);
        $user = new User();
        new ReflectionProperty(User::class, 'id')->setValue($user, 100);
        $oldPath = $root . '/avatars/' . $oldName . '.png';
        file_put_contents($oldPath, 'old avatar');
        $currentName = $oldName;
        $config = $this->mock(ConfigInterface::class);
        $config->shouldReceive('get')->with('game.webroot')->andReturn($root . '/');
        $config->shouldReceive('get')->with('game.user_avatar_path')->andReturn('/avatars/');
        $provider = $this->mock(UserSettingsProviderInterface::class);
        $provider->shouldReceive('getAvatar')->times(3)->with($user)->andReturnUsing(function () use (&$currentName): string {
            return $currentName;
        });
        $setter = $this->mock(ChangeUserSettingInterface::class);
        $setter->shouldReceive('change')->times(3)->withArgs(function ($actualUser, $setting, $value) use ($user, &$currentName): bool {
            self::assertSame($user, $actualUser);
            self::assertSame(UserSettingEnum::AVATAR, $setting);
            $currentName = $value;
            return true;
        });
        $context = $this->mock(ActionControllerContext::class);
        $context->shouldReceive('getUser')->times(3)->andReturn($user);
        $context->shouldReceive('getInfo->addInformation')->times(3);
        $upload = Mockery::mock(PngUpload::class);
        $image = imagecreatetruecolor(150, 150);
        self::assertNotFalse($image);
        $upload->shouldReceive('readUploaded')->times(3)->andReturn($image);
        $_FILES['avatar'] = ['tmp_name' => '/tmp/upload', 'error' => UPLOAD_ERR_OK];
        try {
            $subject = new ChangeAvatar($setter, $provider, $config, $upload);
            $storedName = null;
            for ($i = 0; $i < 3; $i++) {
                $subject->handle($context);
                self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/D', $currentName);
                $storedName ??= $currentName;
                self::assertSame($storedName, $currentName);
                self::assertFileDoesNotExist($oldPath);
                self::assertCount(1, glob($root . '/avatars/*') ?: []);
                self::assertFileExists($root . '/avatars/' . $currentName . '.png');
                self::assertSame([150, 150], array_slice(getimagesize($root . '/avatars/' . $currentName . '.png'), 0, 2));
            }
        } finally {
            foreach (glob($root . '/avatars/*') ?: [] as $path) {
                unlink($path);
            }
            rmdir($root . '/avatars');
            rmdir($root);
            unset($_FILES['avatar']);
            if ($previousMethod === null) {
                unset($_SERVER['REQUEST_METHOD']);
            } else {
                $_SERVER['REQUEST_METHOD'] = $previousMethod;
            }
        }
    }

    public static function previousNames(): iterable
    {
        yield 'previous MD5 name' => [str_repeat('a', 32)];
        yield 'previous SHA-256 name' => [str_repeat('a', 64)];
    }
}
