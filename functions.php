<?php

$pdo = ''; // Databasanslutning måste finnas här

define('EV_BASEURL', "https://documentweb.??????.se/api/");
define('EV_WEBURL', "https://kommun.falkenberg.se/media/evolution/");
define('EV_SAVEPATH', "/home/httpd/fbg-intranet/evolution/");


// Starta upp databas PDO

$db_host    = DB_HOST;
$db_db      = DB_NAME;
$db_pw      = DB_PASSWORD;
$db_user    = DB_USER;
$db_charset = 'utf8mb4';

$dsn = "mysql:host=$db_host;dbname=$db_db;charset=$db_charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];
try {
    $pdo = new PDO($dsn, $db_user, $db_pw, $options);
    // unset($connection);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int) $e->getCode());
}


function jsonHeader()
{
	header('Cache-Control: no-cache, no-store, must-revalidate');
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	header('Content-type: application/json');
	header('Access-Control-Allow-Methods:GET,PUT,POST,DELETE');
	header('Access-Control-Allow-Origin: *');
}