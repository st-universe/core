<?php

declare(strict_types=1);

namespace Stu\Module\Maintenance;

use RuntimeException;
use Stu\Component\Crew\CrewTypeEnum;
use Stu\Component\Crew\Skill\CrewSkillLevelEnum;
use Stu\Component\Game\TimeConstants;
use Stu\Component\Trade\TradeEnum;
use Stu\Module\Commodity\CommodityTypeConstants;
use Stu\Module\Control\StuRandom;
use Stu\Module\Control\StuTime;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\Crew;
use Stu\Orm\Entity\CrewRace;
use Stu\Orm\Entity\TradePost;
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

final class OrionSlaveTrade implements MaintenanceHandlerInterface
{
    private const array ELIGIBLE_FACTION_IDS = [1, 2, 3, 4, 5];

    private const array STAGE_COMMODITIES = [
        1 => [CommodityTypeConstants::COMMODITY_BUILDING_MATERIALS, CommodityTypeConstants::COMMODITY_CHEMICAL_COMPONENTS, CommodityTypeConstants::COMMODITY_DEUTERIUM, CommodityTypeConstants::COMMODITY_TRANSPARENT_ALUMINIUM, CommodityTypeConstants::COMMODITY_IRIDIUM, CommodityTypeConstants::COMMODITY_IRIDIUM_ORE, CommodityTypeConstants::COMMODITY_GALAZIT_ORE, CommodityTypeConstants::COMMODITY_NITRIUM_ORE, CommodityTypeConstants::COMMODITY_MAGNESIT_ORE, CommodityTypeConstants::COMMODITY_KELBONIT_ORE, CommodityTypeConstants::COMMODITY_TRITANIUM_ORE, CommodityTypeConstants::COMMODITY_SPARE_PART, CommodityTypeConstants::COMMODITY_MICRO_PHOTON_TORPEDO],
        2 => [CommodityTypeConstants::COMMODITY_ANTIMATTER, CommodityTypeConstants::COMMODITY_PLASMA, CommodityTypeConstants::COMMODITY_DILITHIUM, CommodityTypeConstants::COMMODITY_IRIDIUM, CommodityTypeConstants::COMMODITY_DURANIUM, CommodityTypeConstants::COMMODITY_GALAZIT, CommodityTypeConstants::COMMODITY_NITRIUM, CommodityTypeConstants::COMMODITY_MAGNESIT, CommodityTypeConstants::COMMODITY_KELBONIT, CommodityTypeConstants::COMMODITY_TALGONIT, CommodityTypeConstants::COMMODITY_SYSTEM_COMPONENT, CommodityTypeConstants::COMMODITY_LIGHT_PHOTON_TORPEDO, CommodityTypeConstants::COMMODITY_PHOTON_TORPEDO],
        3 => [CommodityTypeConstants::COMMODITY_TRITANIUM, CommodityTypeConstants::COMMODITY_ISOLINEAR_STORAGE_CHIP, CommodityTypeConstants::COMMODITY_HIGH_ENERGY_PLASMA, CommodityTypeConstants::COMMODITY_NITRIUM_CIRCUITS, CommodityTypeConstants::COMMODITY_SUBSPACE_FIELD_COILS, CommodityTypeConstants::COMMODITY_METAPHASE_CONVERTER, CommodityTypeConstants::COMMODITY_QUANTUM_TORPEDO, CommodityTypeConstants::COMMODITY_PLASMA_TORPEDO, CommodityTypeConstants::COMMODITY_MIKRODYNE_MODULATOR],
        4 => [CommodityTypeConstants::COMMODITY_LATINUM, CommodityTypeConstants::COMMODITY_HEAVY_QUANTUM_TORPEDO, CommodityTypeConstants::COMMODITY_HEAVY_PLASMA_TORPEDO]
    ];

    private const array STAGE_MODULE_LEVELS = [
        1 => [1, 2],
        2 => [3, 4],
        3 => [5],
        4 => [6]
    ];

    public function __construct(
        private OrionAuctionRepositoryInterface $orionAuctionRepository,
        private OrionAuctionBidRepositoryInterface $orionAuctionBidRepository,
        private CrewRepositoryInterface $crewRepository,
        private CrewAssignmentRepositoryInterface $crewAssignmentRepository,
        private CrewSkillRepositoryInterface $crewSkillRepository,
        private CrewRaceRepositoryInterface $crewRaceRepository,
        private CommodityRepositoryInterface $commodityRepository,
        private ModuleRepositoryInterface $moduleRepository,
        private TradePostRepositoryInterface $tradePostRepository,
        private UserRepositoryInterface $userRepository,
        private PrivateMessageSenderInterface $privateMessageSender,
        private StuRandom $stuRandom,
        private StuTime $stuTime
    ) {}

