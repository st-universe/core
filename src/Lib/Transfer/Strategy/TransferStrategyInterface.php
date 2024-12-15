<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer\Strategy;

use Stu\Lib\Information\InformationInterface;
use Stu\Lib\Transfer\Wrapper\StorageEntityWrapperInterface;
use Stu\Module\Control\GameControllerInterface;

interface TransferStrategyInterface
{
    public function setTemplateVariables(
        bool $isUnload,
        StorageEntityWrapperInterface $source,
        StorageEntityWrapperInterface $target,
        GameControllerInterface $game
    ): void;

    public function transfer(
        bool $isUnload,
        StorageEntityWrapperInterface $source,
        StorageEntityWrapperInterface $target,
        InformationInterface $information
    ): void;
}
