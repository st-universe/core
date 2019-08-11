<?php

declare(strict_types=1);

class SystemNews extends SystemNewsData
{

    static function getListBy($sql)
    {
        $ret = array();
        $result = DB()->query("SELECT * FROM stu_news " . $sql);
        while ($data = mysqli_fetch_assoc($result)) {
            $ret[] = new SystemNewsData($data);
        }
        return $ret;
    }

}