    #[\Override]
    public function handle(): void
    {
        $time = $this->stuTime->time();
        $this->resolveExpiredAuctions($time);
        $this->createAuctions($time);
    }

    private function resolveExpiredAuctions(int $time): void
    {
        foreach ($this->orionAuctionRepository->getExpired($time) as $auction) {
            $highestBid = $this->orionAuctionBidRepository->getHighestBid($auction);
            if ($highestBid === null) {
                $this->orionAuctionRepository->delete($auction);
                $this->crewRepository->delete($auction->getCrew());
                continue;
            }

            $tradePost = $this->getZagosTradePost();
            $crew = $auction->getCrew();
            $winner = $highestBid->getUser();
            $crew->setUser($winner);
            $this->crewRepository->save($crew);

            $crewAssignment = $this->crewAssignmentRepository->prototype()
                ->setCrew($crew)
                ->setUser($winner)
                ->setSlot($auction->getImagePosition())
                ->setTradepost($tradePost);
            $this->crewAssignmentRepository->save($crewAssignment);

            foreach ($this->orionAuctionBidRepository->getByAuction($auction) as $bid) {
                $this->orionAuctionBidRepository->delete($bid);
            }

            $auction
                ->setWinner($winner)
                ->setWinnerName($winner->getName())
                ->setCompletedAt($time);
            $this->orionAuctionRepository->save($auction);

            $this->privateMessageSender->send(
                UserConstants::USER_NPC_FERG,
                $winner->getId(),
                sprintf('Du hast die Orion-Auktion für Crewman %s gewonnen. Der Crewman wartet bei Zagos Freihandel zur Abholung auf dich', $crew->getName()),
                PrivateMessageFolderTypeEnum::SPECIAL_TRADE
            );
        }
    }

    private function createAuctions(int $time): void
    {
        if ($this->orionAuctionRepository->getActive() !== []) {
            return;
        }

        $standardRaces = $this->crewRaceRepository->getWithoutCreatorByFactionIds(self::ELIGIBLE_FACTION_IDS);
        $sharedRaces = $this->crewRaceRepository->getSharedByFactionIds(self::ELIGIBLE_FACTION_IDS);
        if ($standardRaces === [] && $sharedRaces === []) {
            return;
        }

        $seller = $this->userRepository->find(UserConstants::USER_NOONE);
        if ($seller === null) {
            throw new RuntimeException('no neutral user found for Orion auction crew');
        }

        $stages = $this->determineStages();
        $end = strtotime('tomorrow 03:00:00', $time) + TimeConstants::ONE_DAY_IN_SECONDS;
        foreach ($stages as $stage) {
            $this->createAuction($stage, $time, $end, $seller, $this->selectRace($standardRaces, $sharedRaces));
        }
    }

    /** @return list<int> */
    private function determineStages(): array
    {
        $stages = [1];
        foreach ([1 => [60, 2], 2 => [40, 2], 3 => [25, 2], 4 => [1, 1]] as $stage => [$chance, $attempts]) {
            for ($i = 0; $i < $attempts && count($stages) < 7; $i++) {
                $isSelected = $stage === 4
                    ? $this->stuRandom->rand(1, 30) === 1
                    : $this->stuRandom->rand(1, 100) <= $chance;
                if ($isSelected) {
                    $stages[] = $stage;
                }
            }
        }
        while (count($stages) < 3) {
            $stages[] = 1;
        }

        return $stages;
    }

    private function createAuction(int $stage, int $start, int $end, User $seller, CrewRace $race): void
    {
        $rank = $this->getRankForStage($stage);
        $position = $this->getPosition();
        $crew = $this->crewRepository->prototype()
            ->setUser($seller)
            ->setName('Orionischer Crewman')
            ->setRace($race)
            ->setGender($this->stuRandom->rand(1, 100) > $race->getMaleRatio() ? Crew::CREW_GENDER_FEMALE : Crew::CREW_GENDER_MALE)
            ->setType(CrewTypeEnum::CREWMAN)
            ->setIsSlave(true);
        $this->crewRepository->save($crew);

        $this->createSkills($crew, $position, $rank);
        $crew->setRank(CrewSkillLevelEnum::getForExpertise($crew->getHighestSkillExpertise()));

        $auction = $this->orionAuctionRepository->prototype()
            ->setCrew($crew)
            ->setStart($start)
            ->setEnd($end)
            ->setImagePosition($position)
            ->setWantedCommodity($this->getWantedCommodity($stage));
        $this->orionAuctionRepository->save($auction);
    }

