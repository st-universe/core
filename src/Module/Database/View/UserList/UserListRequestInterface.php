<?php

namespace Stu\Module\Database\View\UserList;

interface UserListRequestInterface
{
    public function getSortField(): string;

    public function getSortOrder(): string;

    public function getPagination(): int;
}
