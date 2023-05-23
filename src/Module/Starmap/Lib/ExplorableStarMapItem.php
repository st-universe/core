<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\Lib;

use JBBCode\Parser;
use Stu\Orm\Entity\TradePostInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;

class ExplorableStarMapItem implements ExplorableStarMapItemInterface
{
    private TradePostRepositoryInterface $tradePostRepository;

    private Parser $bbCodeParser;

    private ?TradePostInterface $tradepost = null;

    private bool $hide = false;

    private ExploreableStarMapInterface $exploreableStarMap;

    public function __construct(
        TradePostRepositoryInterface $tradePostRepository,
        Parser $bbCodeParser,
        ExploreableStarMapInterface $exploreableStarMap
    ) {
        $this->tradePostRepository = $tradePostRepository;
        $this->bbCodeParser = $bbCodeParser;
        $this->exploreableStarMap = $exploreableStarMap;
    }

    public function getCx(): int
    {
        return $this->exploreableStarMap->getCx();
    }

    public function getCy(): int
    {
        return $this->exploreableStarMap->getCy();
    }

    public function getFieldId(): int
    {
        return $this->exploreableStarMap->getFieldId();
    }

    public function getLayer(): ?int
    {
        return $this->exploreableStarMap->getLayer();
    }

    public function getTitle(): ?string
    {
        if ($this->hide === true) {
            return null;
        }

        $tradepost = $this->getTradepost();

        return sprintf(
            '%s%s%s%s',
            $tradepost !== null ? $this->getTradepostTitle($tradepost) : '',
            $tradepost !== null && $this->exploreableStarMap->getMapped() !== null ? ' über ' : '',
            $this->exploreableStarMap->getMapped() !== null ? $this->exploreableStarMap->getSystemName() . '-System' : '',
            $this->exploreableStarMap->getRegionDescription() !== null ? $this->exploreableStarMap->getRegionDescription() : ''
        );
    }

    private function getTradepostTitle(TradePostInterface $tradepost): string
    {
        $licenseInfo = $tradepost->getLatestLicenseInfo();

        if ($licenseInfo === null) {
            return $this->getStringWithoutBbCode($tradepost->getName());
        }

        return sprintf(
            '%s (Lizenz für %d Tage: %d %s)',
            $this->getStringWithoutBbCode($tradepost->getName()),
            $licenseInfo->getDays(),
            $licenseInfo->getAmount(),
            $licenseInfo->getCommodity()->getName()
        );
    }

    private function getStringWithoutBbCode(string $string): string
    {
        return $this->bbCodeParser->parse($string)->getAsText();
    }

    public function getIcon(): ?string
    {
        if ($this->hide === true) {
            return null;
        }

        $tradepost = $this->getTradepost();

        if ($tradepost === null && $this->exploreableStarMap->getMapped() === null) {
            return null;
        }

        return sprintf(
            '%s%s',
            $tradepost !== null && !$tradepost->isNpcTradepost() ? 'tradepost' : '',
            $this->exploreableStarMap->getMapped() ? 'mapped' : ''
        );
    }

    public function getHref(): ?string
    {
        return $this->exploreableStarMap->getMapped() ? sprintf('database.php?SHOW_ENTRY=1&cat=7&ent=%d', $this->exploreableStarMap->getMapped()) : null;
    }

    private function getTradepost(): ?TradePostInterface
    {
        if ($this->exploreableStarMap->getTradePostId() === null) {
            return null;
        }

        if ($this->tradepost === null) {
            $this->tradepost = $this->tradePostRepository->find($this->exploreableStarMap->getTradePostId());
        }

        return $this->tradepost;
    }


    public function setHide(bool $hide): ExplorableStarMapItemInterface
    {
        $this->hide = $hide;

        return $this;
    }

    private function getBorder(): string
    {
        $borderType = $this->exploreableStarMap->getMapBorderType();
        if ($borderType === null) {
            if ($this->exploreableStarMap->getAdminRegion() === null) {
                if ($this->exploreableStarMap->getInfluenceArea() !== null) {
                    $influenceArea = $this->exploreableStarMap->getInfluenceArea();
                    $base = $influenceArea->getBase();

                    if ($base !== null) {
                        $user = $base->getUser();
                        $ally = $user->getAlliance();

                        if ($ally !== null && strlen($ally->getRgbCode()) > 0) {
                            return 'border: 1px solid ' . $ally->getRgbCode();
                        } elseif (strlen($user->getRgbCode()) > 0) {
                            return 'border: 1px solid ' . $user->getRgbCode();
                        }
                    }
                }
            }
        } else {
            return 'border: 1px solid ' . $borderType->getColor();
        }

        return '';
    }

    public function getFieldStyle(): string
    {
        if ($this->hide === true) {
            $type = 0;
            $layer = '';
        } else {
            $type = $this->getFieldId();
            $layer = $this->getLayer();
        }

        $style = "background-image: url('assets/map/" . $layer . "/" . $type . ".png');";
        $style .= $this->getBorder();
        return $style;
    }
}
