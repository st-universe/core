<?php

namespace Stu\Module\Spacecraft\Action\TransferWarpcoreCharge;

interface TransferWarpcoreChargeRequestInterface
{
    public function getSpacecraftId(): int;

    /**
     * @return array<int>
     */
    public function getTargetSpacecraftIds(): array;

    /**
     * @return array<int, int>
     */
    public function getTransferAmounts(): array;
}