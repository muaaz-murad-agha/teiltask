<?php

declare(strict_types=1);


require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$root = dirname(__DIR__) . DIRECTORY_SEPARATOR;

define('APP_PATH', $root . 'app' . DIRECTORY_SEPARATOR);
define('VIEWS_PATH', $root . 'views' . DIRECTORY_SEPARATOR);

require_once APP_PATH . 'config.php';

use App\Request;

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $requestForm = new Request( $_POST['published_from_1'],
                                $_POST['published_to_1'],
                                $_POST['published_from_2'],
                                $_POST['published_to_2'],
                                $_POST['published_from_3'],
                                $_POST['published_to_3']);
                            }

                              foreach($requestForm as $mu){
                                echo '<bere>';
                                echo $mu;
                                echo '</bere>';
                              }
?>