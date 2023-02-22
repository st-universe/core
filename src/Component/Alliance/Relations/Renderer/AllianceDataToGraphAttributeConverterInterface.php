<?php

declare(strict_types=1);

namespace Stu\Component\Alliance\Relations\Renderer;

use Stu\Orm\Entity\AllianceInterface;

interface AllianceDataToGraphAttributeConverterInterface
{
    /**
     * Renders bbcode and strip unwanted characters
     */
    public function convertName(AllianceInterface $alliance): string;

    /**
     * Returns the frame color depending on certain alliance attributes/settings
     */
    public function getFrameColor(
        AllianceInterface $alliance,
        string $defaultColor = '#8b8b8b'
    ): string;

    /**
     * Returns the url to the alliance's detail view
     */
    public function getUrl(
        AllianceInterface $alliance
    ): string;

    /**
     * Returns the fille color depending on alliance attributes
     */
    public function getFillColor(
        AllianceInterface $alliance
    ): string;
}
