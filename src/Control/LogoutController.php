<?php

declare(strict_types=1);

namespace Stu\Control;

use Stu\Lib\SessionInterface;
use Stu\Orm\Repository\SessionStringRepositoryInterface;

final class LogoutController extends GameController
{

    private $default_tpl = '';

    private $session;

    public function __construct(
        SessionInterface $session,
        SessionStringRepositoryInterface $sessionStringRepository
    ) {
        $this->session = $session;
        parent::__construct(
            $session,
            $sessionStringRepository,
            $this->default_tpl,
            'Logout'
        );
    }

    public function logout(): void {
        $this->session->createSession();

        $this->session->logout();
    }
}