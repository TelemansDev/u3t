<?php
declare(strict_types=1);

namespace App;
use PDO;

require('AbstractModel.php');

class GameModel extends AbstractModel {
    private array $json = [];

    public function insertMoveData(): void {
        $query = "SELECT turn, id_player1, id_player2, id_next_player FROM game WHERE id = {$_SESSION['game-id']}";
        $result = $this->conn->query($query)->fetch(PDO::FETCH_ASSOC);
        
        $turn = $result['turn'];
        $idPlayer1 = $result['id_player1'];
        $idPlayer2 = $result['id_player2'];
        $idNextPlayer = $result['id_next_player'];
       
        $newIdNextPlayer = null;
        $value = null;

        // Sprawdzenie możliwości ruchu
        if ($idNextPlayer == $_SESSION['id']) {

            // Sprawdzenie którą wartość wstawić do bazy
            if ($idPlayer1 == $_SESSION['id']) {
                $value = 'X';
                $newIdNextPlayer = $idPlayer2;
            }
            else {
                $value = 'O';
                $newIdNextPlayer = $idPlayer1;
            }

            $box = htmlentities($_POST['dataBox']);
            $x = htmlentities($_POST['dataCol']);
            $y = htmlentities($_POST['dataRow']);
            $date = date("Y-m-d H:i:s");

            $query = "INSERT INTO board (id_game, box, x, y, value, date) VALUES ({$_SESSION['game-id']}, $box, $x, $y, '$value', '$date')";
            $result = $this->conn->exec($query);
            echo $query;

            if ($result) {
                $query = "UPDATE game SET turn = $turn + 1, id_next_player = $newIdNextPlayer WHERE id = {$_SESSION['game-id']}";
                $this->conn->exec($query);
            }
        }
    }

