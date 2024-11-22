<?php

declare(strict_types=1);

namespace Stu;

use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\Configuration\Migration\PhpFile;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Tools\Console\Command\DiffCommand;
use Doctrine\Migrations\Tools\Console\Command\ExecuteCommand;
use Doctrine\Migrations\Tools\Console\Command\MigrateCommand;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\UpdateCommand;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\ORM\Tools\Console\EntityManagerProvider\SingleManagerProvider;
use JetBrains\PhpStorm\Deprecated;
use Mockery;
use Override;
use Psr\Container\ContainerInterface;
use request;
use RuntimeException;
use Spatie\Snapshots\MatchesSnapshots;
use Stu\Config\Init;
use Stu\Config\StuContainer;
use Stu\Lib\Session\SessionStringFactoryInterface;
use Stu\Lib\SessionInterface;
use Stu\Module\Control\BenchmarkResultInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\Render\GameTwigRendererInterface;
use Stu\Module\Control\StuRandom;
use Stu\Module\Control\StuTime;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Game\Lib\Component\ComponentLoaderInterface;
use Stu\Module\Twig\TwigHelper;
use Stu\Module\Twig\TwigPageInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;

abstract class TwigTestCase extends StuTestCase
{
    use MatchesSnapshots;

    private static string $INTTEST_MIGRATIONS_CONFIG_PATH = 'dist/db/migrations/testdata.php';
    private static string $INTTEST_CONFIG_PATH = '%s/config.intttest.json';

    private static bool $isSchemaCreated = false;
    private static ?StuContainer $INTTEST_CONTAINER = null;

    #[Override]
    public function setUp(): void
    {
        $this->initializeSchema();

        $dic = $this->getContainer();

        $dic->get(GameControllerInterface::class)->resetGameData();
        $dic->get(TwigPageInterface::class)->resetVariables();
        $dic->get(ComponentLoaderInterface::class)->resetComponents();
    }

    protected abstract function getViewControllerClass(): string;

    /** @param array<string, mixed> $requestVars*/
    protected function renderSnapshot(array $requestVars, ViewControllerInterface $viewController = null): void
    {
        $dic = $this->getContainer();

        request::setMockVars($requestVars);

        $game = $dic->get(GameControllerInterface::class);
        $twigRenderer = $dic->get(GameTwigRendererInterface::class);
        $subject = $viewController ?? $dic->get($this->getViewControllerClass());

        // execute ViewController and render
        $subject->handle($game);
        $renderResult = $twigRenderer->render($game, $game->getUser());

        $this->assertMatchesHtmlSnapshot($renderResult);
    }

    protected function loadTestData(TestDataInterface $testData): int
    {
        $object = $testData->insertTestData();

        $this->getContainer()
            ->get(EntityManagerInterface::class)
            ->flush();

        return $object->getId();
    }

    #[Deprecated()]
    protected function loadTestDataMigration(string $className): void
    {
        $inputString = str_replace('\\', '\\\\', sprintf("execute --configuration=\"%s\" --quiet %s --up", $className));

        $this->runCommandWithDependecyFactory(ExecuteCommand::class, new StringInput($inputString));
    }

    private function initializeSchema(): void
    {
        if (!self::$isSchemaCreated) {

            //$this->createInitialDiff();
            //$this->forceSchemaUpdate($dic);

            $dic = $this->getContainer();

            $this->initializeTestData()
                ->setupServiceMocks($dic)
                ->setupTemplateEngine($dic);

            self::$isSchemaCreated = true;
        }
    }

