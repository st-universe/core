<?php

declare(strict_types=1);

namespace Stu\Control;

final class LogoutController extends GameController
{

    private $default_tpl = '';

    function __construct()
    {
        parent::__construct($this->default_tpl, 'Logout');
    }
}