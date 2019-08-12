<?php

declare(strict_types=1);

namespace Stu\Lib;

interface SessionInterface
{
    public function createSession(bool $session_check = true): void;

    public function checkLoginCookie();

    public function setSessionVar($var, $value);

    public function getSessionVar($var);

    public function removeSessionVar($var);

    public function getUser();

    public function logout();

    public function sessionIsSafe();

    public function getSessionString();

    public function storeSessionData($key, $value);

    public function deleteSessionData($key, $value);

    public function hasSessionValue($key, $value);
}