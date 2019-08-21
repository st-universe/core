<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\EditDetails;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class EditDetailsRequest implements EditDetailsRequestInterface
{
    use CustomControllerHelperTrait;

    public function getName(): string
    {
        return trim(tidyString(strip_tags(
            $this->queryParameter('name')->string()->required()
        )));
    }

    public function getHomepage(): string
    {
        return trim(tidyString(strip_tags(
            $this->queryParameter('homepage')->string()->defaultsToIfEmpty('')
        )));
    }

    public function getDescription(): string
    {
        return trim(tidyString(strip_tags(
            $this->queryParameter('description')->string()->defaultsToIfEmpty('')
        )));
    }

    public function getFactionMode(): int
    {
        return $this->queryParameter('factionid')->int()->defaultsTo(0);
    }

    public function getAcceptApplications(): int
    {
        return $this->queryParameter('acceptapp')->int()->defaultsTo(0);
    }
}