<?php

declare(strict_types=1);

namespace Stu;

use Override;
use request;
use Spatie\Snapshots\MatchesSnapshots;
use Stu\Lib\Component\ComponentRegistrationInterface;
use Stu\Module\Control\ComponentSetupInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\JavascriptExecution;
use Stu\Module\Control\Render\GameTwigRendererInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Twig\TwigHelper;
use Stu\Module\Twig\TwigPageInterface;

abstract class TwigTestCase extends IntegrationTestCase
{
    use MatchesSnapshots;

    private static bool $isTemplateEngineSetup = false;

    #[Override]
    public function setUp(): void
    {
        parent::setUp();
        $this->setupTemplateEngine();
    }

    #[Override]
    public function tearDown(): void
    {
        parent::tearDown();
        $dic = $this->getContainer();
        $dic->get(TwigPageInterface::class)->resetVariables();
        $dic->get(ComponentRegistrationInterface::class)->resetComponents();
        JavascriptExecution::reset();
    }

    /**
     * @param class-string<ViewControllerInterface>|ViewControllerInterface $viewController
     * @param array<string, mixed> $requestVars
     */
    protected function renderSnapshot(int $userId, string|ViewControllerInterface $viewController, array $requestVars): void
    {
        $dic = $this->getContainer();

        self::$testSession->setUserById($userId);
        request::setMockVars($requestVars);

        $game = $dic->get(GameControllerInterface::class);
        $subject = $viewController instanceof ViewControllerInterface ? $viewController : $dic->get($viewController);

        // execute ViewController setup components and render
        $subject->handle($game);
        $dic->get(ComponentSetupInterface::class)->setup($game);
        $renderResult = $dic->get(GameTwigRendererInterface::class)->render($game, $game->getUser());

        $this->assertMatchesHtmlSnapshot($renderResult);
    }

    private function setupTemplateEngine(): void
    {
        if (!self::$isTemplateEngineSetup) {
            $twigHelper = $this->getContainer()->get(TwigHelper::class);
            $twigHelper->registerFiltersAndFunctions();
            $twigHelper->registerGlobalVariables();
            self::$isTemplateEngineSetup = true;
        }
    }
}