    /**
     * @param list<CrewRace> $standardRaces
     * @param list<CrewRace> $sharedRaces
     */
    private function selectRace(array $standardRaces, array $sharedRaces): CrewRace
    {
        if ($standardRaces === []) {
            return $sharedRaces[$this->stuRandom->array_rand($sharedRaces)];
        }
        if ($sharedRaces === []) {
            return $standardRaces[$this->stuRandom->array_rand($standardRaces)];
        }

        $races = $this->stuRandom->rand(1, 100) <= 70 ? $standardRaces : $sharedRaces;
        return $races[$this->stuRandom->array_rand($races)];
    }

    private function createSkills(Crew $crew, CrewTypeEnum $primaryPosition, CrewSkillLevelEnum $rank): void
    {
        $primaryExpertise = $this->stuRandom->rand(
            $rank->getNeededExpertise(),
            ($rank->getNextSkillRank()?->getNeededExpertise() ?? $rank->getNeededExpertise() + 1) - 1
        );
        $this->createSkill($crew, $primaryPosition, $primaryExpertise);

        $positions = array_values(array_filter(
            CrewTypeEnum::getOrder(),
            static fn (CrewTypeEnum $position): bool => $position !== CrewTypeEnum::CREWMAN && $position !== $primaryPosition
        ));
        shuffle($positions);
        $maxAdditionalSkills = min(3, count($positions));
        $additionalSkills = $this->stuRandom->rand(0, $maxAdditionalSkills);
        $maximumSecondaryExpertise = max(0, $rank->getNeededExpertise() - 1);
        for ($i = 0; $i < $additionalSkills; $i++) {
            $this->createSkill($crew, $positions[$i], $this->stuRandom->rand(1, max(1, $maximumSecondaryExpertise)));
        }
    }

    private function createSkill(Crew $crew, CrewTypeEnum $position, int $expertise): void
    {
        $skill = $this->crewSkillRepository->prototype()
            ->setCrew($crew)
            ->setPosition($position);
        $skill->increaseExpertise($expertise);
        $crew->getSkills()->set($position->value, $skill);
        $this->crewSkillRepository->save($skill);
    }

    private function getRankForStage(int $stage): CrewSkillLevelEnum
    {
        if ($stage === 1) {
            $roll = $this->stuRandom->rand(1, 100);
            if ($roll <= 20) {
                return CrewSkillLevelEnum::CREWMAN;
            }
            if ($roll <= 50) {
                return CrewSkillLevelEnum::ENSIGN;
            }
            return CrewSkillLevelEnum::JUNIOR_LIEUTENANT;
        }

        return match ($stage) {
            2 => $this->stuRandom->rand(1, 100) <= 60 ? CrewSkillLevelEnum::LIEUTENANT : CrewSkillLevelEnum::LIEUTENANT_COMMANDER,
            3 => $this->stuRandom->rand(1, 100) <= 90 ? CrewSkillLevelEnum::COMMANDER : CrewSkillLevelEnum::CAPTAIN,
            4 => $this->stuRandom->rand(1, 100) <= 5 ? CrewSkillLevelEnum::ADMIRAL : CrewSkillLevelEnum::COMMODORE,
            default => throw new RuntimeException('invalid Orion auction stage')
        };
    }

    private function getPosition(): CrewTypeEnum
    {
        $positions = array_values(array_filter(
            CrewTypeEnum::getOrder(),
            static fn (CrewTypeEnum $position): bool => $position !== CrewTypeEnum::CREWMAN
        ));

        return $positions[$this->stuRandom->array_rand($positions)];
    }

    private function getWantedCommodity(int $stage): ?Commodity
    {
        $choices = [];
        $commodities = $this->commodityRepository->getAll();
        foreach (self::STAGE_COMMODITIES[$stage] as $commodityId) {
            $commodity = $commodities[$commodityId] ?? null;
            if ($commodity !== null) {
                $choices[] = $commodity;
            }
        }
        foreach ($this->moduleRepository->getNonFactionNonSpecialByLevels(self::STAGE_MODULE_LEVELS[$stage]) as $module) {
            $choices[] = $module->getCommodity();
        }
        $choices[] = null;

        return $choices[$this->stuRandom->array_rand($choices)];
    }

    private function getZagosTradePost(): TradePost
    {
        $tradePost = $this->tradePostRepository->find(TradeEnum::ORION_ZAGOS_TRADEPOST_ID);
        if ($tradePost === null) {
            throw new RuntimeException('no Zagos tradepost found');
        }

        return $tradePost;
    }
}
