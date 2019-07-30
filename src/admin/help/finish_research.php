<?php

include_once(__DIR__.'/../../inc/config.inc.php');

/* $Id:$ */
session_start();

$user = new User($_SESSION['uid']);
$research = $user->getCurrentResearch();

$research->finish();
$research->save();

echo "OK";
?>
