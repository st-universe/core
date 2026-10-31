<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting\Action\ChangeBorgAssimilation;

use request;
use Stu\ActionControllerTestCase;
use Stu\Lib\Session\SessionStorageInterface;
use Stu\Module\Control\StuTime;
use Stu\Module\Game\Lib\BorgAssimilation;
use Stu\Orm\Entity\User;

final class ChangeBorgAssimilationTest extends ActionControllerTestCase
{
    public function testRejectsGetAndUnknownModesWithoutChangingTheAccount(): void
    {
        $server = $_SERVER;
        try {
            foreach ([['GET', 'disable'], ['POST', 'unknown']] as [$method, $mode]) {
                $_SERVER['REQUEST_METHOD'] = $method;
                request::setMockVars([ChangeBorgAssimilation::ACTION_IDENTIFIER => $mode]);
                $storage = $this->mock(SessionStorageInterface::class);
                $storage->shouldNotReceive('deleteSessionData', 'storeSessionData');
                $time = $this->mock(StuTime::class);
                $time->shouldNotReceive('time');
                $this->game->shouldNotReceive('getUser', 'getInfo');
                $action = new ChangeBorgAssimilation(new BorgAssimilation($storage, $time));

                $action->handle($this->game);

                self::assertTrue($action->performSessionCheck());
            }
        } finally {
            $_SERVER = $server;
        }
    }

    public function testDeassimilatesOnlyTheCurrentAccount(): void
    {
        $server = $_SERVER;
        $session = $_SESSION ?? null;
        try {
            $_SERVER['REQUEST_METHOD'] = 'POST';
            request::setMockVars([ChangeBorgAssimilation::ACTION_IDENTIFIER => 'disable']);
            $storage = $this->mock(SessionStorageInterface::class);
            $storage->shouldReceive('deleteSessionData')->with('anniversary_25_borg_started_at')->once();
            $storage->shouldReceive('deleteSessionData')->with('anniversary_25_borg_disabled')->once();
            $storage->shouldReceive('storeSessionData')->with('anniversary_25_borg_disabled', true, true)->once();
            $this->game->shouldReceive('getUser')->once()->andReturn(new User());
            $this->game->shouldReceive('getInfo->addInformation')->once()
                ->with('Die Assimilation ist für deinen Account deaktiviert. Dein gewähltes Interface-Design bleibt erhalten');

            new ChangeBorgAssimilation(new BorgAssimilation($storage, $this->mock(StuTime::class)))->handle($this->game);
        } finally {
            $_SERVER = $server;
            if ($session === null) {
                unset($_SESSION);
            } else {
                $_SESSION = $session;
            }
        }
    }

    public function testReassimilationStartsAFreshTimelineAndAcknowledgesTheReset(): void
    {
        $server = $_SERVER;
        $session = $_SESSION ?? null;
        try {
            $_SERVER['REQUEST_METHOD'] = 'POST';
            request::setMockVars([ChangeBorgAssimilation::ACTION_IDENTIFIER => 'enable']);
            $storage = $this->mock(SessionStorageInterface::class);
            $storage->shouldReceive('deleteSessionData')->with('anniversary_25_borg_disabled')->once();
            $storage->shouldReceive('deleteSessionData')->with('anniversary_25_borg_started_at')->once();
            $storage->shouldReceive('storeSessionData')->with('anniversary_25_borg_started_at', 1000, true)->once();
            $time = $this->mock(StuTime::class);
            $time->shouldReceive('time')->once()->andReturn(1000);
            $this->game->shouldReceive('getUser')->once()->andReturn(new User());
            $this->game->shouldReceive('setTemplateVar')->with('BORG_ASSIMILATION_RESTARTED', true)->once();
            $this->game->shouldReceive('getInfo->addInformation')->with('Die Assimilation wurde neu gestartet')->once();

            new ChangeBorgAssimilation(new BorgAssimilation($storage, $time))->handle($this->game);
        } finally {
            $_SERVER = $server;
            if ($session === null) {
                unset($_SESSION);
            } else {
                $_SESSION = $session;
            }
        }
    }
}
