<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\Lib;

use JBBCode\Parser;
use Override;
use Stu\Component\Map\EncodedMapInterface;
use Stu\Component\Player\Settings\UserSettingsProviderInterface;
use Stu\Orm\Entity\Layer;
use Stu\Orm\Entity\TradePost;
use Stu\Orm\Repository\TradePostRepositoryInterface;

class ExplorableStarMapItem implements ExplorableStarMapItemInterface
{
    private ?TradePost $tradepost = null;

    private bool $hide = false;

    public function __construct(
        private readonly TradePostRepositoryInterface $tradePostRepository,
        private readonly UserSettingsProviderInterface $userSettingsProvider,
        private readonly EncodedMapInterface $encodedMap,
        private readonly Parser $bbCodeParser,
        private readonly ExploreableStarMapInterface $exploreableStarMap,
        private readonly Layer $layer
    ) {}

    #[Override]
    public function getCx(): int
    {
        return $this->exploreableStarMap->getCx();
    }

    #[Override]
    public function getCy(): int
    {
        return $this->exploreableStarMap->getCy();
    }

    #[Override]
    public function getFieldId(): int
    {
        return $this->exploreableStarMap->getFieldId();
    }

    #[Override]
    public function getLayer(): Layer
    {
        return $this->layer;
    }

    #[Override]
    public function getTitle(): ?string
    {
        if ($this->hide === true) {
            return null;
        }

        $tradepost = $this->getTradepost();

        $result = '';

        if ($tradepost !== null) {
            $result .= $this->getTradepostTitle($tradepost);
        }

        $isSystemNameSet = false;
        if ($this->exploreableStarMap->getMapped() !== null) {
            if ($result !== '') {
                $result .= ' über ';
            }
            $isSystemNameSet = true;
            $result .= $this->exploreableStarMap->getSystemName() . '-System';
        }

        if ($this->exploreableStarMap->getRegionDescription() !== null) {
            if ($isSystemNameSet) {
                $result .= ', ';
            }
            $result .= $this->exploreableStarMap->getRegionDescription();
        }

        return $result;
    }

    private function getTradepostTitle(TradePost $tradepost): string
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

    #[Override]
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
            $tradepost !== null ? 'tradepost' : '',
            $this->exploreableStarMap->getMapped() ? 'mapped' : ''
        );
    }

    #[Override]
    public function getHref(): ?string
    {
        return $this->exploreableStarMap->getMapped()
            ? sprintf(
                'switchInnerContent(\'SHOW_ENTRY\', \'Systemkarte\', \'cat=7&ent=%d\', \'database.php\');',
                $this->exploreableStarMap->getMapped()
            ) : null;
    }

    private function getTradepost(): ?TradePost
    {
        if ($this->exploreableStarMap->getTradePostId() === null) {
            return null;
        }

        if ($this->tradepost === null) {
            $this->tradepost = $this->tradePostRepository->find($this->exploreableStarMap->getTradePostId());
        }

        return $this->tradepost;
    }


    #[Override]
    public function setHide(bool $hide): ExplorableStarMapItemInterface
    {
        $this->hide = $hide;

        return $this;
    }

    private function getBorder(): string
    {
        $borderType = $this->exploreableStarMap->getMapBorderType();
        if ($borderType === null) {
            if ($this->exploreableStarMap->getAdminRegion() === null && $this->exploreableStarMap->getInfluenceArea() !== null) {
                $influenceArea = $this->exploreableStarMap->getInfluenceArea();
                $base = $influenceArea->getStation();
                if ($base !== null) {
                    $user = $base->getUser();
                    $ally = $user->getAlliance();

                    $userRgbCode = $this->userSettingsProvider->getRgbCode($user);

                    if ($ally !== null && strlen($ally->getRgbCode()) > 0) {
                        return 'border: 1px solid ' . $ally->getRgbCode();
                    } elseif (strlen($userRgbCode) > 0) {
                        return 'border: 1px solid ' . $userRgbCode;
                    }
                }
            }
        } else {
            return 'border: 1px solid ' . $borderType->getColor();
        }

        return '';
    }

    #[Override]
    public function getFieldStyle(): string
    {
        if ($this->hide === true) {
            $imageUrl = '0.png';
        } elseif ($this->layer->isEncoded()) {
            $imageUrl = $this->encodedMap->getEncodedMapPath($this->getFieldId(), $this->getLayer());
        } else {
            $imageUrl = sprintf('%d/%d.png', $this->getLayer()->getId(), $this->getFieldId());
        }

        $style = "background-image: url('assets/map/" . $imageUrl . "'); opacity:1;";
        return $style . $this->getBorder();
    }
}
