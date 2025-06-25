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
use Doctrine\ORM\Tools\Console\Command\GenerateProxiesCommand;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\UpdateCommand;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\ORM\Tools\Console\EntityManagerProvider\SingleManagerProvider;
use JetBrains\PhpStorm\Deprecated;
use Mockery;
use Override;
use request;
use RuntimeException;
use Spatie\Snapshots\MatchesSnapshots;
use Stu\Config\ConfigStageEnum;
use Stu\Config\Init;
use Stu\Config\StuContainer;
use Stu\Lib\Component\ComponentRegistrationInterface;
use Stu\Lib\Session\SessionStringFactoryInterface;
use Stu\Lib\Session\SessionInterface;
use Stu\Module\Control\BenchmarkResultInterface;
use Stu\Module\Control\ComponentSetupInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\Render\GameTwigRendererInterface;
use Stu\Module\Control\StuRandom;
use Stu\Module\Control\StuTime;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Twig\TwigHelper;
use Stu\Module\Twig\TwigPageInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;

abstract class TwigTestCase extends StuTestCase
{
    use MatchesSnapshots;

    private const string INTTEST_MIGRATIONS_CONFIG_PATH = 'config/migrations/inttest.php';

    private static bool $isSchemaCreated = false;
    private static bool $isMocksSetup = false;
    private static bool $isTemplateEngineSetup = false;
    private static ?StuContainer $INTTEST_CONTAINER = null;
    private static ?TestSession $testSession = null;

    #[Override]
    public function setUp(): void
    {
        $this->initializeSchemaAndTestdata();
        $this->setupMocks();
        $this->setupTemplateEngine();
    }

    #[Override]
    public function tearDown(): void
    {
        $dic = $this->getContainer();
        $dic->get(GameControllerInterface::class)->resetGameData();
        $dic->get(TwigPageInterface::class)->resetVariables();
        $dic->get(ComponentRegistrationInterface::class)->resetComponents();
    }

    public static function tearDownAfterClass(): void
    {
        StuMocks::get()->reset();
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

        $this->runCommandWithDependencyFactory(ExecuteCommand::class, new StringInput($inputString));
    }

    private function initializeSchemaAndTestdata(): void
    {
        if (!self::$isSchemaCreated) {

            //$this->createInitialDiff();
            //$this->runCommand(UpdateCommand::class, "orm:schema-tool:update --force");

            $this->runCommand(GenerateProxiesCommand::class, "orm:generate-proxies --quiet");
            $this->initializeTestData();

            self::$isSchemaCreated = true;
        }
    }

    private function setupMocks(): void
    {
        if (!self::$isMocksSetup) {

            $this->setupTestSession();
            $this->setupServiceMocks();
            self::$isMocksSetup = true;
        }
    }

    private function setupTestSession(): void
    {
        if (self::$testSession === null) {
            $dic = $this->getContainer();
            self::$testSession = new TestSession($dic->get(UserRepositoryInterface::class));
            $dic->setAdditionalService(SessionInterface::class, self::$testSession);
        }
    }

    private function setupServiceMocks(): TwigTestCase
    {
        $dic = $this->getContainer();

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

        $dic
            ->setAdditionalService(SessionStringFactoryInterface::class, $sessionStringFactoryMock)
            ->setAdditionalService(StuRandom::class, $stuRandomMock)
            ->setAdditionalService(StuTime::class, $stuTimeMock)
            ->setAdditionalService(BenchmarkResultInterface::class, $benchmarkResultMock);

        return $this;
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

    private function createInitialDiff(): void
    {
        $this->runCommandWithDependencyFactory(
            DiffCommand::class,
            new StringInput(sprintf("diff --configuration=\"%s\" --from-empty-schema", self::INTTEST_MIGRATIONS_CONFIG_PATH))
        );
    }

    private function initializeTestData(): TwigTestCase
    {
        $this->runCommandWithDependencyFactory(
            MigrateCommand::class,
            new StringInput(sprintf(
                "migrate --configuration=\"%s\" --all-or-nothing --allow-no-migration --no-interaction --quiet", // -vv",
                self::INTTEST_MIGRATIONS_CONFIG_PATH
            ))
        );

        return $this;
    }

    private function runCommand(string $commandClass, string $input): void
    {
        $dic = $this->getContainer();
        $entityManagerProvider = new SingleManagerProvider($dic->get(EntityManagerInterface::class));

        $application = ConsoleRunner::createApplication(
            $entityManagerProvider,
            [new $commandClass($entityManagerProvider)]
        );

        $application->setAutoExit(false);
        $exitCode = $application->run(new StringInput($input));

        if ($exitCode != 0) {
            throw new RuntimeException(sprintf('Could not execute %s!', $input));
        }
    }

    private function runCommandWithDependencyFactory(string $command, InputInterface $input): void
    {
        $dic = $this->getContainer();
        $entityManager = $dic->get(EntityManagerInterface::class);

        $entityManager->wrapInTransaction(function (EntityManagerInterface $entityManager) use ($command, $input): void {

            $entityManagerProvider = new SingleManagerProvider($entityManager);
            $config = new PhpFile(self::INTTEST_MIGRATIONS_CONFIG_PATH);
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
            self::$INTTEST_CONTAINER = Init::getContainer(ConfigStageEnum::INTEGRATION_TEST, true);
        }

        return self::$INTTEST_CONTAINER;
    }
}
