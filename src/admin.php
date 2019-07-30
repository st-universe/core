<?php

@session_start();
require_once 'inc/config.inc.php';

DB()->beginTransaction();
new AdminApp;
DB()->commitTransaction();

?>
