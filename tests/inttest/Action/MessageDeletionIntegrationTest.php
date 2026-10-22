<?php

declare(strict_types=1);

namespace Stu\Action;

use PHPUnit\Framework\Attributes\DataProvider;
use Stu\CommunicationMutationIntegrationTestCase;

final class MessageDeletionIntegrationTest extends CommunicationMutationIntegrationTestCase
{
    public static function actions(): iterable
    {
        yield 'DeletePms' => ['Stu\\Module\\Message\\Action\\DeletePms\\DeletePms'];
        yield 'DeletePmCategory' => ['Stu\\Module\\Message\\Action\\DeletePmCategory\\DeletePmCategory'];
    }

    #[DataProvider('actions')]
    public function testForgedRequestsLeaveDatabaseUnchangedAndAuthorizedPostWorks(string $class): void
    {
        $this->assertForgedRequestsLeaveDatabaseUnchangedAndAuthorizedPostWorks($class);
    }

    public function testOwnTokenCannotChangeForeignObjects(): void
    {
        $this->assertOwnTokenCannotChangeForeignObjects();
    }

}
