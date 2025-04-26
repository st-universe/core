<?php

namespace Stu\Module\Alliance\Action\EditDetails;

interface EditDetailsRequestInterface
{
    public function getName(): string;

    public function getHomepage(): string;

    public function getDescription(): string;

    public function getFactionMode(): int;

    public function getAcceptApplications(): int;

    public function getRgbCode(): string;

    public function getFounderDescription(): string;

    public function getSuccessorDescription(): string;

    public function getDiplomaticDescription(): string;
}
