<?php
/**
 * This is the plugin pre-hook. It is run before the request is run, after everything is loaded.
 */

$Stats=new StatsPlugin();
$Stats->Insert();
