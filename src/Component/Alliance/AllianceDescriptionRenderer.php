<?php

declare(strict_types=1);

namespace Stu\Component\Alliance;

use Noodlehaus\ConfigInterface;
use Stu\Component\Alliance\Relations\Renderer\AllianceRelationRendererInterface;
use Stu\Lib\ParserWithImageInterface;
use Stu\Orm\Entity\AllianceInterface;
use Stu\Orm\Repository\AllianceRelationRepositoryInterface;

/**
 * Provides utility methods for alliance description rendering
 */
final class AllianceDescriptionRenderer implements AllianceDescriptionRendererInterface
{
    /**
     * @var int
     */
    private const RELATION_IMAGE_WIDTH = 600;

    /**
     * @var int
     */
    private const RELATION_IMAGE_HEIGHT = 700;

    private ParserWithImageInterface $parserWithImage;

    private AllianceRelationRendererInterface $allianceRelationRenderer;

    private AllianceRelationRepositoryInterface $allianceRelationRepository;

    private ConfigInterface $config;

    public function __construct(
        ParserWithImageInterface $parserWithImage,
        AllianceRelationRendererInterface $allianceRelationRenderer,
        ConfigInterface $config,
        AllianceRelationRepositoryInterface $allianceRelationRepository
    ) {
        $this->parserWithImage = $parserWithImage;
        $this->allianceRelationRenderer = $allianceRelationRenderer;
        $this->allianceRelationRepository = $allianceRelationRepository;
        $this->config = $config;
    }

    public function render(
        AllianceInterface $alliance
    ): string {
        $replacementVars = $this->getReplacementVars();

        /** @var string $description */
        $description = preg_replace_callback(
            '#\$(ALLIANCE_[^\s]+)#',
            static function (array $match) use ($replacementVars, $alliance): string {
                $replacer = $replacementVars[$match[1]] ?? null;
                if ($replacer !== null) {
                    return $replacer($alliance);
                }

                return '';
            },
            $alliance->getDescription()
        );
        return $this->parserWithImage->parse($description)->getAsHTML();
    }

    /**
     * @return array<string, callable(AllianceInterface): string>
     */
    private function getReplacementVars(): array
    {
        return [
            'ALLIANCE_HOMEPAGE_LINK' => static fn (AllianceInterface $alliance): string => sprintf('<a href="%s" target="_blank">%s</a>', $alliance->getHomepage(), 'Zur Allianz Homepage'),
            'ALLIANCE_BANNER' => function (AllianceInterface $alliance): string {
                $avatar = $alliance->getAvatar();

                return $avatar !== ''
                    ? sprintf('<img src="%s/%s.png" />', $this->config->get('game.alliance_avatar_path'), $avatar)
                    : '';
            },
            'ALLIANCE_PRESIDENT' => static fn (AllianceInterface $alliance): string => $alliance->getFounder()->getUser()->getName(),
            'ALLIANCE_VICEPRESIDENT' => static fn (AllianceInterface $alliance): string => $alliance->getSuccessor() !== null
                ? $alliance->getSuccessor()->getUser()->getName()
                : 'Unbesetzt',
            'ALLIANCE_FOREIGNMINISTER' => static fn (AllianceInterface $alliance): string => $alliance->getDiplomatic() !== null
                ? $alliance->getDiplomatic()->getUser()->getName()
                : 'Unbesetzt',
            'ALLIANCE_DIPLOMATIC_RELATIONS' => fn (AllianceInterface $alliance): string =>
            $this->allianceRelationRenderer->render(
                $this->allianceRelationRepository->getActiveByAlliance($alliance->getId()),
                self::RELATION_IMAGE_WIDTH,
                self::RELATION_IMAGE_HEIGHT
            )
        ];
    }
}
