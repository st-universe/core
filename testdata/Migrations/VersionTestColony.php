<?php

declare(strict_types=1);

namespace Stu\Testdata;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class VersionTestColony extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds default stu_colony.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO stu_colony (id, colonies_classes_id, user_id, name, planet_name, mask, database_id, starsystem_map_id, rotation_factor, surface_width)
                VALUES (76754, 701, 1, \'Dünnes Asteroidenfeld Stempor\'\'Arr\', \'Dünnes Asteroidenfeld Stempor\'\'Arr\', NULL, NULL, 204228, 834, 0),
                       (76755, 702, 1, \'Mittleres Asteroidenfeld Stempor\'\'Arr\', \'Mittleres Asteroidenfeld Stempor\'\'Arr\', NULL, NULL, 204229, 567, 0),
                       (76756, 702, 1, \'Mittleres Asteroidenfeld Stempor\'\'Arr\', \'Mittleres Asteroidenfeld Stempor\'\'Arr\',NULL, NULL, 204230, 668, 0),
                       (76757, 702, 1, \'Mittleres Asteroidenfeld Stempor\'\'Arr\', \'Mittleres Asteroidenfeld Stempor\'\'Arr\', NULL, NULL, 204231, 396, 0),
                       (76771, 702, 1, \'Mittleres Asteroidenfeld Stempor\'\'Arr\', \'Mittleres Asteroidenfeld Stempor\'\'Arr\', NULL, NULL, 204292, 508, 0),
                       (76772, 702, 1, \'Mittleres Asteroidenfeld Stempor\'\'Arr\', \'Mittleres Asteroidenfeld Stempor\'\'Arr\', NULL, NULL, 204302, 432, 0),
                       (76773, 703, 1, \'Dichtes Asteroidenfeld Stempor\'\'Arr\', \'Dichtes Asteroidenfeld Stempor\'\'Arr\', NULL, NULL, 204303, 190, 0),
                       (76764, 702, 1, \'Mittleres Asteroidenfeld Stempor\'\'Arr\', \'Mittleres Asteroidenfeld Stempor\'\'Arr\', NULL, NULL, 204270, 486, 0),
                       (76776, 703, 1, \'Dichtes Asteroidenfeld Stempor\'\'Arr\', \'Dichtes Asteroidenfeld Stempor\'\'Arr\', NULL, NULL, 204347, 254, 0),
                       (76763, 405, 1, \'Stempor\'\'Arr 1a\', \'Stempor\'\'Arr 1a\', NULL, NULL, 204253, 114, 0),
                       (76775, 701, 1, \'Dünnes Asteroidenfeld Stempor\'\'Arr\', \'Dünnes Asteroidenfeld Stempor\'\'Arr\', NULL, NULL, 204325, 343, 0),
                       (76774, 703, 1, \'Dichtes Asteroidenfeld Stempor\'\'Arr\', \'Dichtes Asteroidenfeld Stempor\'\'Arr\', NULL, NULL, 204324, 409, 0),
                       (76778, 703, 1, \'Dichtes Asteroidenfeld Stempor\'\'Arr\', \'Dichtes Asteroidenfeld Stempor\'\'Arr\', NULL, NULL, 204369, 387, 0),
                       (76781, 702, 1, \'Mittleres Asteroidenfeld Stempor\'\'Arr\', \'Mittleres Asteroidenfeld Stempor\'\'Arr\', NULL, NULL, 204391, 227, 0),
                       (76783, 703, 1, \'Dichtes Asteroidenfeld Stempor\'\'Arr\', \'Dichtes Asteroidenfeld Stempor\'\'Arr\', NULL, NULL, 204402, 363, 0),
                       (76784, 702, 1, \'Mittleres Asteroidenfeld Stempor\'\'Arr\', \'Mittleres Asteroidenfeld Stempor\'\'Arr\', NULL, NULL, 204412, 440, 0),
                       (76786, 702, 1, \'Mittleres Asteroidenfeld Stempor\'\'Arr\', \'Mittleres Asteroidenfeld Stempor\'\'Arr\', NULL, NULL, 204423, 856, 0),
                       (76787, 701, 1, \'Dünnes Asteroidenfeld Stempor\'\'Arr\', \'Dünnes Asteroidenfeld Stempor\'\'Arr\', NULL, NULL, 204424, 613, 0),
                       (76788, 702, 1, \'Mittleres Asteroidenfeld Stempor\'\'Arr\', \'Mittleres Asteroidenfeld Stempor\'\'Arr\', NULL, NULL, 204434, 221, 0),
                       (76789, 703, 1, \'Dichtes Asteroidenfeld Stempor\'\'Arr\', \'Dichtes Asteroidenfeld Stempor\'\'Arr\', NULL, NULL, 204435, 914, 0),
                       (76790, 702, 1, \'Mittleres Asteroidenfeld Stempor\'\'Arr\', \'Mittleres Asteroidenfeld Stempor\'\'Arr\', NULL, NULL, 204446, 464, 0),
                       (76791, 701, 1, \'Dünnes Asteroidenfeld Stempor\'\'Arr\', \'Dünnes Asteroidenfeld Stempor\'\'Arr\', NULL, NULL, 204447, 239, 0),
                       (76760, 703, 1, \'Dichtes Asteroidenfeld Stempor\'\'Arr\', \'Dichtes Asteroidenfeld Stempor\'\'Arr\', NULL, NULL, 204249, 202, 0),
                       (76761, 702, 1, \'Mittleres Asteroidenfeld Stempor\'\'Arr\', \'Mittleres Asteroidenfeld Stempor\'\'Arr\', NULL, NULL, 204250, 368, 0),
                       (76762, 701, 1, \'Dünnes Asteroidenfeld Stempor\'\'Arr\', \'Dünnes Asteroidenfeld Stempor\'\'Arr\', NULL, NULL, 204251, 538, 0),
                       (76765, 701, 1, \'Dünnes Asteroidenfeld Stempor\'\'Arr\', \'Dünnes Asteroidenfeld Stempor\'\'Arr\', NULL, NULL, 204271, 149, 0),
                       (76768, 703, 1, \'Dichtes Asteroidenfeld Stempor\'\'Arr\', \'Dichtes Asteroidenfeld Stempor\'\'Arr\', NULL, NULL, 204279, 612, 0),
                       (76769, 701, 1, \'Dünnes Asteroidenfeld Stempor\'\'Arr\', \'Dünnes Asteroidenfeld Stempor\'\'Arr\', NULL, NULL, 204280, 329, 0),
                       (76766, 203, 1, \'Stempor\'\'Arr 1\', \'Stempor\'\'Arr 1\', \'YToxMDA6e2k6MDtpOjkwMDtpOjE7aTo5MDA7aToyO2k6OTAwO2k6MztpOjkwMDtpOjQ7aTo5MDA7aTo1O2k6OTAwO2k6NjtpOjkwMDtpOjc7aTo5MDA7aTo4O2k6OTAwO2k6OTtpOjkwMDtpOjEwO2k6OTAwO2k6MTE7aTo5MDA7aToxMjtpOjkwMDtpOjEzO2k6OTAwO2k6MTQ7aTo5MDA7aToxNTtpOjkwMDtpOjE2O2k6OTAwO2k6MTc7aTo5MDA7aToxODtpOjkwMDtpOjE5O2k6OTAwO2k6MjA7aToxMTE7aToyMTtpOjIwMTtpOjIyO2k6MjAxO2k6MjM7aToyMDE7aToyNDtpOjExMTtpOjI1O2k6MTExO2k6MjY7aTo3MDE7aToyNztpOjcwMTtpOjI4O2k6MTExO2k6Mjk7aToxMTE7aTozMDtpOjIwMTtpOjMxO2k6MjAxO2k6MzI7aToyMDE7aTozMztpOjExMTtpOjM0O2k6MTExO2k6MzU7aToxMTE7aTozNjtpOjEwMTtpOjM3O2k6MTExO2k6Mzg7aToxMTE7aTozOTtpOjExMTtpOjQwO2k6MjAxO2k6NDE7aToyMDE7aTo0MjtpOjIwMTtpOjQzO2k6MTAxO2k6NDQ7aToxMjE7aTo0NTtpOjEwMTtpOjQ2O2k6MTAxO2k6NDc7aToxMjE7aTo0ODtpOjcwMTtpOjQ5O2k6NzAxO2k6NTA7aToyMDE7aTo1MTtpOjIwMTtpOjUyO2k6MjAxO2k6NTM7aToxMDE7aTo1NDtpOjEyMTtpOjU1O2k6MTIxO2k6NTY7aToxMjE7aTo1NztpOjcwMTtpOjU4O2k6NzAxO2k6NTk7aTo3MDE7aTo2MDtpOjExMTtpOjYxO2k6MjAxO2k6NjI7aToyMDE7aTo2MztpOjExMTtpOjY0O2k6MTExO2k6NjU7aToxMTE7aTo2NjtpOjExMTtpOjY3O2k6NzAxO2k6Njg7aTo3MDE7aTo2OTtpOjEwMTtpOjcwO2k6MjAxO2k6NzE7aToxMDE7aTo3MjtpOjExMTtpOjczO2k6MTExO2k6NzQ7aToxMDE7aTo3NTtpOjExMTtpOjc2O2k6MTAxO2k6Nzc7aToxMDE7aTo3ODtpOjEwMTtpOjc5O2k6MTAxO2k6ODA7aTo4MDI7aTo4MTtpOjgwMTtpOjgyO2k6ODAxO2k6ODM7aTo4MDE7aTo4NDtpOjgwMjtpOjg1O2k6ODAyO2k6ODY7aTo4MDE7aTo4NztpOjgwMjtpOjg4O2k6ODAyO2k6ODk7aTo4MDI7aTo5MDtpOjgwMjtpOjkxO2k6ODAyO2k6OTI7aTo4MDE7aTo5MztpOjgwMTtpOjk0O2k6ODAyO2k6OTU7aTo4MDI7aTo5NjtpOjgwMjtpOjk3O2k6ODAyO2k6OTg7aTo4MDE7aTo5OTtpOjgwMTt9\', NULL, 204275, 114, 10),
                       (42, 201, 101, \'Stempor\'\'Arr 4\', \'Stempor\'\'Arr 4\', \'YToxMDA6e2k6MDtpOjkwMDtpOjE7aTo5MDA7aToyO2k6OTAwO2k6MztpOjkwMDtpOjQ7aTo5MDA7aTo1O2k6OTAwO2k6NjtpOjkwMDtpOjc7aTo5MDA7aTo4O2k6OTAwO2k6OTtpOjkwMDtpOjEwO2k6OTAwO2k6MTE7aTo5MDA7aToxMjtpOjkwMDtpOjEzO2k6OTAwO2k6MTQ7aTo5MDA7aToxNTtpOjkwMDtpOjE2O2k6OTAwO2k6MTc7aTo5MDA7aToxODtpOjkwMDtpOjE5O2k6OTAwO2k6MjA7aTo3MDE7aToyMTtpOjcwMTtpOjIyO2k6NTAxO2k6MjM7aToyMDE7aToyNDtpOjEwMTtpOjI1O2k6MTEyO2k6MjY7aTo1MDE7aToyNztpOjExMjtpOjI4O2k6MTEyO2k6Mjk7aToxMDE7aTozMDtpOjExMTtpOjMxO2k6NzAxO2k6MzI7aToxMDE7aTozMztpOjEwMTtpOjM0O2k6MTAxO2k6MzU7aToyMDE7aTozNjtpOjIwMTtpOjM3O2k6MTExO2k6Mzg7aTo3MDE7aTozOTtpOjEwMTtpOjQwO2k6MTAxO2k6NDE7aToxMDE7aTo0MjtpOjEwMTtpOjQzO2k6NDAxO2k6NDQ7aToxMDE7aTo0NTtpOjIwMTtpOjQ2O2k6MTExO2k6NDc7aToxMTE7aTo0ODtpOjExMTtpOjQ5O2k6MTExO2k6NTA7aTo3MDE7aTo1MTtpOjQwMTtpOjUyO2k6NDAxO2k6NTM7aToxMDE7aTo1NDtpOjEwMTtpOjU1O2k6MTExO2k6NTY7aToxMTE7aTo1NztpOjIwMTtpOjU4O2k6MjAxO2k6NTk7aToyMDE7aTo2MDtpOjEwMTtpOjYxO2k6MTAxO2k6NjI7aTo3MDE7aTo2MztpOjcwMTtpOjY0O2k6MjAxO2k6NjU7aToyMDE7aTo2NjtpOjIwMTtpOjY3O2k6MjAxO2k6Njg7aToyMDE7aTo2OTtpOjIwMTtpOjcwO2k6NTAxO2k6NzE7aToxMDE7aTo3MjtpOjExMjtpOjczO2k6NTAxO2k6NzQ7aTo1MDE7aTo3NTtpOjIwMTtpOjc2O2k6MjAxO2k6Nzc7aToyMDE7aTo3ODtpOjIwMTtpOjc5O2k6MjAxO2k6ODA7aTo4MDI7aTo4MTtpOjgwMTtpOjgyO2k6ODAxO2k6ODM7aTo4MDE7aTo4NDtpOjg1MTtpOjg1O2k6ODUxO2k6ODY7aTo4NTE7aTo4NztpOjgwMjtpOjg4O2k6ODAyO2k6ODk7aTo4MDE7aTo5MDtpOjgwMjtpOjkxO2k6ODAxO2k6OTI7aTo4MDE7aTo5MztpOjgwMjtpOjk0O2k6ODAxO2k6OTU7aTo4NTE7aTo5NjtpOjg1MTtpOjk3O2k6ODAyO2k6OTg7aTo4MDE7aTo5OTtpOjgwMTt9\', NULL, 204359, 116, 10),
                       (76797, 215, 1, \'Stempor\'\'Arr 5\', \'Stempor\'\'Arr 5\', NULL, NULL, 204585, 67, 10),
                       (76796, 231, 1, \'Stempor\'\'Arr 6\', \'Stempor\'\'Arr 6\', NULL, NULL, 204579, 21, 10),
                       (76780, 405, 1, \'Stempor\'\'Arr 4a\', \'Stempor\'\'Arr 4a\', NULL, NULL, 204381, 131, 7),
                       (76767, 413, 1, \'Stempor\'\'Arr 1b\', \'Stempor\'\'Arr 1b\', NULL, NULL, 204276, 36, 7),
                       (76782, 701, 1, \'Dünnes Asteroidenfeld Stempor\'\'Arr\', \'Dünnes Asteroidenfeld Stempor\'\'Arr\', NULL, NULL, 204401, 408, 6),
                       (76792, 405, 1, \'Stempor\'\'Arr 2a\', \'Stempor\'\'Arr 2a\', NULL, NULL, 204522, 113, 7),
                       (76752, 401, 1, \'Stempor\'\'Arr 3a\', \'Stempor\'\'Arr 3a\', NULL, NULL, 204195, 104, 7),
                       (76770, 223, 1, \'Stempor\'\'Arr 7\', \'Stempor\'\'Arr 7\', NULL, NULL, 204289, 208, 10),
                       (76759, 415, 1, \'Stempor\'\'Arr 7a\', \'Stempor\'\'Arr 7a\', NULL, NULL, 204246, 80, 7),
                       (76793, 413, 1, \'Stempor\'\'Arr 2b\', \'Stempor\'\'Arr 2b\', NULL, NULL, 204542, 54, 7),
                       (76779, 701, 1, \'Dünnes Asteroidenfeld Stempor\'\'Arr\', \'Dünnes Asteroidenfeld Stempor\'\'Arr\', NULL, NULL, 204379, 658, 6),
                       (76795, 431, 1, \'Stempor\'\'Arr 6a\', \'Stempor\'\'Arr 6a\', NULL, NULL, 204558, 31, 7),
                       (76753, 431, 1, \'Stempor\'\'Arr 3b\', \'Stempor\'\'Arr 3b\', NULL, NULL, 204218, 33, 7),
                       (76785, 702, 1, \'Mittleres Asteroidenfeld Stempor\'\'Arr\', \'Mittleres Asteroidenfeld Stempor\'\'Arr\', NULL, NULL, 204413, 521, 6),
                       (76794, 211, 1, \'Stempor\'\'Arr 2\', \'Stempor\'\'Arr 2\', NULL, NULL, 204543, 123, 10),
                       (76758, 221, 1, \'Stempor\'\'Arr 3\', \'Stempor\'\'Arr 3\', NULL, NULL, 204239, 109, 10);
        ');
    }
}
