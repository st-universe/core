<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestKnArchiv extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds kn archive posts for testing.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_kn_archiv (id, version, former_id, titel, "text", "date", username, user_id, lastedit, plot_id, ratings, del_user_id)
            VALUES 
            (1, \'v1.0\', 1, \'Archiv Test Titel 1\', \'Dies ist ein archivierter Post im Kommunikationsnetzwerk\', 1695064302, \'TestUser1\', 101, 0, null, \'{"157":1,"186":1}\', null),
            (2, \'v1.0\', 2, \'Archiv Test Titel 2\', \'Ein weiterer archivierter Post\', 1695064400, \'TestUser2\', 102, 1695064500, null, \'{"111":1,"103":-1}\', null),
            (3, \'v2.0\', 3, \'Version 2 Post\', \'Ein Post aus Version 2.0\', 1695070000, \'TestUser3\', 103, 0, null, \'{}\', null);
            ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM stu_kn_archiv WHERE id IN (1, 2, 3);');
    }
}
