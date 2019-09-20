<?php

namespace Stu\Module\Colony\Lib;

interface ColonySurfaceInterface
{
    public function getSurface(): array;

    public function getSurfaceTileCssClass(): string;

    public function getEpsBoxTitleString(): string;
}