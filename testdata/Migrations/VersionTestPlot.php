<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestPlot extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds a kn plot.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_plots (id, user_id,title,description,start_date,end_date)
                VALUES (9, 101,\'The Plot Title\',\'The Plot description\',1732214228,NULL);');
    }
}
