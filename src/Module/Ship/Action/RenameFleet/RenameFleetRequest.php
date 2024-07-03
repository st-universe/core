<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\RenameFleet;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class RenameFleetRequest implements RenameFleetRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getFleetId(): int
    {
        return $this->queryParameter('fleetid')->int()->required();
    }

    #[Override]
    public function getNewName(): string
    {
        return trim(strip_tags($this->queryParameter('fleetname')->string()->defaultsToIfEmpty('')));
    }
}
