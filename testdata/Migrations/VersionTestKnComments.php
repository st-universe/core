<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestKnComments extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds a kn post comment.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_kn_comments (id, post_id,user_id,username,"text","date",deleted)
        VALUES (43, 42, 101, \'\', \'This is a KN post comment.\',1732626985, NULL);
        ');
    }
}
