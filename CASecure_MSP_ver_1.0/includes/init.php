<?php
include_once dirname(__DIR__, 1)."/database/sessions/PersistentSessionHandler.php";
use database\sessions\PersistentSessionHandler;

require_once __DIR__ . '/Psr4AutoloaderClass.php';
require_once __DIR__ . '/db_connect.php';

$loader = new Psr4AutoloaderClass();
$loader->register();
$loader->addNamespace('Parsclick', __DIR__ . '/../../Parsclick');

$handler = new PersistentSessionHandler($db);
session_set_save_handler($handler);
session_start();
$_SESSION['active'] = time();