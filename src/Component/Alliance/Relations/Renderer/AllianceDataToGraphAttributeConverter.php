<?php

declare(strict_types=1);

namespace Stu\Component\Alliance\Relations\Renderer;

use JBBCode\Parser;
use Noodlehaus\ConfigInterface;
use Override;
use Stu\Component\Faction\FactionEnum;
use Stu\Orm\Entity\Alliance;

/**
 * Converts alliance related data to to graph attribute values
 */
final class AllianceDataToGraphAttributeConverter implements AllianceDataToGraphAttributeConverterInterface
{
    public function __construct(private Parser $bbCodeParser, private ConfigInterface $config)
    {
    }

    #[Override]
    public function convertName(
        Alliance $alliance
    ): string {
        return str_replace(
            ['&', '<', '>', '"', "'", '\\', "\n"],
            '',
            $this->bbCodeParser->parse($alliance->getName())->getAsText()
        );
    }

    #[Override]
    public function getFrameColor(
        Alliance $alliance,
        string $defaultColor = '#8b8b8b'
    ): string {
        $faction = $alliance->getFaction();

        if ($faction !== null) {
            return FactionEnum::FACTION_ID_TO_COLOR_MAP[$faction->getId()];
        }

        $rgbCode = $alliance->getRgbCode();

        if ($rgbCode !== '') {
            return $rgbCode;
        }

        return $defaultColor;
    }

    #[Override]
    public function getUrl(
        Alliance $alliance
    ): string {
        return sprintf(
            '%s/alliance.php?id=%d',
            $this->config->get('game.base_url'),
            $alliance->getId()
        );
    }

    #[Override]
    public function getFillColor(
        Alliance $alliance
    ): string {
        return $alliance->isNpcAlliance()
            ? '#2b2b2b'
            : '#4b4b4b';
    }
}
