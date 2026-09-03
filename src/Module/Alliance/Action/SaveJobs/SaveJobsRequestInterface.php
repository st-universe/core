<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\SaveJobs;

interface SaveJobsRequestInterface
{
    public function getJobsData(): string;
}
