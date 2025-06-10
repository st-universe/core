<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250610063612_SpacecraftCondition extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Extracted spacecraft condition fields from spacecraft entity.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_spacecraft_condition (spacecraft_id INT NOT NULL, hull INT NOT NULL, shield INT NOT NULL, is_disabled BOOLEAN NOT NULL, state SMALLINT NOT NULL, PRIMARY KEY(spacecraft_id))
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO stu_spacecraft_condition 
            (spacecraft_id, hull, shield, is_disabled, state)
            SELECT sp.id, sp.huelle, sp.schilde, sp.disabled, sp.state
            FROM stu_spacecraft sp
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_spacecraft_condition ADD CONSTRAINT FK_5BAFF7731C6AF6FD FOREIGN KEY (spacecraft_id) REFERENCES stu_spacecraft (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_spacecraft DROP huelle
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_spacecraft DROP schilde
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_spacecraft DROP disabled
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_spacecraft DROP state
        SQL);
    }

    public function down(Schema $schema): void
    {
        // create columns
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_spacecraft ADD huelle INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_spacecraft ADD schilde INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_spacecraft ADD disabled BOOLEAN DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_spacecraft ADD state SMALLINT DEFAULT NULL
        SQL);


        // fill data
        $this->addSql(<<<'SQL'
            UPDATE stu_spacecraft sp
            SET huelle = sc.hull, schilde = sc.shield, disabled = sc.is_disabled, state = sc.state
            FROM stu_spacecraft_condition sc
            WHERE sc.spacecraft_id = sp.id
        SQL);

        // set not null
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_spacecraft ALTER huelle SET NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_spacecraft ALTER schilde SET NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_spacecraft ALTER disabled SET NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_spacecraft ALTER state SET NOT NULL
        SQL);

        // drop condition table
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_spacecraft_condition DROP CONSTRAINT FK_5BAFF7731C6AF6FD
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_spacecraft_condition
        SQL);
    }
}