    private function setupServiceMocks(StuContainer $dic): TwigTestCase
    {
        $sessionMock = $this->mock(SessionInterface::class);
        $sessionMock->shouldReceive('getUser')
            ->zeroOrMoreTimes()
            ->andReturn($dic->get(UserRepositoryInterface::class)->find(101));

        $sessionStringFactoryMock = $this->mock(SessionStringFactoryInterface::class);
        $sessionStringFactoryMock->shouldReceive('createSessionString')
            ->zeroOrMoreTimes()
            ->andReturn('MOCKED_SESSIONSTRING');

        $stuRandomMock = $this->mock(StuRandom::class);
        $stuRandomMock->shouldReceive('uniqid')
            ->zeroOrMoreTimes()
            ->andReturn('MOCKED_UNIQUEID');

        $stuTimeMock = $this->mock(StuTime::class);
        $stuTimeMock->shouldReceive('time')
            ->zeroOrMoreTimes()
            ->andReturn(1732214228);
        $stuTimeMock->shouldReceive('date')
            ->with('d M Y')
            ->zeroOrMoreTimes()
            ->andReturn('21 Nov 2024');
        $stuTimeMock->shouldReceive('date')
            ->with('M, d Y G:i:s')
            ->zeroOrMoreTimes()
            ->andReturn('Nov, 21 2024 14:30:45');
        $stuTimeMock->shouldReceive('transformToStuDate')
            ->with(Mockery::any())
            ->zeroOrMoreTimes()
            ->andReturn('21.07.2394');
        $stuTimeMock->shouldReceive('transformToStuDateTime')
            ->with(Mockery::any())
            ->zeroOrMoreTimes()
            ->andReturn('21.07.2394 11:30');

        $benchmarkResultMock = $this->mock(BenchmarkResultInterface::class);
        $benchmarkResultMock->shouldReceive('getResult')
            ->zeroOrMoreTimes()
            ->andReturn([
                'executionTime' => '4.203s',
                'memoryPeakUsage' => '42.77Mb'
            ]);

        $dic->setAdditionalService(SessionInterface::class, $sessionMock);
        $dic->setAdditionalService(SessionStringFactoryInterface::class, $sessionStringFactoryMock);
        $dic->setAdditionalService(StuRandom::class, $stuRandomMock);
        $dic->setAdditionalService(StuTime::class, $stuTimeMock);
        $dic->setAdditionalService(BenchmarkResultInterface::class, $benchmarkResultMock);

        return $this;
    }

    private function setupTemplateEngine(ContainerInterface $dic): TwigTestCase
    {
        $twigHelper = $dic->get(TwigHelper::class);
        $twigHelper->registerFiltersAndFunctions();
        $twigHelper->registerGlobalVariables();

        return $this;
    }

    private function createInitialDiff(): void
    {
        $this->runCommandWithDependecyFactory(
            DiffCommand::class,
            new StringInput(sprintf("diff --configuration=\"%s\"", self::$INTTEST_MIGRATIONS_CONFIG_PATH))
        );
    }

    private function forceSchemaUpdate(ContainerInterface $dic): void
    {
        $entityManagerProvider = new SingleManagerProvider($dic->get(EntityManagerInterface::class));

        $application = ConsoleRunner::createApplication(
            $entityManagerProvider,
            [new UpdateCommand($entityManagerProvider)]
        );

        // create schema
        $application->setAutoExit(false);
        $exitCode = $application->run(new StringInput('orm:schema-tool:update --force'));

        if ($exitCode != 0) {
            throw new RuntimeException('Could not force schema update!');
        }
    }

    private function initializeTestData(): TwigTestCase
    {
        $this->runCommandWithDependecyFactory(
            MigrateCommand::class,
            new StringInput(sprintf(
                "migrate --configuration=\"%s\" --all-or-nothing --allow-no-migration --no-interaction",
                self::$INTTEST_MIGRATIONS_CONFIG_PATH
            ))
        );

        return $this;
    }

    private function runCommandWithDependecyFactory(string $command, InputInterface $input): void
    {
        $dic = $this->getContainer();
        $entityManager = $dic->get(EntityManagerInterface::class);

        $entityManager->wrapInTransaction(function (EntityManagerInterface $entityManager) use ($command, $input) {

            $entityManagerProvider = new SingleManagerProvider($entityManager);
            $config = new PhpFile(self::$INTTEST_MIGRATIONS_CONFIG_PATH);
            $dependencyFactory = DependencyFactory::fromEntityManager(
                $config,
                new ExistingEntityManager($entityManager)
            );
            $application = ConsoleRunner::createApplication(
                $entityManagerProvider,
                [new $command($dependencyFactory)]
            );

            $application->setAutoExit(false);
            if ($application->run($input) != 0) {
                throw new RuntimeException(sprintf('Could not execute %s!', $command));
            }
        });
    }

    protected function getContainer(): StuContainer
    {
        if (self::$INTTEST_CONTAINER === null) {
            self::$INTTEST_CONTAINER = Init::getContainer(self::$INTTEST_CONFIG_PATH, true);
        }

        return self::$INTTEST_CONTAINER;
    }
}
