<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestUserSetting extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds default stu_user_setting.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_user_setting (user_id, setting, value) VALUES (2, \'default_view\', \'maindesk\');
INSERT INTO stu_user_setting (user_id, setting, value) VALUES (2, \'show_pm_read_receipt\', \'1\');
INSERT INTO stu_user_setting (user_id, setting, value) VALUES (2, \'css_color_sheet\', \'schwarz\');
INSERT INTO stu_user_setting (user_id, setting, value) VALUES (1, \'default_view\', \'maindesk\');
INSERT INTO stu_user_setting (user_id, setting, value) VALUES (1, \'show_pm_read_receipt\', \'1\');
INSERT INTO stu_user_setting (user_id, setting, value) VALUES (1, \'css_color_sheet\', \'schwarz\');
INSERT INTO stu_user_setting (user_id, setting, value) VALUES (3, \'default_view\', \'maindesk\');
INSERT INTO stu_user_setting (user_id, setting, value) VALUES (3, \'show_pm_read_receipt\', \'1\');
INSERT INTO stu_user_setting (user_id, setting, value) VALUES (3, \'css_color_sheet\', \'schwarz\');
        ');
    }
}
