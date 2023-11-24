<?php

declare(strict_types=1);

namespace Stu\Component\Alliance\Relations\Renderer;

use JBBCode\Parser;
use Noodlehaus\ConfigInterface;
use Stu\Component\Faction\FactionEnum;
use Stu\Orm\Entity\AllianceInterface;

/**
 * Converts alliance related data to to graph attribute values
 */
final class AllianceDataToGraphAttributeConverter implements AllianceDataToGraphAttributeConverterInterface
{
    private Parser $bbCodeParser;

    private ConfigInterface $config;

    public function __construct(
        Parser $bbCodeParser,
        ConfigInterface $config
    ) {
        $this->bbCodeParser = $bbCodeParser;
        $this->config = $config;
    }

    public function convertName(
        AllianceInterface $alliance
    ): string {
        return str_replace(
            ['&', '<', '>', '"', "'", '\\', "\n"],
            '',
            $this->bbCodeParser->parse($alliance->getName())->getAsText()
        );
    }

    public function getFrameColor(
        AllianceInterface $alliance,
        string $defaultColor = '#8b8b8b'
    ): string {
        $factionId = $alliance->getFactionId();

        if ($factionId !== null) {
            return FactionEnum::FACTION_ID_TO_COLOR_MAP[$factionId];
        }

        $rgbCode = $alliance->getRgbCode();

        if ($rgbCode !== '') {
            return $rgbCode;
        }

        return $defaultColor;
    }

    public function getUrl(
        AllianceInterface $alliance
    ): string {
        return sprintf(
            '%s/alliance.php?id=%d',
            $this->config->get('game.base_url'),
            $alliance->getId()
        );
    }

    public function getFillColor(
        AllianceInterface $alliance
    ): string {
        return $alliance->isNpcAlliance()
            ? '#2b2b2b'
            : '#4b4b4b';
    }
}
