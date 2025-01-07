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
        $this->addSql('INSERT INTO stu_user_setting (user_id, setting, value)
                VALUES (2, \'default_view\', \'maindesk\'),
                       (2, \'show_pm_read_receipt\', \'1\'),
                       (2, \'css_color_sheet\', \'schwarz\'),
                       (1, \'default_view\', \'maindesk\'),
                       (1, \'show_pm_read_receipt\', \'1\'),
                       (1, \'css_color_sheet\', \'schwarz\'),
                       (3, \'default_view\', \'maindesk\'),
                       (3, \'show_pm_read_receipt\', \'1\'),
                       (3, \'css_color_sheet\', \'schwarz\'),
                       (102,\'inbox_messenger_style\',\'1\'),
                       (14,\'show_pm_read_receipt\',\'1\'),
                       (102,\'show_pm_read_receipt\',\'1\');
        ');
    }
}
