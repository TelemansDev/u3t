<?php
declare(strict_types=1);

namespace App;
use PDO;

require_once('src/Model/AbstractModel.php');

class UserModel extends AbstractModel {
    public function setUserId(string $userName): ?int {
        $userName = $this->conn->quote($userName);
        
        $query = "INSERT INTO users (user_name) VALUES ($userName)";
        $result = $this->conn->exec($query);
        
        if ($result)
            return (int)$this->conn->lastInsertId();
        else
            return null;
    }

    public function createRoom(string $roomName, string $roomPass): ?int {
        $roomName = $this->conn->quote($roomName);
        (string)$roomPass = ($roomPass !== '') ? $roomPass = password_hash($roomPass, PASSWORD_DEFAULT) : $roomPass = "";
        
        $query = "INSERT INTO room (name, owner, password) VALUES ($roomName, {$_SESSION['id']}, '$roomPass')";
        $result = $this->conn->exec($query);

        if ($result) {
            $idRoom = (int)$this->conn->lastInsertId();
            return $this->createGame($idRoom);
        }

        return null;
    }

    public function showRooms(): array {
        $query = "SELECT game.id, room.name, room.password, users.user_name FROM `room` 
            INNER JOIN game ON room.id = game.id_room 
            INNER JOIN users ON room.owner = users.id
            WHERE game.id_player2 IS NULL";

        $result = $this->conn->query($query)->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function gameJoin(int $id): void {
        $query = "SELECT id_player2 FROM game WHERE id = $id";
        $result = $this->conn->query($query)->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            if ($result['id_player2'] === null) {
                $query = "UPDATE game SET id_player2 = {$_SESSION['id']} WHERE id = $id";
                $this->conn->exec($query);
            }
        }
    }

    public function gameLeave(int $id): void {
        $query  = "SELECT id_room FROM game WHERE id = $id";
        $result = $this->conn->query($query)->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            $idRoom = $result['id_room']; 

            $query = "DELETE FROM room WHERE id = $idRoom";
            $result = $this->conn->exec($query);

            if ($result) {

                $query = "DELETE FROM game WHERE id = $id";
                $result = $this->conn->exec($query);

                if ($result) {
                    $query = "DELETE FROM board WHERE id_game = $id";
                    $result = $this->conn->exec($query);
                }
            }
        }
    }

    public function gameStatus(int $idGame): ?array {
        $query = "SELECT game.id_player2, room.owner, room.password FROM game
            INNER JOIN room ON game.id_room = room.id WHERE game.id = $idGame";
        $result = $this->conn->query($query)->fetch(PDO::FETCH_ASSOC);

        if ($result) 
            return $result;
        else
            return null;
    }

    public function checkPassword(int $idGame): string {
        $query = "SELECT room.password FROM room
            INNER JOIN game ON room.id = game.id_room
            WHERE game.id = $idGame";
        $result = $this->conn->query($query)->fetch(PDO::FETCH_ASSOC);

        if ($result)
            return $result['password'];
    }

    private function createGame(int $idRoom): ?int {
        $query = "INSERT INTO 
            game (turn, id_player1, id_player2, id_next_player, id_room) 
            VALUES (0, {$_SESSION['id']}, null, {$_SESSION['id']}, $idRoom)";
        $result = $this->conn->exec($query);

        if ($result) 
            return (int)$this->conn->lastInsertId();
        else 
            return null;
    }
}