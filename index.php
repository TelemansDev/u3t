<?php
declare(strict_types=1);

namespace App;

require_once('src/Controller/UserController.php');
require_once('src/Request.php');

$config = require_once('src/config.php');
$request = new Request($_GET, $_POST);

$userController = new UserController($request, $config);
$userController->run();
    

/*
    session_start();
    require_once('./src/config.php');
    require_once('./src/Database.php');

    if (!isset($_SESSION['id'])) {
        $db = new Database($config);
        $_SESSION['id'] = $db->setId();
    } else 
        //echo $_SESSION['id'];
*/
?>