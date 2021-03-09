<?php
declare(strict_types=1);

namespace App;

require_once('./Model/GameModel.php');
$config = require_once('./config.php');

session_start();

$gameModel = new GameModel($config);

if (isset($_POST['dataRow'])) {
    $gameModel->insertMoveData();
}

if (isset($_GET['data'])) {
    if ($_GET['data'] === 'initData')
        $gameModel->initGame();
    else if ($_GET['data'] === 'getData')
        $gameModel->listenerAjax();
    else if ($_GET['data'] === 'checkWin')
        $gameModel->checkWin();
}