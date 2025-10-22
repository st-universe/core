<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\SaveJobs;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class SaveJobsRequest implements SaveJobsRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getJobsData(): string
    {
        return $this->parameter('jobs')->string()->defaultsToIfEmpty('[]');
    }
}
