<?php

namespace Stu\Orm\Entity;

interface RpgPlotMemberInterface
{
    public function getId(): int;

    public function getPlotId(): int;

    public function getUserId(): int;

    public function getRpgPlot(): RpgPlotInterface;

    public function setRpgPlot(RpgPlotInterface $rpgPlot): RpgPlotMemberInterface;

    public function getUser(): UserInterface;

    public function setUser(UserInterface $user): RpgPlotMemberInterface;
}
