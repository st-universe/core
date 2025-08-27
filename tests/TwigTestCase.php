<?php

declare(strict_types=1);

namespace Stu;

use Doctrine\ORM\Tools\Console\Command\GenerateProxiesCommand;
use Override;
use request;
use Spatie\Snapshots\MatchesSnapshots;
use Stu\Module\Control\ComponentSetupInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\Render\GameTwigRendererInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Twig\TwigHelper;

abstract class TwigTestCase extends IntegrationTestCase
{
    use MatchesSnapshots;

    private static bool $isSchemaCreated = false;
    private static bool $isTemplateEngineSetup = false;

    #[Override]
    public function setUp(): void
    {
        parent::setUp();
        $this->initializeSchemaAndTestdata();
        $this->setupTemplateEngine();
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

    private function initializeSchemaAndTestdata(): void
    {
        if (!self::$isSchemaCreated) {

            $this->initializeTestData();

            self::$isSchemaCreated = true;
        }
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
