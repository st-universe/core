<?php

declare(strict_types=1);

namespace Stu\Component\Research;

use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Component\Quest\AwayMission\AwayMissionResearchEnum;
use Stu\Module\Control\StuTime;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Orm\Entity\SpecialResearch;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\SpecialResearchRepositoryInterface;
use Stu\StuTestCase;

final class SpecialResearchCompletionTest extends StuTestCase
{
    /** @return iterable<string, array{bool, SpecialResearchStatusEnum}> */
    public static function outcomes(): iterable
    {
        yield 'success' => [false, SpecialResearchStatusEnum::COMPLETED];
        yield 'failure' => [true, SpecialResearchStatusEnum::FAILED];
    }

    #[DataProvider('outcomes')]
    public function testStoresActualCompletionTimeOnceInsteadOfTheScheduledDeadline(bool $failed, SpecialResearchStatusEnum $status): void
    {
        $research = $this->research();
        $repository = $this->mock(SpecialResearchRepositoryInterface::class);
        $repository->shouldReceive('save')->once()->with($research);
        $messages = $this->messages();
        $time = $this->mock(StuTime::class);
        $time->shouldReceive('time')->once()->andReturn(1200);
        $completion = new SpecialResearchCompletion($repository, $messages, $time);
        self::assertSame(1000, $research->getFinishTime());
        $completion->finish($research, $failed);
        $completion->finish($research, $failed);
        self::assertSame($status, $research->getStatus());
        self::assertSame(1200, $research->getFinishTime());
        self::assertSame(500, $research->getStartTime());
    }

    public function testDestroyedFacilityUsesItsActualRemovalTime(): void
    {
        $research = $this->research();
        $repository = $this->mock(SpecialResearchRepositoryInterface::class);
        $repository->shouldReceive('save')->once()->with($research);
        $time = $this->mock(StuTime::class);
        $time->shouldNotReceive('time');
        new SpecialResearchCompletion($repository, $this->messages(), $time)->facilityLost($research, 700);
        self::assertSame(SpecialResearchStatusEnum::FAILED, $research->getStatus());
        self::assertSame(700, $research->getFinishTime());
    }

    private function research(): SpecialResearch
    {
        $user = $this->mock(User::class);
        $user->shouldReceive('getId')->once()->andReturn(101);
        return new SpecialResearch()->setUser($user)->setResearchType(AwayMissionResearchEnum::SENSORS)->setStartTime(500)->setFinishTime(1000);
    }

    private function messages(): PrivateMessageSenderInterface
    {
        $messages = $this->mock(PrivateMessageSenderInterface::class);
        $messages->shouldReceive('send')->once()->with(1, 101, Mockery::type('string'), PrivateMessageFolderTypeEnum::SPECIAL_COLONY);
        return $messages;
    }
}
