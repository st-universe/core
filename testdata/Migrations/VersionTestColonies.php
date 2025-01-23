<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestColonies extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds default stu_colonies.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_colonies (id, colonies_classes_id, user_id, name, planet_name, bev_work, bev_free, bev_max, eps, max_eps, max_storage, mask, database_id, populationlimit, immigrationstate, shields, shield_frequency, torpedo_type, starsystem_map_id, rotation_factor, surface_width)
                VALUES (76754, 701, 1, \'Dünnes Asteroidenfeld Stempor\'\'Arr\', \'Dünnes Asteroidenfeld Stempor\'\'Arr\', 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 1, 0, 0, NULL, 204228, 834, 0),
                       (76755, 702, 1, \'Mittleres Asteroidenfeld Stempor\'\'Arr\', \'Mittleres Asteroidenfeld Stempor\'\'Arr\', 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 1, 0, 0, NULL, 204229, 567, 0),
                       (76756, 702, 1, \'Mittleres Asteroidenfeld Stempor\'\'Arr\', \'Mittleres Asteroidenfeld Stempor\'\'Arr\', 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 1, 0, 0, NULL, 204230, 668, 0),
                       (76757, 702, 1, \'Mittleres Asteroidenfeld Stempor\'\'Arr\', \'Mittleres Asteroidenfeld Stempor\'\'Arr\', 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 1, 0, 0, NULL, 204231, 396, 0),
                       (76771, 702, 1, \'Mittleres Asteroidenfeld Stempor\'\'Arr\', \'Mittleres Asteroidenfeld Stempor\'\'Arr\', 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 1, 0, 0, NULL, 204292, 508, 0),
                       (76772, 702, 1, \'Mittleres Asteroidenfeld Stempor\'\'Arr\', \'Mittleres Asteroidenfeld Stempor\'\'Arr\', 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 1, 0, 0, NULL, 204302, 432, 0),
                       (76773, 703, 1, \'Dichtes Asteroidenfeld Stempor\'\'Arr\', \'Dichtes Asteroidenfeld Stempor\'\'Arr\', 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 1, 0, 0, NULL, 204303, 190, 0),
                       (76764, 702, 1, \'Mittleres Asteroidenfeld Stempor\'\'Arr\', \'Mittleres Asteroidenfeld Stempor\'\'Arr\', 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 1, 0, 0, NULL, 204270, 486, 0),
                       (76776, 703, 1, \'Dichtes Asteroidenfeld Stempor\'\'Arr\', \'Dichtes Asteroidenfeld Stempor\'\'Arr\', 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 1, 0, 0, NULL, 204347, 254, 0),
                       (76763, 405, 1, \'Stempor\'\'Arr 1a\', \'Stempor\'\'Arr 1a\', 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 1, 0, 0, NULL, 204253, 114, 0),
                       (76775, 701, 1, \'Dünnes Asteroidenfeld Stempor\'\'Arr\', \'Dünnes Asteroidenfeld Stempor\'\'Arr\', 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 1, 0, 0, NULL, 204325, 343, 0),
                       (76774, 703, 1, \'Dichtes Asteroidenfeld Stempor\'\'Arr\', \'Dichtes Asteroidenfeld Stempor\'\'Arr\', 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 1, 0, 0, NULL, 204324, 409, 0),
                       (76778, 703, 1, \'Dichtes Asteroidenfeld Stempor\'\'Arr\', \'Dichtes Asteroidenfeld Stempor\'\'Arr\', 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 1, 0, 0, NULL, 204369, 387, 0),
                       (76781, 702, 1, \'Mittleres Asteroidenfeld Stempor\'\'Arr\', \'Mittleres Asteroidenfeld Stempor\'\'Arr\', 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 1, 0, 0, NULL, 204391, 227, 0),
                       (76783, 703, 1, \'Dichtes Asteroidenfeld Stempor\'\'Arr\', \'Dichtes Asteroidenfeld Stempor\'\'Arr\', 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 1, 0, 0, NULL, 204402, 363, 0),
                       (76784, 702, 1, \'Mittleres Asteroidenfeld Stempor\'\'Arr\', \'Mittleres Asteroidenfeld Stempor\'\'Arr\', 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 1, 0, 0, NULL, 204412, 440, 0),
                       (76786, 702, 1, \'Mittleres Asteroidenfeld Stempor\'\'Arr\', \'Mittleres Asteroidenfeld Stempor\'\'Arr\', 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 1, 0, 0, NULL, 204423, 856, 0),
                       (76787, 701, 1, \'Dünnes Asteroidenfeld Stempor\'\'Arr\', \'Dünnes Asteroidenfeld Stempor\'\'Arr\', 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 1, 0, 0, NULL, 204424, 613, 0),
                       (76788, 702, 1, \'Mittleres Asteroidenfeld Stempor\'\'Arr\', \'Mittleres Asteroidenfeld Stempor\'\'Arr\', 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 1, 0, 0, NULL, 204434, 221, 0),
                       (76789, 703, 1, \'Dichtes Asteroidenfeld Stempor\'\'Arr\', \'Dichtes Asteroidenfeld Stempor\'\'Arr\', 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 1, 0, 0, NULL, 204435, 914, 0),
                       (76790, 702, 1, \'Mittleres Asteroidenfeld Stempor\'\'Arr\', \'Mittleres Asteroidenfeld Stempor\'\'Arr\', 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 1, 0, 0, NULL, 204446, 464, 0),
                       (76791, 701, 1, \'Dünnes Asteroidenfeld Stempor\'\'Arr\', \'Dünnes Asteroidenfeld Stempor\'\'Arr\', 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 1, 0, 0, NULL, 204447, 239, 0),
                       (76760, 703, 1, \'Dichtes Asteroidenfeld Stempor\'\'Arr\', \'Dichtes Asteroidenfeld Stempor\'\'Arr\', 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 1, 0, 0, NULL, 204249, 202, 0),
                       (76761, 702, 1, \'Mittleres Asteroidenfeld Stempor\'\'Arr\', \'Mittleres Asteroidenfeld Stempor\'\'Arr\', 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 1, 0, 0, NULL, 204250, 368, 0),
                       (76762, 701, 1, \'Dünnes Asteroidenfeld Stempor\'\'Arr\', \'Dünnes Asteroidenfeld Stempor\'\'Arr\', 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 1, 0, 0, NULL, 204251, 538, 0),
                       (76765, 701, 1, \'Dünnes Asteroidenfeld Stempor\'\'Arr\', \'Dünnes Asteroidenfeld Stempor\'\'Arr\', 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 1, 0, 0, NULL, 204271, 149, 0),
                       (76768, 703, 1, \'Dichtes Asteroidenfeld Stempor\'\'Arr\', \'Dichtes Asteroidenfeld Stempor\'\'Arr\', 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 1, 0, 0, NULL, 204279, 612, 0),
                       (76769, 701, 1, \'Dünnes Asteroidenfeld Stempor\'\'Arr\', \'Dünnes Asteroidenfeld Stempor\'\'Arr\', 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 1, 0, 0, NULL, 204280, 329, 0),
                       (76766, 203, 1, \'Stempor\'\'Arr 1\', \'Stempor\'\'Arr 1\', 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 1, 0, 0, NULL, 204275, 114, 10),
                       (42, 201, 101, \'Stempor\'\'Arr 4\', \'Stempor\'\'Arr 4\', 0, 84, 84, 96, 96, 1000, \'YToxMDA6e2k6MDtpOjkwMDtpOjE7aTo5MDA7aToyO2k6OTAwO2k6MztpOjkwMDtpOjQ7aTo5MDA7aTo1O2k6OTAwO2k6NjtpOjkwMDtpOjc7aTo5MDA7aTo4O2k6OTAwO2k6OTtpOjkwMDtpOjEwO2k6OTAwO2k6MTE7aTo5MDA7aToxMjtpOjkwMDtpOjEzO2k6OTAwO2k6MTQ7aTo5MDA7aToxNTtpOjkwMDtpOjE2O2k6OTAwO2k6MTc7aTo5MDA7aToxODtpOjkwMDtpOjE5O2k6OTAwO2k6MjA7aTo3MDE7aToyMTtpOjcwMTtpOjIyO2k6NTAxO2k6MjM7aToyMDE7aToyNDtpOjEwMTtpOjI1O2k6MTEyO2k6MjY7aTo1MDE7aToyNztpOjExMjtpOjI4O2k6MTEyO2k6Mjk7aToxMDE7aTozMDtpOjExMTtpOjMxO2k6NzAxO2k6MzI7aToxMDE7aTozMztpOjEwMTtpOjM0O2k6MTAxO2k6MzU7aToyMDE7aTozNjtpOjIwMTtpOjM3O2k6MTExO2k6Mzg7aTo3MDE7aTozOTtpOjEwMTtpOjQwO2k6MTAxO2k6NDE7aToxMDE7aTo0MjtpOjEwMTtpOjQzO2k6NDAxO2k6NDQ7aToxMDE7aTo0NTtpOjIwMTtpOjQ2O2k6MTExO2k6NDc7aToxMTE7aTo0ODtpOjExMTtpOjQ5O2k6MTExO2k6NTA7aTo3MDE7aTo1MTtpOjQwMTtpOjUyO2k6NDAxO2k6NTM7aToxMDE7aTo1NDtpOjEwMTtpOjU1O2k6MTExO2k6NTY7aToxMTE7aTo1NztpOjIwMTtpOjU4O2k6MjAxO2k6NTk7aToyMDE7aTo2MDtpOjEwMTtpOjYxO2k6MTAxO2k6NjI7aTo3MDE7aTo2MztpOjcwMTtpOjY0O2k6MjAxO2k6NjU7aToyMDE7aTo2NjtpOjIwMTtpOjY3O2k6MjAxO2k6Njg7aToyMDE7aTo2OTtpOjIwMTtpOjcwO2k6NTAxO2k6NzE7aToxMDE7aTo3MjtpOjExMjtpOjczO2k6NTAxO2k6NzQ7aTo1MDE7aTo3NTtpOjIwMTtpOjc2O2k6MjAxO2k6Nzc7aToyMDE7aTo3ODtpOjIwMTtpOjc5O2k6MjAxO2k6ODA7aTo4MDI7aTo4MTtpOjgwMTtpOjgyO2k6ODAxO2k6ODM7aTo4MDE7aTo4NDtpOjg1MTtpOjg1O2k6ODUxO2k6ODY7aTo4NTE7aTo4NztpOjgwMjtpOjg4O2k6ODAyO2k6ODk7aTo4MDE7aTo5MDtpOjgwMjtpOjkxO2k6ODAxO2k6OTI7aTo4MDE7aTo5MztpOjgwMjtpOjk0O2k6ODAxO2k6OTU7aTo4NTE7aTo5NjtpOjg1MTtpOjk3O2k6ODAyO2k6OTg7aTo4MDE7aTo5OTtpOjgwMTt9\', NULL, 0, 1, 0, 0, NULL, 204359, 116, 10),
                       (76797, 215, 1, \'Stempor\'\'Arr 5\', \'Stempor\'\'Arr 5\', 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 1, 0, 0, NULL, 204585, 67, 10),
                       (76796, 231, 1, \'Stempor\'\'Arr 6\', \'Stempor\'\'Arr 6\', 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 1, 0, 0, NULL, 204579, 21, 10),
                       (76780, 405, 1, \'Stempor\'\'Arr 4a\', \'Stempor\'\'Arr 4a\', 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 1, 0, 0, NULL, 204381, 131, 7),
                       (76767, 413, 1, \'Stempor\'\'Arr 1b\', \'Stempor\'\'Arr 1b\', 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 1, 0, 0, NULL, 204276, 36, 7),
                       (76782, 701, 1, \'Dünnes Asteroidenfeld Stempor\'\'Arr\', \'Dünnes Asteroidenfeld Stempor\'\'Arr\', 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 1, 0, 0, NULL, 204401, 408, 6),
                       (76792, 405, 1, \'Stempor\'\'Arr 2a\', \'Stempor\'\'Arr 2a\', 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 1, 0, 0, NULL, 204522, 113, 7),
                       (76752, 401, 1, \'Stempor\'\'Arr 3a\', \'Stempor\'\'Arr 3a\', 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 1, 0, 0, NULL, 204195, 104, 7),
                       (76770, 223, 1, \'Stempor\'\'Arr 7\', \'Stempor\'\'Arr 7\', 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 1, 0, 0, NULL, 204289, 208, 10),
                       (76759, 415, 1, \'Stempor\'\'Arr 7a\', \'Stempor\'\'Arr 7a\', 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 1, 0, 0, NULL, 204246, 80, 7),
                       (76793, 413, 1, \'Stempor\'\'Arr 2b\', \'Stempor\'\'Arr 2b\', 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 1, 0, 0, NULL, 204542, 54, 7),
                       (76779, 701, 1, \'Dünnes Asteroidenfeld Stempor\'\'Arr\', \'Dünnes Asteroidenfeld Stempor\'\'Arr\', 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 1, 0, 0, NULL, 204379, 658, 6),
                       (76795, 431, 1, \'Stempor\'\'Arr 6a\', \'Stempor\'\'Arr 6a\', 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 1, 0, 0, NULL, 204558, 31, 7),
                       (76753, 431, 1, \'Stempor\'\'Arr 3b\', \'Stempor\'\'Arr 3b\', 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 1, 0, 0, NULL, 204218, 33, 7),
                       (76785, 702, 1, \'Mittleres Asteroidenfeld Stempor\'\'Arr\', \'Mittleres Asteroidenfeld Stempor\'\'Arr\', 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 1, 0, 0, NULL, 204413, 521, 6),
                       (76794, 211, 1, \'Stempor\'\'Arr 2\', \'Stempor\'\'Arr 2\', 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 1, 0, 0, NULL, 204543, 123, 10),
                       (76758, 221, 1, \'Stempor\'\'Arr 3\', \'Stempor\'\'Arr 3\', 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 1, 0, 0, NULL, 204239, 109, 10);
        ');
    }
}
