<?php

declare(strict_types=1);

namespace Stu\Control;

use Stu\Lib\SessionInterface;

final class LogoutController extends GameController
{

    private $default_tpl = '';

    private $session;

    public function __construct(
        SessionInterface $session
    )
    {
        $this->session = $session;
        parent::__construct($session, $this->default_tpl, 'Logout');
    }

    public function logout(): void {
        $this->session->createSession();

        $this->session->logout();
    }
}