    public function listenerAjax(): void {
        $query = "SELECT turn, id_player1, id_player2, id_next_player FROM game
            WHERE id = {$_SESSION['game-id']}";
        $result = $this->conn->query($query)->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $turn = $result['turn'];
            $idPlayer1 = $result['id_player1'];
            $idPlayer2 = $result['id_player2'];
            $idNextPlayer = $result['id_next_player'];

            if ($idPlayer1 !== NULL && $idPlayer2 !== NULL) {
                if ($_SESSION['id'] == $idNextPlayer)
                    $move = 1;
                else
                    $move = 0;

                // pobranie planszy
                $query = "SELECT box, x, y, value FROM board WHERE id_game = {$_SESSION['game-id']}
                ORDER BY date DESC LIMIT 1";
                $result = $this->conn->query($query)->fetch(PDO::FETCH_ASSOC);

                // przygotowanie json'a
                $this->json = [
                    'board' => $result,
                    'turn' => $turn,
                    'move' => $move
                ];
            } else {
                $move = 0;
                $this->json['move'] = $move;
            }

            echo json_encode($this->json);
        }
    }

    public function initGame(): void {
        // pobranie planszy
        $query = "SELECT box, x, y, value FROM board WHERE id_game = {$_SESSION['game-id']}";
        $result = $this->conn->query($query)->fetchAll(PDO::FETCH_ASSOC);
        
        $this->json['board'] = $result;

        if ($result) {
            // pobranie ostatniego ruchu
            $query = "SELECT box, x, y, value FROM board WHERE id_game = {$_SESSION['game-id']}
                ORDER BY date DESC LIMIT 1";
            $result = $this->conn->query($query)->fetch(PDO::FETCH_ASSOC);

            $this->json['lastMove'] = $result;
        } else 
            $this->json['lastMove'] = null;

        // sprawdzenie wygranej dla poszczególnych single-board
        $query = "SELECT MAX(value) as max, MIN(value) as min, COUNT(value) as count, box FROM board 
            INNER JOIN win_conditions ON (board.x = win_conditions.x) AND (board.y = win_conditions.y)
            WHERE board.id_game = {$_SESSION['game-id']}
            GROUP BY variant, box
            ORDER BY count DESC";
        
        $result = $this->conn->query($query)->fetchAll(PDO::FETCH_ASSOC);
        
        $singleBoardWin = [];

        foreach ($result as $winCondition) {
            if ($winCondition['count'] == 3 && $winCondition['max'] === $winCondition['min']) {
                $temp = [
                    'box' => $winCondition['box'], 
                    'value' => $winCondition['max']
                ];
                array_push($singleBoardWin, $temp);
            } else if ($winCondition['count'] < 3)
                break;
        }

        $this->json['singleBoardWin'] = $singleBoardWin;

        // sprawdzanie czy wszystcy gracze dołączyli
        $query = "SELECT turn, id_player1, id_player2, id_next_player FROM game
            WHERE id = {$_SESSION['game-id']}";
        $result = $this->conn->query($query)->fetch(PDO::FETCH_ASSOC);

        $move = 0;
        $turn = 0;
        
        if ($result) {
            $turn = $result['turn'];
            $idPlayer1 = $result['id_player1'];
            $idPlayer2 = $result['id_player2'];
            $idNextPlayer = $result['id_next_player'];

            if ($idPlayer1 !== NULL && $idPlayer2 !== NULL) {
                if ($_SESSION['id'] == $idNextPlayer)
                    $move = 1;
            }
        }

        $this->json['move'] = $move;
        $this->json['turn'] = $turn;

        echo json_encode($this->json);
    }

    public function checkWin(): void {
        $WinningCombinations = [
            [0, 1, 2], [3, 4, 5], [6, 7, 8], [0, 3, 6], [1, 4, 7], [2, 5, 8], [0, 4, 8], [2, 4, 6]
        ];

        $query = "SELECT MAX(value) as max, MIN(value) as min, COUNT(value) as count, box FROM board 
            INNER JOIN win_conditions ON (board.x = win_conditions.x) AND (board.y = win_conditions.y)
            WHERE board.id_game = {$_SESSION['game-id']}
            GROUP BY variant, box
            ORDER BY count DESC";
        
        $result = $this->conn->query($query)->fetchAll(PDO::FETCH_ASSOC);

        $player1Board = [];
        $player2Board = [];
        $drawBoard = [0, 0, 0, 0, 0, 0, 0, 0, 0];

        foreach ($result as $winCondition) {
            if ($winCondition['count'] == 3 && $winCondition['max'] === $winCondition['min']) {
                if ($winCondition['max'] === 'X')
                    array_push($player1Board, $winCondition['box']);
                else
                    array_push($player2Board, $winCondition['box']);
            } 
            else if ($winCondition['count'] == 3) {
                $drawBoard[$winCondition['box']] += 1;
            }
            else {
                break;
            }
        }

        // sprawdzenie ilości uzupełnionych bordów
        $completeBoardCount = count($player1Board) + count($player2Board);
        foreach ($drawBoard as $element) {
            if ($element == 8)
                $completeBoardCount++;
        }

        // sprawdzanie wygranej
        if (count($player1Board) > 2 || count($player2Board) > 2) {
            $query = "SELECT id_next_player FROM game WHERE id = {$_SESSION['game-id']}";
            $result = $this->conn->query($query)->fetch(PDO::FETCH_ASSOC);
            
            if ($result['id_next_player'] == $_SESSION['id'])
                $move = 1;
            else
                $move = 0;

            for ($i=0; $i<8; $i++) {
                $player1Win = false;
                $player2Win = false;

                for ($j=0; $j<3; $j++) {
                    if (in_array((string)$WinningCombinations[$i][$j], $player1Board) && !$player2Win) {
                        $player1Win = true;
                        $player2Win = false;
                    }
                    else if (in_array((string)$WinningCombinations[$i][$j], $player2Board) && !$player1Win) {
                        $player2Win = true;
                        $player1Win = false;
                    }
                    else {
                        $player1Win = false;
                        $player2Win = false;
                        break;
                    }
                }

                if ($player1Win || $player2Win) {
                    $this->json['win'] = !$move;
                    break;
                }
            }
        } 
        // sprawdzenie remisu
        if (!isset($this->json['win']) && $completeBoardCount == 9) {
            $this->json['win'] = -1;
        }

        echo json_encode($this->json);
    }
}