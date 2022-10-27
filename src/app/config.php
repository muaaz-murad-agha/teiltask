<?php

declare(strict_types=1);

namespace App;

$host   = $_ENV['DB_HOST'];
$dbname = $_ENV['DB_DATABASE'];
$charset = 'utf8';
$user   = $_ENV['DB_USER'];
$pass   = $_ENV['DB_PASS'];

try {
    $db = new \PDO("mysql:host=$host;dbname=$dbname;charset=$charset", $user, $pass, [
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ
    ]);
} catch (\PDOException $e) {
    print 'Error!: ' . $e->getMessage() .  '<br/>';
    die();
}
