<?php

declare(strict_types=1);

namespace Stu\Module\Maintenance;

use PHPUnit\Framework\Attributes\TestWith;
use Stu\Component\Crew\Skill\CrewSkillLevelEnum;
use Stu\Module\Control\StuRandom;
use Stu\Module\Control\StuTime;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Entity\Crew;
use Stu\Orm\Entity\CrewRace;
use Stu\Orm\Entity\CrewSkill;
use Stu\Orm\Entity\OrionAuction;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;
use Stu\Orm\Repository\CrewRaceRepositoryInterface;
use Stu\Orm\Repository\CrewRepositoryInterface;
use Stu\Orm\Repository\CrewSkillRepositoryInterface;
use Stu\Orm\Repository\ModuleRepositoryInterface;
use Stu\Orm\Repository\OrionAuctionBidRepositoryInterface;
use Stu\Orm\Repository\OrionAuctionRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\StuTestCase;

final class OrionSlaveTradeTest extends StuTestCase
{
    #[TestWith([1, 1, CrewSkillLevelEnum::COMMANDER, CrewSkillLevelEnum::ADMIRAL])]
    #[TestWith([100, 100, CrewSkillLevelEnum::CAPTAIN, CrewSkillLevelEnum::COMMODORE])]
    public function testCreatesCrewAtHighestExpertiseRankWithoutPromotionLimits(
        int $stageThreeRoll,
        int $stageFourRoll,
        CrewSkillLevelEnum $stageThreeRank,
        CrewSkillLevelEnum $stageFourRank
    ): void {
        $auctionRepository = $this->mock(OrionAuctionRepositoryInterface::class);
        $crewRepository = $this->mock(CrewRepositoryInterface::class);
        $skillRepository = $this->mock(CrewSkillRepositoryInterface::class);
        $raceRepository = $this->mock(CrewRaceRepositoryInterface::class);
        $commodityRepository = $this->mock(CommodityRepositoryInterface::class);
        $moduleRepository = $this->mock(ModuleRepositoryInterface::class);
        $userRepository = $this->mock(UserRepositoryInterface::class);
        $random = $this->mock(StuRandom::class);
        $time = $this->mock(StuTime::class);
        $seller = $this->mock(User::class);
        $race = $this->mock(CrewRace::class);
        $auctions = [];
        $crews = [];

        $time->shouldReceive('time')->once()->andReturn(1_790_300_000);
        $auctionRepository->shouldReceive('getExpired')->once()->andReturn([]);
        $auctionRepository->shouldReceive('getActive')->once()->andReturn([]);
        $auctionRepository->shouldReceive('prototype')->times(3)->andReturnUsing(static fn () => new OrionAuction());
        $auctionRepository->shouldReceive('save')->times(3)->andReturnUsing(
            static function (OrionAuction $auction) use (&$auctions): void {
                $auctions[] = $auction;
            }
        );
        $crewRepository->shouldReceive('prototype')->times(3)->andReturnUsing(static fn () => new Crew());
        $crewRepository->shouldReceive('save')->times(3)->andReturnUsing(
            static function (Crew $crew) use (&$crews): void {
                $crews[] = $crew;
            }
        );
        $crewRepository->shouldNotReceive('getAmountByUserAndRank');
        $skillRepository->shouldReceive('prototype')->times(12)->andReturnUsing(static fn () => new CrewSkill());
        $skillRepository->shouldReceive('save')->times(12);
        $raceRepository->shouldReceive('getWithoutCreatorByFactionIds')->with([1, 2, 3, 4, 5])->once()->andReturn([$race]);
        $raceRepository->shouldReceive('getSharedByFactionIds')->with([1, 2, 3, 4, 5])->once()->andReturn([]);
        $race->shouldReceive('getMaleRatio')->times(3)->andReturn(50);
        $userRepository->shouldReceive('find')->with(UserConstants::USER_NOONE)->once()->andReturn($seller);
        $commodityRepository->shouldReceive('getAll')->times(3)->andReturn([]);
        $moduleRepository->shouldReceive('getNonFactionNonSpecialByLevels')->times(3)->andReturn([]);
        $random->shouldReceive('array_rand')->times(9)->andReturn(0);
        $random->shouldReceive('rand')->with(1, 30)->once()->andReturn(1);
        $random->shouldReceive('rand')->with(1, 100)->times(12)->andReturn(
            100, 100, 100, 100, 1, 100,
            100, 50, $stageThreeRoll, 50, $stageFourRoll, 50
        );
        $random->shouldReceive('rand')->with(0, 3)->times(3)->andReturn(3);
        foreach ([CrewSkillLevelEnum::JUNIOR_LIEUTENANT, $stageThreeRank, $stageFourRank] as $rank) {
            $minimum = $rank->getNeededExpertise();
            $maximum = ($rank->getNextSkillRank()?->getNeededExpertise() ?? $minimum + 1) - 1;
            $random->shouldReceive('rand')->with($minimum, $maximum)->once()->andReturn($maximum);
            $random->shouldReceive('rand')->with(1, $minimum - 1)->times(3)->andReturn($minimum - 1);
        }

        $subject = new OrionSlaveTrade(
            $auctionRepository,
            $this->mock(OrionAuctionBidRepositoryInterface::class),
            $crewRepository,
            $this->mock(CrewAssignmentRepositoryInterface::class),
            $skillRepository,
            $raceRepository,
            $commodityRepository,
            $moduleRepository,
            $this->mock(TradePostRepositoryInterface::class),
            $userRepository,
            $this->mock(PrivateMessageSenderInterface::class),
            $random,
            $time
        );

        $subject->handle();

        self::assertFalse((new Crew())->isSlave());
        self::assertCount(3, $auctions);
        self::assertSame($stageThreeRank, $crews[1]->getRank());
        self::assertSame($stageFourRank, $crews[2]->getRank());
        foreach ($crews as $index => $crew) {
            self::assertTrue($crew->isSlave());
            self::assertSame($crew, $auctions[$index]->getCrew());
            self::assertSame($seller, $crew->getUser());
            self::assertSame(CrewSkillLevelEnum::getForExpertise($crew->getHighestSkillExpertise()), $crew->getRank());
            self::assertGreaterThan($crew->getHighestSkillExpertise(), $crew->getExpertiseSum());
        }
    }
}
