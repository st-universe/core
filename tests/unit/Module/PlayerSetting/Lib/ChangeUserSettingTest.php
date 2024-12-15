<?php

declare(strict_types=1);

namespace Stu\Module\Research\Action\CancelResearch;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Override;
use Stu\Module\PlayerSetting\Lib\ChangeUserSetting;
use Stu\Module\PlayerSetting\Lib\ChangeUserSettingInterface;
use Stu\Module\PlayerSetting\Lib\UserSettingEnum;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Entity\UserSetting;
use Stu\Orm\Repository\UserSettingRepositoryInterface;
use Stu\StuTestCase;

class ChangeUserSettingTest extends StuTestCase
{
    /** @var MockInterface&UserSettingRepositoryInterface */
    private MockInterface $userSettingRepository;

    private ChangeUserSettingInterface $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->userSettingRepository = $this->mock(UserSettingRepositoryInterface::class);

        $this->subject = new ChangeUserSetting(
            $this->userSettingRepository
        );
    }

    public function testChangeExpectNewEntityIfNoneExists(): void
    {
        $user = $this->mock(UserInterface::class);
        $userSetting = $this->mock(UserSetting::class);

        $settings = new ArrayCollection();

        $user->shouldReceive('getSettings')
            ->withNoArgs()
            ->andReturn($settings);

        $userSetting->shouldReceive('setUser')
            ->with($user)
            ->once()
            ->andReturnSelf();
        $userSetting->shouldReceive('setSetting')
            ->with(UserSettingEnum::AVATAR)
            ->once();
        $userSetting->shouldReceive('setValue')
            ->with('foo')
            ->once();

        $this->userSettingRepository->shouldReceive('prototype')
            ->withNoArgs()
            ->once()
            ->andReturn($userSetting);
        $this->userSettingRepository->shouldReceive('save')
            ->with($userSetting)
            ->once();


        $this->subject->change($user, UserSettingEnum::AVATAR, 'foo');

        $this->assertEquals(1, $settings->count());
        $this->assertTrue($settings->containsKey(UserSettingEnum::AVATAR->value));
        $this->assertEquals($userSetting, $settings->get(UserSettingEnum::AVATAR->value));
    }

    public function testChangeEditIfAlreadyExists(): void
    {
        $user = $this->mock(UserInterface::class);
        $userSetting = $this->mock(UserSetting::class);

        $settings = new ArrayCollection([UserSettingEnum::AVATAR->value => $userSetting]);

        $user->shouldReceive('getSettings')
            ->withNoArgs()
            ->andReturn($settings);

        $userSetting->shouldReceive('setValue')
            ->with('bar')
            ->once();

        $this->userSettingRepository->shouldReceive('save')
            ->with($userSetting)
            ->once();

        $this->subject->change($user, UserSettingEnum::AVATAR, 'bar');

        $this->assertEquals(1, $settings->count());
        $this->assertTrue($settings->containsKey(UserSettingEnum::AVATAR->value));
        $this->assertEquals($userSetting, $settings->get(UserSettingEnum::AVATAR->value));
    }

    public function testResetExpectNothingIfNotExists(): void
    {
        $user = $this->mock(UserInterface::class);

        $settings = new ArrayCollection();

        $user->shouldReceive('getSettings')
            ->withNoArgs()
            ->andReturn($settings);

        $this->subject->reset($user, UserSettingEnum::AVATAR);

        $this->assertTrue($settings->isEmpty());
    }

    public function testResetExpectDeletionIfExists(): void
    {
        $user = $this->mock(UserInterface::class);
        $userSetting = $this->mock(UserSetting::class);

        $settings = new ArrayCollection([UserSettingEnum::AVATAR->value => $userSetting]);

        $user->shouldReceive('getSettings')
            ->withNoArgs()
            ->andReturn($settings);

        $this->userSettingRepository->shouldReceive('delete')
            ->with($userSetting)
            ->once();

        $this->subject->reset($user, UserSettingEnum::AVATAR);

        $this->assertTrue($settings->isEmpty());
    }
}
