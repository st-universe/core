<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\CreateAlliance;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class CreateAllianceRequest implements CreateAllianceRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getName(): string
    {
        return $this->tidyString(
            $this->parameter('name')->string()->required()
        );
    }

    #[Override]
    public function getDescription(): string
    {
        return $this->tidyString(
            $this->parameter('description')->string()->required()
        );
    }

    #[Override]
    public function getFactionMode(): int
    {
        return $this->parameter('factionid')->int()->defaultsTo(0);
    }
}
