<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowOrionSlaveTrade;

use Stu\Component\Crew\CrewTypeEnum;
use Stu\Component\Crew\Skill\CrewSkillLevelEnum;
use Stu\Component\Trade\TradeEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\CrewSkill;
use Stu\Orm\Entity\OrionAuction;
use Stu\Orm\Repository\OrionAuctionBidRepositoryInterface;
use Stu\Orm\Repository\OrionAuctionRepositoryInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use Stu\Orm\Repository\UserCrewRankRepositoryInterface;

final class ShowOrionSlaveTrade implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_ORION_SLAVE_TRADE';

    public function __construct(
        private OrionAuctionRepositoryInterface $orionAuctionRepository,
        private OrionAuctionBidRepositoryInterface $orionAuctionBidRepository,
        private TradeLicenseRepositoryInterface $tradeLicenseRepository,
        private UserCrewRankRepositoryInterface $userCrewRankRepository
    ) {}

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $game->appendNavigationPart('trade.php', 'Handel');
        $game->appendNavigationPart(sprintf('trade.php?%s=1', self::VIEW_IDENTIFIER), 'Orion Sklavenhandel');
        $game->setPageTitle('/ Handel / Orion Sklavenhandel');
        $game->setViewTemplate('html/trade/orionSlaveTrade.twig');

        $hasLicense = $this->tradeLicenseRepository->hasLicenseByUserAndTradePost($user->getId(), TradeEnum::ORION_ZAGOS_TRADEPOST_ID);
        $game->setTemplateVar('HAS_LICENSE', $hasLicense);
        if (!$hasLicense) {
            return;
        }

        $rankNames = [];
        foreach (CrewSkillLevelEnum::cases() as $rank) {
            $rankNames[$rank->value] = $this->userCrewRankRepository->getRankName($user, $rank);
        }
        $auctions = $this->orionAuctionRepository->getActive();
        $radarData = [];
        $highestBids = [];
        $highestSkills = [];
        foreach ($auctions as $auction) {
            $radarData[$auction->getId()] = $this->getRadarData($auction, $rankNames);
            $highestBids[$auction->getId()] = $this->orionAuctionBidRepository->getHighestBid($auction);
            $highestSkills[$auction->getId()] = $this->getHighestSkill($auction);
        }

        $game->setTemplateVar('AUCTIONS', $auctions);
        $game->setTemplateVar('HIGHEST_BIDS', $highestBids);
        $game->setTemplateVar('HIGHEST_SKILLS', $highestSkills);
        $game->setTemplateVar('HISTORY', $this->orionAuctionRepository->getHistorySince(time() - 1_209_600));
        $game->setTemplateVar('CREW_RANK_NAMES', $rankNames);
        $game->setTemplateVar('RADAR_DATA', $radarData);
    }

    /**
     * @param array<string, string> $rankNames
     * @return array{skills: list<array{name: string, expertise: int}>, ranks: list<array{name: string, expertise: int}>}
     */
    private function getRadarData(OrionAuction $auction, array $rankNames): array
    {
        $skills = [];
        foreach (CrewTypeEnum::getOrder() as $position) {
            if ($position === CrewTypeEnum::CREWMAN) {
                continue;
            }
            $skills[] = [
                'name' => $position->getDescription(),
                'expertise' => $auction->getCrew()->getSkillAt($position)?->getExpertise() ?? 0
            ];
        }

        return [
            'skills' => $skills,
            'ranks' => array_map(
                static fn (CrewSkillLevelEnum $rank): array => [
                    'name' => $rankNames[$rank->value],
                    'expertise' => $rank->getNeededExpertise()
                ],
                array_reverse(CrewSkillLevelEnum::cases())
            )
        ];
    }

    private function getHighestSkill(OrionAuction $auction): CrewSkill
    {
        $highestSkill = null;
        foreach ($auction->getCrew()->getSkills() as $skill) {
            if ($highestSkill === null || $skill->getExpertise() > $highestSkill->getExpertise()) {
                $highestSkill = $skill;
            }
        }

        return $highestSkill ?? throw new \RuntimeException('Orion auction crew has no skills');
    }
}
