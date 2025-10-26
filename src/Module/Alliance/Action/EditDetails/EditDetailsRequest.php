<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\EditDetails;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class EditDetailsRequest implements EditDetailsRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getName(): string
    {
        return $this->tidyString(
            $this->parameter('name')->string()->required()
        );
    }

    #[\Override]
    public function getHomepage(): string
    {
        return $this->tidyString(
            $this->parameter('homepage')->string()->defaultsToIfEmpty('')
        );
    }

    #[\Override]
    public function getDescription(): string
    {
        return $this->tidyString(
            $this->parameter('description')->string()->defaultsToIfEmpty('')
        );
    }

    #[\Override]
    public function getFactionMode(): int
    {
        return $this->parameter('factionid')->int()->defaultsTo(0);
    }

    #[\Override]
    public function getAcceptApplications(): int
    {
        return $this->parameter('acceptapp')->int()->defaultsTo(0);
    }

    #[\Override]
    public function getRgbCode(): string
    {
        return $this->tidyString(
            $this->parameter('rgb')->string()->required()
        );
    }

    #[\Override]
    public function getJobIdFounder(): int
    {
        return $this->parameter('job_id_founder')->int()->defaultsTo(0);
    }

    #[\Override]
    public function getJobTitleFounder(): string
    {
        return $this->tidyString(
            $this->parameter('job_title_founder')->string()->defaultsToIfEmpty('')
        );
    }

    #[\Override]
    public function getJobIdSuccessor(): int
    {
        return $this->parameter('job_id_successor')->int()->defaultsTo(0);
    }

    #[\Override]
    public function getJobTitleSuccessor(): string
    {
        return $this->tidyString(
            $this->parameter('job_title_successor')->string()->defaultsToIfEmpty('')
        );
    }

    #[\Override]
    public function getJobIdDiplomatic(): int
    {
        return $this->parameter('job_id_diplomatic')->int()->defaultsTo(0);
    }

    #[\Override]
    public function getJobTitleDiplomatic(): string
    {
        return $this->tidyString(
            $this->parameter('job_title_diplomatic')->string()->defaultsToIfEmpty('')
        );
    }
}
