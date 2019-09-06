<?php

namespace Stu\Orm\Entity;

interface GameConfigInterface
{
    public function getId(): int;

    public function getOption(): int;

    public function setOption(int $option): GameConfigInterface;

    public function getValue(): int;

    public function setValue(int $value): GameConfigInterface;
}