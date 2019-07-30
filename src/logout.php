<?php
@session_start();

require_once('inc/config.inc.php');

$tpl = '';

$app = new gameapp($tpl, 'Logout');
$app->logout();
