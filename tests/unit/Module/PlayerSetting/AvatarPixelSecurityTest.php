<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting;

use Mockery;
use Noodlehaus\ConfigInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionProperty;
use Stu\Component\Alliance\Enum\AllianceJobPermissionEnum;
use Stu\Component\Player\Settings\UserSettingsProviderInterface;
use Stu\Lib\Upload\PngUpload;
use Stu\Module\Alliance\Action\ChangeAvatar\ChangeAvatar as AllianceChangeAvatar;
use Stu\Module\Alliance\Lib\AllianceJobManagerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\PlayerSetting\Action\ChangeAvatar\ChangeAvatar;
use Stu\Module\PlayerSetting\Lib\ChangeUserSettingInterface;
use Stu\Module\PlayerSetting\Lib\UserSettingEnum;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\AllianceRepositoryInterface;
use Stu\StuTestCase;

final class AvatarPixelSecurityTest extends StuTestCase
{
    #[DataProvider('routes')]
    public function testBothRoutesValidateBeforeDecodingOrChangingStoredData(bool $allianceRoute, bool $valid): void
    {
        $previousMethod = $_SERVER['REQUEST_METHOD'] ?? null;
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $directory = sys_get_temp_dir() . '/stu-avatar-' . bin2hex(random_bytes(8));
        mkdir($directory);
        $_FILES['avatar'] = ['tmp_name' => '/tmp/oversized.png', 'error' => UPLOAD_ERR_OK, 'type' => 'image/png', 'size' => 100, 'name' => 'avatar.png'];
        $user = new User();
        new ReflectionProperty(User::class, 'id')->setValue($user, 100);
        $context = $this->mock(ActionControllerContext::class);
        $context->shouldReceive('getUser')->andReturn($user);
        $context->shouldReceive('getInfo->addInformation')->once();
        $config = $this->mock(ConfigInterface::class);
        $upload = Mockery::mock(PngUpload::class);
        $image = imagecreatetruecolor(100, 100);
        self::assertNotFalse($image);
        $upload->shouldReceive('readUploaded')->once()->with('/tmp/oversized.png', UPLOAD_ERR_OK, 200000, $allianceRoute ? 600 : 2048, $allianceRoute ? 150 : 2048, $allianceRoute ? 90000 : 4000000)->andReturn($valid ? $image : null);
        $setter = $this->mock(ChangeUserSettingInterface::class);
        $provider = $this->mock(UserSettingsProviderInterface::class);
        $jobs = $this->mock(AllianceJobManagerInterface::class);
        $alliances = $this->mock(AllianceRepositoryInterface::class);
        if ($valid) {
            $config->shouldReceive('get')->with('game.webroot')->andReturn($directory);
            $config->shouldReceive('get')->with($allianceRoute ? 'game.alliance_avatar_path' : 'game.user_avatar_path')->andReturn('.');
        } else {
            $config->shouldNotReceive('get');
        }
        if ($allianceRoute) {
            $alliance = new Alliance();
            new ReflectionProperty(Alliance::class, 'id')->setValue($alliance, 1);
            $user->setAlliance($alliance);
            $context->shouldReceive('setView')->once();
            $jobs->shouldReceive('hasUserPermission')->once()->with($user, $alliance, AllianceJobPermissionEnum::EDIT_ALLIANCE)->andReturn(true);
            if ($valid) {
                $alliances->shouldReceive('save')->once()->with($alliance);
            } else {
                $alliances->shouldNotReceive('save');
            }
            $subject = new AllianceChangeAvatar($jobs, $alliances, $config, $upload);
        } else {
            if ($valid) {
                $provider->shouldReceive('getAvatar')->once()->with($user)->andReturn('');
                $setter->shouldReceive('change')->once()->with($user, UserSettingEnum::AVATAR, Mockery::type('string'));
            } else {
                $provider->shouldNotReceive('getAvatar');
                $setter->shouldNotReceive('change');
            }
            $subject = new ChangeAvatar($setter, $provider, $config, $upload);
        }
        try {
            $subject->handle($context);
            self::assertCount($valid ? 1 : 0, glob($directory . '/*.png') ?: []);
        } finally {
            foreach (glob($directory . '/*') ?: [] as $path) {
                unlink($path);
            }
            rmdir($directory);
            unset($_FILES['avatar']);
            if ($previousMethod === null) {
                unset($_SERVER['REQUEST_METHOD']);
            } else {
                $_SERVER['REQUEST_METHOD'] = $previousMethod;
            }
        }
    }

    public static function routes(): iterable
    {
        yield 'player rejects' => [false, false];
        yield 'player accepts' => [false, true];
        yield 'alliance rejects' => [true, false];
        yield 'alliance accepts' => [true, true];
    }
}
