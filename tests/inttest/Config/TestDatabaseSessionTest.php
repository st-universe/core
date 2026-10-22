<?php

declare(strict_types=1);

namespace Stu\Config;

use Doctrine\ORM\EntityManagerInterface;
use Stu\IntegrationTestCase;
use Stu\Lib\Session\SessionInterface;
use Stu\Lib\Session\SessionStorageInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\StuMocks;

final class TestDatabaseSessionTest extends IntegrationTestCase
{
    public function testSessionStorageKeepsTestSessionAcrossCleanupUserChangesAndDatabaseRebuild(): void
    {
        $dic = $this->getContainer();
        $dic->clearAdditionalServices();
        self::assertSame(self::$testSession, $dic->get(SessionInterface::class));
        $storage = $dic->get(SessionStorageInterface::class);
        self::$testSession->setUserById(101);
        $storage->storeSessionData('database-reset-regression', 'Spieler 101', true);
        self::assertSame('Spieler 101', $storage->getSessionValue('database-reset-regression'));
        self::$testSession->setUserById(102);
        self::assertFalse($storage->getSessionValue('database-reset-regression'));
        $storage->storeSessionData('database-reset-regression', 'Spieler 102', true);
        StuMocks::get()->reset();
        self::assertSame(self::$testSession, $dic->get(SessionInterface::class));
        self::assertSame('Spieler 102', $storage->getSessionValue('database-reset-regression'));
        self::$testSession->setUserById(101);
        self::assertSame('Spieler 101', $storage->getSessionValue('database-reset-regression'));

        self::$isSchemaInitializationNeeded = true;
        parent::setUp();
        self::$isSchemaInitializationNeeded = true;

        self::assertSame($storage, $dic->get(SessionStorageInterface::class));
        self::assertFalse($storage->getSessionValue('database-reset-regression'));
        $storage->storeSessionData('database-reset-regression', 'Neue Testdatenbank', true);
        $dic->get(EntityManagerInterface::class)->flush();
        self::assertSame('Neue Testdatenbank', $storage->getSessionValue('database-reset-regression'));
    }

    public function testDatabaseRebuildRefreshesUserForExistingSessionAndController(): void
    {
        $dic = $this->getContainer();
        self::$testSession->setUserById(101);
        $session = self::$testSession;
        $game = $dic->get(GameControllerInterface::class);
        $previousUser = $game->getUser();
        $previousUser->getAlliance();
        $previousUser->getFaction();
        $previousUser->getColonies()->toArray();
        $previousUser->getUserLayers()->toArray();

        self::$isSchemaInitializationNeeded = true;
        parent::setUp();
        self::$isSchemaInitializationNeeded = true;

        $em = $dic->get(EntityManagerInterface::class);
        $user = $game->getUser();
        self::assertSame($session, self::$testSession);
        self::assertSame(101, $user->getId());
        self::assertNotSame($previousUser, $user);
        self::assertFalse($em->contains($previousUser));
        self::assertTrue($em->contains($user));
        $user->setDescription('Beschreibung nach Neuaufbau der Testdatenbank');
        $dic->get(UserRepositoryInterface::class)->save($user);
        $em->flush();
        $em->refresh($user);
        self::assertSame('Beschreibung nach Neuaufbau der Testdatenbank', $user->getDescription());
    }
}
