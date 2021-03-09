<?php

declare(strict_types=1);

namespace App;

require_once("src/View.php");
require_once("src/Request.php");
require_once("src/Model/UserModel.php");

class UserController {
    private View $view;
    private Request $request;
    private UserModel $db;

    public function __construct(Request $request, array $conf) {
        session_start();

        $this->request = $request;
        $this->view = new View();
        $this->db = new UserModel($conf);
    }

    public function login(): void {
        if (isset($_SESSION['id'])) {
            header('Location: ./?action=listRooms');
            exit;
        } else if ($this->request->postParam('user-name')) {
            $userName = $this->request->postParam('user-name');
            $id = $this->db->setUserId($userName);

            if ($id) {
                $_SESSION['id'] = $id;
                $_SESSION['user-name'] = $userName;
            } else
                exit;

            header('Location: ./?action=listRooms');
            exit;
        }

        $this->view->render('login');
    }

    public function rooms(): void {
        if (isset($_SESSION['game-id'])) {
            header("Location: ./?action=game&id={$_SESSION['game-id']}");
            exit;
        }

        $listRooms = $this->db->showRooms();
        $this->view->render('rooms', $listRooms);
    }

    public function createRoom(): void {
        if ($this->request->postParam('room-name')) {
            $roomName = $this->request->postParam('room-name');
            $roomPass = $this->request->postParam('room-pass');
            
            $idGame = $this->db->createRoom($roomName, $roomPass);
            if ($idGame) {
                $_SESSION['game-id'] = $idGame;
                header("Location: ./?action=game&id=$idGame");
                exit;
            }
        }
    }

    public function leaveGame(): void {
        if ($this->request->postParam('leave-game')) {
            $idGame = (int)$_SESSION['game-id'];
            $this->db->gameLeave($idGame);

            unset($_SESSION['game-id']);
            header('Location: ./?action=listRooms');
            exit;
        }
    }

    public function game(): void {
        // idGame z URL
        $idGameUrl = (int)$this->request->getParam('id');
        
        // obsługa hasła
        if ($this->request->postParam('game-pass'))
            $this->checkPassword($idGameUrl);
        
        // idGame z sesji
        $idGame = isset($_SESSION['game-id']) ? (int)$_SESSION['game-id'] : null;
        
        if ($idGameUrl) {
            $gameStatus = $this->db->gameStatus($idGameUrl);
            
            if ($gameStatus) {
                $roomPass = $gameStatus['password'];

                if ($idGame === $idGameUrl) {
                    $this->view->render('game');
                    exit;
                } else if ($roomPass) {
                    $this->view->render('gamePass');
                    exit;
                } else {
                    $_SESSION['game-id'] = $idGameUrl;
                    $this->db->gameJoin($idGameUrl);
                    $this->view->render('game');
                    exit;
                }
            } else {
                unset($_SESSION['game-id']);
                header('Location: ./?action=listRooms');
                exit;
            }
        } else {
            header('Location: ./?action=listRooms');
            exit;
        }
    }

    public function run(): void {
        $action = $this->request->getParam('action');
        
        switch($action) {
            case 'createRoom':
                $this->createRoom();
                break;
            case 'game':
                $this->game();
                break;
            case 'leaveGame':
                $this->leaveGame();
                break;
            case 'listRooms':
                $this->rooms();
                break;
            default:
                $this->login();
                break;
        }
    }

    private function checkPassword(int $idGame): void {
        $pass = $this->request->postParam('game-pass');
        $passDB = $this->db->checkPassword($idGame);
        
        if (password_verify($pass, $passDB)) {
            $_SESSION['game-id'] = $idGame;
            $this->db->gameJoin($idGame);
        } else {
            $_SESSION['bad-pass'] = 1;
        }
    }
}