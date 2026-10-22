<?php

declare(strict_types=1);

namespace Stu\Config;

use Doctrine\ORM\EntityManagerInterface;
use Stu\IntegrationTestCase;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class TestDatabaseSessionTest extends IntegrationTestCase
{
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
