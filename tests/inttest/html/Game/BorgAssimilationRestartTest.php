<?php

declare(strict_types=1);

namespace Stu\Html\Game;

use Doctrine\ORM\EntityManagerInterface;
use request;
use Stu\Component\Game\ModuleEnum;
use Stu\Lib\Session\SessionStorageInterface;
use Stu\Lib\Session\SessionStringFactory;
use Stu\Module\Control\AccessCheckInterface;
use Stu\Module\Control\Component\Action\ActionContextFactoryInterface;
use Stu\Module\Control\Component\View\ViewContextFactoryInterface;
use Stu\Module\Control\ComponentSetupInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\Render\GameTwigRendererInterface;
use Stu\Module\Control\StuTime;
use Stu\Module\Game\Lib\BorgAssimilation;
use Stu\Module\Game\View\Overview\Overview;
use Stu\Module\PlayerSetting\Action\ChangeBorgAssimilation\ChangeBorgAssimilation;
use Stu\Orm\Repository\SessionStringRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\TwigTestCase;

final class BorgAssimilationRestartTest extends TwigTestCase
{
    public function testAccountCanDeassimilatePersistentlyAndReassimilateFromItsColourSettings(): void
    {
        $previousServer = $_SERVER;
        $previousSession = $_SESSION ?? null;
        try {
            $container = $this->getContainer();
            self::$testSession->setUserById(101);
            $user = self::$testSession->getUser();
            $user->setSessionData(serialize(['hiddenfleets' => [12 => 1]]));
            $storage = $container->get(SessionStorageInterface::class);
            $storage->reset();
            $game = $container->get(GameControllerInterface::class);
            $context = $container->get(ActionContextFactoryInterface::class)->createActionContext($game);
            $action = $container->get(ChangeBorgAssimilation::class);
            $access = $container->get(AccessCheckInterface::class);
            $tokens = new SessionStringFactory($container->get(SessionStringRepositoryInterface::class), new StuTime());
            $_SERVER['REQUEST_METHOD'] = 'POST';
            request::setMockVars([ChangeBorgAssimilation::ACTION_IDENTIFIER => 'disable', 'sstr' => 'invalid']);
            self::assertFalse($access->checkUserAccess($action, $game->getInfo()));
            self::assertTrue($container->get(BorgAssimilation::class)->isEnabled($user));
            request::setMockVars([
                ChangeBorgAssimilation::ACTION_IDENTIFIER => 'disable',
                'sstr' => $tokens->createSessionString($user)
            ]);
            self::assertTrue($access->checkUserAccess($action, $game->getInfo()));

            $action->handle($context);
            $html = $this->renderSettings();

            self::assertStringContainsString('value="enable" class="button">', $html);
            self::assertStringContainsString('Reassimilieren', $html);
            self::assertStringNotContainsString('/css/borgAssimilation.css', $html);
            self::assertStringNotContainsString('/js/borgAssimilation.js', $html);
            self::assertStringContainsString('"enabled":false', $html);
            self::assertStringContainsString('"elapsed":0', $html);
            $entityManager = $container->get(EntityManagerInterface::class);
            $entityManager->flush();
            $entityManager->clear();
            $storage->reset();
            self::$testSession->setUser(null);
            $user = $container->get(UserRepositoryInterface::class)->find(101);
            self::$testSession->setUser($user);
            $_SESSION = ['anniversary_25_borg_started_at' => 700];
            $assimilation = $container->get(BorgAssimilation::class);
            self::assertFalse($assimilation->isEnabled($user));
            self::assertSame(0, $assimilation->getElapsedMilliseconds($user));
            self::assertArrayNotHasKey('anniversary_25_borg_started_at', $_SESSION);
            self::assertTrue($storage->hasSessionValue('hiddenfleets', 12));
            request::setMockVars([
                ChangeBorgAssimilation::ACTION_IDENTIFIER => 'enable',
                'sstr' => $tokens->createSessionString($user)
            ]);
            self::assertTrue($access->checkUserAccess($action, $game->getInfo()));

            $action->handle($context);
            $html = $this->renderSettings();

            self::assertStringContainsString('value="disable" class="button">', $html);
            self::assertStringContainsString('Deassimilieren', $html);
            self::assertStringContainsString('/js/borgAssimilation.js', $html);
            self::assertStringContainsString('"enabled":true', $html);
            self::assertStringContainsString('"restarted":true', $html);
            self::assertSame(0, $assimilation->getElapsedMilliseconds($user));
            $entityManager->flush();
            $entityManager->refresh($user);
            $storage->reset();
            self::assertTrue($assimilation->isEnabled($user));
            self::assertTrue($storage->hasSessionValue('hiddenfleets', 12));
        } finally {
            $_SERVER = $previousServer;
            if ($previousSession === null) {
                unset($_SESSION);
            } else {
                $_SESSION = $previousSession;
            }
        }
    }

    private function renderSettings(): string
    {
        $container = $this->getContainer();
        $game = $container->get(GameControllerInterface::class);
        $context = $container->get(ViewContextFactoryInterface::class)->createViewContext($game, ModuleEnum::OPTIONS);
        $container->get(Overview::class)->handle($context);
        $container->get(ComponentSetupInterface::class)->setup();

        return $container->get(GameTwigRendererInterface::class)->render($game, $game->getUser());
    }

}
