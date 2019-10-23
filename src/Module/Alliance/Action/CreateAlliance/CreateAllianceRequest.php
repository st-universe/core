<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\CreateAlliance;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class CreateAllianceRequest implements CreateAllianceRequestInterface
{
    use CustomControllerHelperTrait;

    public function getName(): string
    {
        return $this->tidyString(
            $this->queryParameter('name')->string()->required()
        );
    }

    public function getDescription(): string
    {
        return $this->tidyString(
            $this->queryParameter('description')->string()->required()
        );
    }

    public function getFactionMode(): int
    {
        return $this->queryParameter('factionid')->int()->defaultsTo(0);
    }
}
