<?php

namespace Stu\Orm\Entity;

interface RpgPlotMemberInterface
{
    public function getId(): int;

    public function getPlotId(): int;

    public function setPlotId(int $plotId): RpgPlotMemberInterface;

    public function getUserId(): int;

    public function setUserId(int $userId): RpgPlotMemberInterface;
}