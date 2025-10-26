<?php

declare(strict_types=1);

namespace Stu;

use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\Configuration\Migration\PhpFile;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Tools\Console\Command\MigrateCommand;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\DropCommand;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\ORM\Tools\Console\EntityManagerProvider\SingleManagerProvider;
use Mockery;
use Stu\Component\Database\AchievementManager;
use Stu\Config\ConfigStageEnum;
use Stu\Config\Init;
use Stu\Config\StuContainer;
use Stu\Lib\Component\ComponentRegistrationInterface;
use Stu\Lib\Session\SessionInterface;
use Stu\Lib\Session\SessionStringFactoryInterface;
use Stu\Module\Control\BenchmarkResultInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\StuRandom;
use Stu\Module\Control\StuTime;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\Orm\Transaction\ReopeningEntityManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;

abstract class IntegrationTestCase extends StuTestCase
{
    private const string INTTEST_MIGRATIONS_CONFIG_PATH = 'config/migrations/inttest.php';

    protected static ?TestSession $testSession = null;

    protected static bool $isSchemaInitializationNeeded = true;
    private static ?StuContainer $INTTEST_CONTAINER = null;

    #[\Override]
    public function setUp(): void
    {
        $this->initializeSchemaAndTestdataIfNeeded();
        $this->setupTestSession();
        $this->setupServiceMocks();
    }

    #[\Override]
    public function tearDown(): void
    {
        $dic = $this->getContainer();
        $dic->get(GameControllerInterface::class)->resetGameData();
        $dic->get(ComponentRegistrationInterface::class)->resetComponents();
        AchievementManager::reset();
    }

    public static function tearDownAfterClass(): void
    {
        StuMocks::get()->reset();
    }

    private function setupTestSession(): void
    {
        $dic = $this->getContainer();

        if (self::$testSession === null) {
            self::$testSession = new TestSession($dic->get(UserRepositoryInterface::class));
        }
        $dic->setAdditionalService(SessionInterface::class, self::$testSession);
        self::$testSession->getUser()?->setSessiondata('');
    }

    private function setupServiceMocks(): IntegrationTestCase
    {
        $dic = $this->getContainer();

        $sessionStringFactoryMock = $this->mock(SessionStringFactoryInterface::class);
        $sessionStringFactoryMock->shouldReceive('createSessionString')
            ->zeroOrMoreTimes()
            ->andReturn('MOCKED_SESSIONSTRING');

        $stuRandomMock = $this->mock(StuRandom::class);
        $stuRandomMock->shouldReceive('rand')
            ->zeroOrMoreTimes()
            ->andReturn(1);
        $stuRandomMock->shouldReceive('randomKeyOfProbabilities')
            ->zeroOrMoreTimes()
            ->andReturn(0);
        $stuRandomMock->shouldReceive('uniqid')
            ->zeroOrMoreTimes()
            ->andReturn('MOCKED_UNIQUEID');
        $stuRandomMock->shouldReceive('array_rand')
            ->zeroOrMoreTimes()
            ->andReturnUsing(function (array $array) {
                $keys = array_keys($array);
                return $keys[0];
            });

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

    private function initializeSchemaAndTestdataIfNeeded(): void
    {
        if (self::$isSchemaInitializationNeeded) {

            /** @var ReopeningEntityManager */
            $em = $this->getContainer()->get(EntityManagerInterface::class);
            $em->reset();

            $this->dropSchema();
            $this->initializeSchemaAndTestData();

            self::$isSchemaInitializationNeeded = false;
        }
    }

    private function dropSchema(): void
    {
        $this->runCommand(DropCommand::class, "orm:schema-tool:drop --force --full-database --quiet");
    }

    private function initializeSchemaAndTestData(): IntegrationTestCase
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

    protected function runCommand(string $commandClass, string $input): void
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
            throw new TestExecutionException(sprintf('Could not execute %s!', $input));
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
                throw new TestExecutionException(sprintf('Could not execute %s!', $command));
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
