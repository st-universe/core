<?php

declare(strict_types=1);

namespace Stu\Component\Alliance\Relations\Renderer;

use JBBCode\Parser;
use Noodlehaus\ConfigInterface;
use Stu\Component\Faction\FactionEnum;
use Stu\Component\Game\ModuleEnum;
use Stu\Orm\Entity\Alliance;

/**
 * Converts alliance related data to to graph attribute values
 */
final class AllianceDataToGraphAttributeConverter implements AllianceDataToGraphAttributeConverterInterface
{
    public function __construct(private Parser $bbCodeParser, private ConfigInterface $config) {}

    #[\Override]
    public function convertName(
        Alliance $alliance
    ): string {
        return str_replace(
            ['&', '<', '>', '"', "'", '\\', "\n"],
            '',
            $this->bbCodeParser->parse($alliance->getName())->getAsText()
        );
    }

    #[\Override]
    public function getFrameColor(
        Alliance $alliance,
        string $defaultColor = '#8b8b8b'
    ): string {
        $faction = $alliance->getFaction();

        if ($faction !== null) {
            return FactionEnum::from($faction->getId())->getColorCode();
        }

        $rgbCode = $alliance->getRgbCode();

        if ($rgbCode !== '') {
            return $rgbCode;
        }

        return $defaultColor;
    }

    #[\Override]
    public function getUrl(
        Alliance $alliance
    ): string {
        return sprintf(
            '%s/%s?id=%d',
            $this->getBaseUrl(),
            ModuleEnum::ALLIANCE->getPhpPage(),
            $alliance->getId()
        );
    }

    #[\Override]
    public function getFillColor(
        Alliance $alliance
    ): string {
        return $alliance->isNpcAlliance()
            ? '#2b2b2b'
            : '#4b4b4b';
    }

    private function getBaseUrl(): string
    {
        $requestHost = $this->getRequestHost();

        if ($requestHost !== null) {
            return sprintf('%s://%s', $this->getRequestScheme(), $requestHost);
        }

        return rtrim((string) $this->config->get('game.base_url'), '/');
    }

    private function getRequestHost(): ?string
    {
        $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? null;

        if (!is_string($host)) {
            return null;
        }

        $host = str_replace(["\r", "\n"], '', trim($host));

        return $host !== '' && preg_match('/^[A-Za-z0-9.\-:\[\]]+$/', $host) === 1
            ? $host
            : null;
    }

    private function getRequestScheme(): string
    {
        $forwardedProto = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? null;

        if (is_string($forwardedProto)) {
            $proto = strtolower(trim(explode(',', $forwardedProto)[0]));

            if ($proto === 'http' || $proto === 'https') {
                return $proto;
            }
        }

        return (
            isset($_SERVER['HTTPS'])
            && $_SERVER['HTTPS'] !== ''
            && $_SERVER['HTTPS'] !== 'off'
        ) ? 'https' : 'http';
    }
}
