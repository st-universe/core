<?php
@session_start();
require_once 'inc/config.inc.php';

DB()->beginTransaction();
$app = new colonyapp;
DB()->commitTransaction();
