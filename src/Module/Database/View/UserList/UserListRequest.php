<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\UserList;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class UserListRequest implements UserListRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getSortField(): string
    {
        /**
         * @var string $param
         */
        $param = $this->queryParameter('order')->oneOf(['id', 'fac', 'alliance'])->defaultsTo('id');

        return $param;
    }

    #[Override]
    public function getSortOrder(): string
    {
        /**
         * @var string $param
         */
        $param = $this->queryParameter('way')->oneOf(['up', 'down'])->defaultsTo('down');

        return $param;
    }

    #[Override]
    public function getPagination(): int
    {
        return $this->queryParameter('mark')->int()->defaultsTo(0);
    }
}
