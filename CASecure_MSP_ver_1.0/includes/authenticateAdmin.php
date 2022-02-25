<?php
include_once dirname(__DIR__, 1)."/database/sessions/AutoLogin.php";

require_once __DIR__ . '/init.php';

use database\sessions\AutoLogin;
//print_r($_SESSION); 
if ((isset($_SESSION['authenticated']) || isset($_SESSION['lynda_auth'])) && $_SESSION['admin'] == 1) {
   // we're OK
} 
else if ($_SESSION['admin'] != 1) {
	header('Location: NotAuthorized.php');
	exit;
}
else {
    $autologin = new AutoLogin($db);
    $autologin->checkCredentials();
    if (!isset($_SESSION['lynda_auth'])) {
        header('Location: Login.php');
        exit;
    }
}