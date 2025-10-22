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

    public function getJobIdFounder(): int;

    public function getJobTitleFounder(): string;

    public function getJobIdSuccessor(): int;

    public function getJobTitleSuccessor(): string;

    public function getJobIdDiplomatic(): int;

    public function getJobTitleDiplomatic(): string;
}
