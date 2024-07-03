<?php

declare(strict_types=1);

namespace Stu\Module\Index\View\ShowRegistration;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class ShowRegistrationRequest implements ShowRegistrationRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getToken(): string
    {
        return $this->queryParameter('token')->string()->defaultsToIfEmpty('');
    }
}
