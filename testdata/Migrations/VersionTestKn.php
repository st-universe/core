<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestKn extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds a kn post.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_kn (id, titel,"text","date",username,user_id,lastedit,plot_id,ratings,del_user_id)
            VALUES (42, \'Dies ist der KN Titel\',\'Dies ist ein Post im Kommunikationsnetzwerk\',1695064302,\'Username\',101,0,9,\'{"157":1,"186":1,"136":1,"111":1,"103":1,"102":1,"135":1,"123":1,"142":1,"130":1,"127":1,"114":1,"178":1,"152":1}\',149);
            ');
    }
}
