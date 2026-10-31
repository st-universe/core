<?php

declare(strict_types=1);

namespace Stu\Config;

use Doctrine\ORM\EntityManagerInterface;
use Stu\IntegrationTestCase;
use Stu\Lib\Session\SessionStorageInterface;
use Stu\Module\Control\StuTime;
use Stu\Module\Game\Lib\BorgAssimilation;
use Stu\Orm\Repository\UserRepositoryInterface;

final class BorgAssimilationPersistenceTest extends IntegrationTestCase
{
    public function testGuestProgressSurvivesAnEntirelyNewSessionAndKeepsOtherAccountData(): void
    {
        $previousSession = $_SESSION ?? null;
        try {
            $container = $this->getContainer();
            $storage = $container->get(SessionStorageInterface::class);
            $entityManager = $container->get(EntityManagerInterface::class);
            $users = $container->get(UserRepositoryInterface::class);
            self::$testSession->setUserById(101);
            $user = self::$testSession->getUser();
            $user->setSessionData(serialize(['hiddenfleets' => [12 => 1]]));
            $users->save($user);
            $storage->reset();
            $_SESSION = ['anniversary_25_borg_started_at' => 920];
            $time = $this->mock(StuTime::class);
            $time->shouldReceive('time')->twice()->andReturn(1000, 2000);
            $assimilation = new BorgAssimilation($storage, $time);

            self::assertSame(80_000, $assimilation->getElapsedMilliseconds($user));
            $entityManager->flush();
            $entityManager->clear();
            $storage->reset();
            self::$testSession->setUser(null);
            $_SESSION = [];
            $restoredUser = $users->find(101);
            self::$testSession->setUser($restoredUser);

            self::assertSame(BorgAssimilation::DURATION_MILLISECONDS, $assimilation->getElapsedMilliseconds($restoredUser));
            self::assertTrue($storage->hasSessionValue('hiddenfleets', 12));
        } finally {
            if ($previousSession === null) {
                unset($_SESSION);
            } else {
                $_SESSION = $previousSession;
            }
        }
    }
}
