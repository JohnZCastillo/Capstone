<?php

require './vendor/autoload.php';


use Dotenv\Dotenv;
use Ifsnop\Mysqldump\Mysqldump;
use App\Lib\Encryptor;


$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$path = './backup/dump.sql';

$db = $_ENV['DB_NAME'];
$user = $_ENV['DB_USER'];
$pass = $_ENV['DB_PASS'];
$key = $_ENV['ENCRYPTION_KEY'];

$dump = new Mysqldump('mysql:host=localhost;dbname=' . $db, $user, $pass);
$dump->start($path);

Encryptor::encryptDumpFile($path,$key);