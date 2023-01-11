<?php

namespace Stu\Orm\Entity;


interface LayerInterface
{
    public function getId(): int;

    public function getName(): string;

    public function getWidth(): int;

    public function getHeight(): int;

    public function isHidden(): bool;
}
