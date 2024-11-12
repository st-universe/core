<?php

declare(strict_types=1);

namespace Stu\Component\Admin\Reset;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;

final class SequenceReset implements SequenceResetInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    public function resetSequences(): int
    {
        $connection = $this->entityManager->getConnection();

        $result = $connection->executeQuery(
            "SELECT  'SELECT SETVAL(' ||quote_literal(S.relname)|| ',
                        (SELECT COALESCE(MAX(' ||quote_ident(C.attname)|| '), 1)
                        FROM ' ||quote_ident(T.relname)  || '));'
            FROM pg_class S
            JOIN pg_depend D
                ON S.oid = D.objid
            JOIN pg_class T
                ON D.refobjid = T.oid
            JOIN pg_attribute C
                ON D.refobjid = C.attrelid
                AND D.refobjsubid = C.attnum
            WHERE S.relkind = 'S'
            ORDER BY S.relname"
        );

        $count = 0;

        while ($query = $result->fetchOne()) {
            $connection->executeQuery(
                $query
            );

            $count++;
        }

        $connection->executeQuery(
            sprintf(
                "SELECT SETVAL('stu_user_id_seq', %d)",
                UserEnum::USER_FIRST_ID
            )
        );

        return $count;
    }
}
