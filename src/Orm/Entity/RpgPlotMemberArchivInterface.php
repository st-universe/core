<?php

namespace Stu\Orm\Entity;

interface RpgPlotMemberArchivInterface
{
    public function getId(): int;

    public function getVersion(): ?string;

    public function getFormerId(): int;

    public function getPlotId(): int;

    public function getUserId(): int;

    public function getRpgPlot(): RpgPlotArchivInterface;

    public function setRpgPlot(RpgPlotArchivInterface $rpgPlot): RpgPlotMemberArchivInterface;
}
