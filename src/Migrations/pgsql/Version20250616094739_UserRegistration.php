<?php

declare(strict_types=1);

namespace Stu\Migrations\Pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250616094739_UserRegistration extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Extracted user registration fields from user entity.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE stu_user_registration (login VARCHAR(20) NOT NULL, pass VARCHAR(255) NOT NULL, sms_code VARCHAR(6) DEFAULT NULL, email VARCHAR(200) NOT NULL, mobile VARCHAR(255) DEFAULT NULL, creation INT NOT NULL, delmark SMALLINT NOT NULL, password_token VARCHAR(255) NOT NULL, sms_sended INT DEFAULT 1, user_id INT NOT NULL, PRIMARY KEY(user_id))
        SQL);
        $this->addSql(<<<'SQL'
            INSERT INTO stu_user_registration 
            (user_id, login, pass, email, creation, delmark, sms_code, mobile, password_token, sms_sended)
            SELECT u.id, u.login, u.pass, u.email, u.creation, u.delmark, u.sms_code, u.mobile, u.password_token, u.sms_sended
            FROM stu_user u
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_user_registration ADD CONSTRAINT FK_9C660348A76ED395 FOREIGN KEY (user_id) REFERENCES stu_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_user DROP login
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_user DROP pass
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_user DROP email
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_user DROP creation
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_user DROP delmark
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_user DROP tick
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_user DROP password_token
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_user DROP mobile
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_user DROP sms_code
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_user DROP sms_sended
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE stu_user_referer DROP CONSTRAINT FK_A00722FDA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_user_referer ADD CONSTRAINT FK_A00722FDA76ED395 FOREIGN KEY (user_id) REFERENCES stu_user_registration (user_id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_user ADD login VARCHAR(20) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_user ADD pass VARCHAR(255) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_user ADD email VARCHAR(200) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_user ADD creation INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_user ADD delmark SMALLINT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_user ADD tick SMALLINT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_user ADD password_token VARCHAR(255) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_user ADD mobile VARCHAR(255) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_user ADD sms_code VARCHAR(6) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_user ADD sms_sended INT DEFAULT NULL
        SQL);

        // fill data
        $this->addSql(<<<'SQL'
            UPDATE stu_user u
            SET login = ur.login, pass = ur.pass, email = ur.email, creation = ur.creation,
                delmark = ur.delmark, sms_code = ur.sms_code, mobile = ur.mobile,
                password_token = ur.password_token, sms_sended = ur.sms_sended
            FROM stu_user_registration ur
            WHERE ur.user_id = u.id
        SQL);

        // set not null
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_user ALTER login SET NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_user ALTER pass SET NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_user ALTER email SET NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_user ALTER creation SET NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_user ALTER delmark SET NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_user ALTER password_token SET NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_user ALTER sms_sended SET NOT NULL
        SQL);

        // remove table
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_user_registration DROP CONSTRAINT FK_9C660348A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE stu_user_registration
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE stu_user_referer DROP CONSTRAINT fk_a00722fda76ed395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE stu_user_referer ADD CONSTRAINT fk_a00722fda76ed395 FOREIGN KEY (user_id) REFERENCES stu_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }
}
