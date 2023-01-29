<?php

namespace Stu\Orm\Entity;

interface PartnerSiteInterface
{
    public function getId(): int;

    public function getName(): string;

    public function getUrl(): string;

    public function getText(): string;

    public function getBanner(): string;
}
