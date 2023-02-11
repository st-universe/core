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
    private const RELATION_IMAGE_WIDTH = 600;
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
            '/\$(ALLIANCE_[^\s]+)/',
            function ($match) use ($replacementVars, $alliance): string {
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
            'ALLIANCE_HOMEPAGE_LINK' => fn (AllianceInterface $alliance): string =>
                sprintf('<a href="%s" target="_blank">%s</a>', $alliance->getHomepage(), 'Zur Allianz Homepage'),
            'ALLIANCE_BANNER' => function (AllianceInterface $alliance): string {
                $avatar = $alliance->getAvatar();

                return $avatar !== ''
                    ? sprintf('<img src="%s/%s.png" />', $this->config->get('game.alliance_avatar_path'), $avatar)
                    : '';
            },
            'ALLIANCE_PRESIDENT' => fn (AllianceInterface $alliance): string =>
                $alliance->getFounder()->getUser()->getUserName(),
            'ALLIANCE_VICEPRESIDENT' => fn (AllianceInterface $alliance): string =>
                $alliance->getSuccessor()
                    ? $alliance->getSuccessor()->getUser()->getUserName()
                    : 'Unbesetzt',
            'ALLIANCE_FOREIGNMINISTER' => fn (AllianceInterface $alliance): string =>
                $alliance->getDiplomatic()
                    ? $alliance->getDiplomatic()->getUser()->getUserName()
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