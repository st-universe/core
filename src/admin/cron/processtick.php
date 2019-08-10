<?php
require_once __DIR__.'/../../inc/config.inc.php';

ProcessTick::finishBuildProcesses();
ProcessTick::finishTerraformingProcesses();
ProcessTick::processShipQueue();
ProcessTick::processShieldRegeneration();
