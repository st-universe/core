<?php
include_once("inc/config.inc.php");

ProcessTick::finishBuildProcesses();
ProcessTick::finishTerraformingProcesses();
ProcessTick::processShipQueue();
ProcessTick::processShieldRegeneration();
