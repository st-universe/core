<?php

namespace Stu\Orm\Entity;

interface KnPostToPlotApplicationInterface
{
    public function getId(): int;

    public function getRpgPlot(): RpgPlotInterface;

    public function setRpgPlot(RpgPlotInterface $rpgPlot): KnPostToPlotApplicationInterface;

    public function getKnPost(): KnPostInterface;

    public function setKnPost(KnPostInterface $knPost): KnPostToPlotApplicationInterface;

    public function getTime(): int;

    public function setTime(int $time): KnPostToPlotApplicationInterface;
}
