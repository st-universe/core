<?php

namespace Stu\Component\Spacecraft\Trait;

use Stu\Component\Game\TimeConstants;

trait SpacecraftHoldingWebTrait
{
    use SpacecraftTrait;

    public function isHeldByTholianWeb(): bool
    {
        return $this->getThis()->getHoldingWeb() !== null;
    }

    public function getHoldingWebBackgroundStyle(): string
    {
        $holdingWeb = $this->getThis()->getHoldingWeb();
        if ($holdingWeb === null) {
            return '';
        }

        if ($holdingWeb->isFinished()) {
            $icon =  'web.png';
        } else {
            $closeTofinish = $holdingWeb->getFinishedTime() - time() < TimeConstants::ONE_HOUR_IN_SECONDS;

            $icon = $closeTofinish ? 'web_u.png' : 'web_u2.png';
        }

        return sprintf('src="assets/buttons/%s"; class="indexedGraphics" style="z-index: 5;"', $icon);
    }

    public function getHoldingWebImageStyle(): string
    {
        $holdingWeb = $this->getThis()->getHoldingWeb();
        if ($holdingWeb === null) {
            return '';
        }

        if ($holdingWeb->isFinished()) {
            $icon =  'webfill.png';
        } else {
            $closeTofinish = $holdingWeb->getFinishedTime() - time() < TimeConstants::ONE_HOUR_IN_SECONDS;

            $icon = $closeTofinish ? 'web_ufill.png' : 'web_ufill2.png';
        }

        return $icon;
    }
}
