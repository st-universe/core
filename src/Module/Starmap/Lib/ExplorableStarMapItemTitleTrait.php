<?php

namespace Stu\Module\Starmap\Lib;

use JBBCode\Parser;
use Stu\Orm\Entity\TradePost;

trait ExplorableStarMapItemTitleTrait
{
    private function getTradepostTitle(TradePost $tradepost, Parser $bbCodeParser): string
    {
        $licenseInfo = $tradepost->getLatestLicenseInfo();

        if ($licenseInfo === null) {
            return $this->getStringWithoutBbCode($tradepost->getName(), $bbCodeParser);
        }

        return sprintf(
            '%s (Lizenz für %d Tage: %d %s)',
            $this->getStringWithoutBbCode($tradepost->getName(), $bbCodeParser),
            $licenseInfo->getDays(),
            $licenseInfo->getAmount(),
            $licenseInfo->getCommodity()->getName()
        );
    }

    private function getTerritoryOwnerTitle(bool $hide, ExploreableStarMapInterface $exploreableStarMap, Parser $bbCodeParser): ?string
    {
        if ($hide === true || $exploreableStarMap->getAdminRegion() !== null) {
            return null;
        }

        $influenceArea = $exploreableStarMap->getInfluenceArea();
        if ($influenceArea === null) {
            return null;
        }

        $base = $influenceArea->getStation();
        if ($base === null) {
            return null;
        }

        $user = $base->getUser();
        $userName = trim($this->getStringWithoutBbCode($user->getName(), $bbCodeParser));
        if ($userName === '') {
            return null;
        }

        $alliance = $user->getAlliance();
        if ($alliance !== null) {
            $allianceName = trim($this->getStringWithoutBbCode($alliance->getName(), $bbCodeParser));
            if ($allianceName !== '') {
                return sprintf('Gebiet: %s (%s)', $allianceName, $userName);
            }
        }

        return sprintf('Gebiet: %s', $userName);
    }

    private function getStringWithoutBbCode(string $string, Parser $bbCodeParser): string
    {
        return $bbCodeParser->parse($string)->getAsText();
    }
}
