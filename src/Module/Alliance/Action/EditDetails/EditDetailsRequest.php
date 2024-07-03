<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\EditDetails;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class EditDetailsRequest implements EditDetailsRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getName(): string
    {
        return $this->tidyString(
            $this->queryParameter('name')->string()->required()
        );
    }

    #[Override]
    public function getHomepage(): string
    {
        return $this->tidyString(
            $this->queryParameter('homepage')->string()->defaultsToIfEmpty('')
        );
    }

    #[Override]
    public function getDescription(): string
    {
        return $this->tidyString(
            $this->queryParameter('description')->string()->defaultsToIfEmpty('')
        );
    }

    #[Override]
    public function getFactionMode(): int
    {
        return $this->queryParameter('factionid')->int()->defaultsTo(0);
    }

    #[Override]
    public function getAcceptApplications(): int
    {
        return $this->queryParameter('acceptapp')->int()->defaultsTo(0);
    }

    #[Override]
    public function getRgbCode(): string
    {
        return $this->tidyString(
            $this->queryParameter('rgb')->string()->required()
        );
    }
}
