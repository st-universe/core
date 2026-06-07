<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\Lib;

use JBBCode\Parser;
use Stu\Component\Map\EncodedMapInterface;
use Stu\Component\Player\Settings\UserSettingsProviderInterface;
use Stu\Lib\Map\FieldTypeEffectEnum;
use Stu\Orm\Entity\Layer;
use Stu\Orm\Entity\TradePost;
use Stu\Orm\Repository\TradePostRepositoryInterface;

class ExplorableStarMapItem implements ExplorableStarMapItemInterface
{
    private ?TradePost $tradepost = null;

    private ?string $borderCssValue = null;

    private bool $borderCssValueLoaded = false;

    private bool $hide = false;

    public function __construct(
        private readonly TradePostRepositoryInterface $tradePostRepository,
        private readonly UserSettingsProviderInterface $userSettingsProvider,
        private readonly EncodedMapInterface $encodedMap,
        private readonly Parser $bbCodeParser,
        private readonly ExploreableStarMapInterface $exploreableStarMap,
        private readonly Layer $layer
    ) {}

    #[\Override]
    public function getCx(): int
    {
        return $this->exploreableStarMap->getCx();
    }

    #[\Override]
    public function getCy(): int
    {
        return $this->exploreableStarMap->getCy();
    }

    #[\Override]
    public function getFieldId(): int
    {
        return $this->exploreableStarMap->getFieldId();
    }

    #[\Override]
    public function getLayer(): Layer
    {
        return $this->layer;
    }

    #[\Override]
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

    #[\Override]
    public function getTooltip(): string
    {
        if ($this->hide === true) {
            return '';
        }

        $lines = [];
        $fieldName = $this->exploreableStarMap->getFieldName();
        if ($fieldName !== '') {
            $lines[] = $fieldName;

            foreach ($this->getEffectDescriptions() as $description) {
                $lines[] = $description;
            }
        }

        if ($this->isImpassable()) {
            $lines[] = 'Unpassierbar';
        }

        $title = $this->getTitle();
        if ($title !== null && $title !== '') {
            $lines[] = $title;
        }

        return implode("\n", array_unique($lines));
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

    #[\Override]
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

    #[\Override]
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


    #[\Override]
    public function setHide(bool $hide): ExplorableStarMapItemInterface
    {
        $this->hide = $hide;
        $this->borderCssValue = null;
        $this->borderCssValueLoaded = false;

        return $this;
    }

    #[\Override]
    public function getBorderStyle(): string
    {
        $borderCssValue = $this->getBorderCssValue();

        return $borderCssValue !== null ? 'border: ' . $borderCssValue : '';
    }

    #[\Override]
    public function getTerritoryStyle(): string
    {
        $borderCssValue = $this->getBorderCssValue();

        return $borderCssValue !== null ? '--starmap-territory-border: ' . $borderCssValue . ';' : '';
    }

    #[\Override]
    public function hasTerritory(): bool
    {
        return $this->getBorderCssValue() !== null;
    }

    private function getBorderCssValue(): ?string
    {
        if ($this->borderCssValueLoaded) {
            return $this->borderCssValue;
        }

        $this->borderCssValueLoaded = true;

        if ($this->hide === true) {
            return null;
        }

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
                        $this->borderCssValue = '1px solid ' . $ally->getRgbCode();
                        return $this->borderCssValue;
                    } elseif (strlen($userRgbCode) > 0) {
                        $this->borderCssValue = '1px solid ' . $userRgbCode;
                        return $this->borderCssValue;
                    }
                }
            }
        } else {
            $this->borderCssValue = '1px solid ' . $borderType->getColor();
            return $this->borderCssValue;
        }

        return null;
    }

    #[\Override]
    public function hasEffects(): bool
    {
        if ($this->hide === true) {
            return false;
        }

        return $this->exploreableStarMap->getEffects() !== [];
    }

    #[\Override]
    public function isImpassable(): bool
    {
        if ($this->hide === true) {
            return false;
        }

        return !$this->exploreableStarMap->getPassable();
    }

    #[\Override]
    public function getFieldImagePath(): string
    {
        if ($this->hide === true) {
            return '0.png';
        }

        if ($this->layer->isEncoded()) {
            return $this->encodedMap->getEncodedMapPath($this->getFieldId(), $this->getLayer());
        }

        return sprintf('%d/%d.png', $this->getLayer()->getId(), $this->getFieldId());
    }

    #[\Override]
    public function getFieldStyle(): string
    {
        $style = "background-image: url('assets/map/" . $this->getFieldImagePath() . "'); opacity:1;";
        return $style . $this->getBorderStyle();
    }

    /**
     * @return array<int, string>
     */
    private function getEffectDescriptions(): array
    {
        return array_values(array_filter(
            array_map(
                fn (FieldTypeEffectEnum $effect): ?string => $effect->getDescription(),
                $this->exploreableStarMap->getEffects()
            )
        ));
    }
}
