// style font awesome dla poszczególnych graczy
const PLAYER_1 = 'fa-times';
const PLAYER_2 = 'fa-circle';

// uchwyt do belki dla poszegónych graczy
const player1_info_move = document.querySelector('.player-1-info');
const player2_info_move = document.querySelector('.player-2-info');

// parametry startowe gry
const listener_interval = setInterval(listener, 1100);

const player1_moves = [
    [], [], [], [], [], [], [], [], []
];
const player2_moves = [
    [], [], [], [], [], [], [], [], []
];
const win_boards = {};

let lastMove = null;

let turn = 0;
let move = 0;

// uchwyt do wszystkich elementów .box
const boxes = document.querySelectorAll('.box');
boxes.forEach(box => {
    box.addEventListener('click', simpleClick)
});

// definicja funkcji wyświetlajacej informację którego gracza jest ruch  
function whoseTurn() {
    if (turn % 2 === 0) {
        player1_info_move.style.textDecoration = 'underline';
        player2_info_move.style.textDecoration = '';
    } else if (turn % 2 !== 0) {
        player1_info_move.style.textDecoration = '';
        player2_info_move.style.textDecoration = 'underline';
    }
}

// obsługa zdarzeń ajax
function initGame() {
    $.get('./src/actionGameAjax.php' , {
        data: 'initData'
    }, function(response) {
        console.log(response);

        if (response.move)
            move = 1;
        else
            move = 0;
        
        if (response.lastMove)
            lastMove = response.lastMove;
        
        turn = parseInt(response.turn);
        
        // Wypełnienie planszy
        if (response.board) {
            response.board.forEach((element, index) => {
                let currentBox = element.box * 9 + element.y * 3 + parseInt(element.x);

                if (element.value === 'X') {
                    player1_moves[element.box].push(element.y * 3 + parseInt(element.x));
                    boxes[currentBox].classList.add('times-color', 'fas', 'fa-times');
                }
                else {
                    player2_moves[element.box].push(element.y * 3 + parseInt(element.x));
                    boxes[currentBox].classList.add('circle-color', 'far', 'fa-circle');
                }
            });
        }

        // Kolorowanie zwycięskich boxów
        changeWinStyle(response.singleBoardWin);
        
        // Kolorowanie boxa dla następnego gracza
        if (lastMove) {
            let nextBox = lastMove.y * 3 + parseInt(lastMove.x);
            let singleBoard =  document.querySelectorAll('[data-box~="' + nextBox + '"]');
            changeBoardStyle('next-box', singleBoard);
        } else {
            changeBoardStyle('next-box', boxes);
        }

        checkWin();
        whoseTurn();
    }, 'json');
}

function pushData(dataset) {
    $.post('./src/actionGameAjax.php', {
        dataBox: dataset.box,
        dataRow: dataset.row,
        dataCol: dataset.col
    }, function(response) {
    });
}

function checkWin() {
    $.get('./src/actionGameAjax.php', {
        data: 'checkWin'
    }, function(response) {
        console.log(response);
        if (response.win == 1) {
            swal.fire({
                title: 'Wygrana!',
                icon: 'success',
                confirmButtonText: 'Ok'
            });

            move = 0;
            clearInterval(listener_interval);
        }
        else if (response.win == 0) {
            swal.fire({
                title: 'Porażka!',
                icon: 'error',
                confirmButtonText: 'Ok'
            });

            move = 0;
            clearInterval(listener_interval);
        }
        else if (response.win == -1) {
            swal.fire({
                title: 'Remis!',
                icon: 'info',
                confirmButtonText: 'Ok'
            });

            move = 0;
            clearInterval(listener_interval);
        }
    }, 'json');
}

function listener() {
    $.get('./src/actionGameAjax.php', {
        data: 'getData'
    }, function(response) {
        
        // Wypełnienie planszy
        if (response.board) {
            let lastBox = lastMove ? lastMove.box * 9 + lastMove.y * 3 + parseInt(lastMove.x) : null;
            let currentBox = response.board.box * 9 + response.board.y * 3 + parseInt(response.board.x);
            let nextBox = response.board.y * 3 + parseInt(response.board.x);
            turn = parseInt(response.turn);

            if (currentBox !== lastBox) {
                if (response.board.value === 'X') {
                    player1_moves[response.board.box].push(response.board.y * 3 + parseInt(response.board.x));
                    boxes[currentBox].classList.add('times-color', 'fas', 'fa-times');
                    checkSingleBoard(player1_moves[response.board.box], response.board.box, 'X');
                }
                else {
                    player2_moves[response.board.box].push(response.board.y * 3 + parseInt(response.board.x));
                    boxes[currentBox].classList.add('circle-color', 'far', 'fa-circle');
                    checkSingleBoard(player2_moves[response.board.box], response.board.box, 'O');
                }

                // Dodanie styli do następnego ruchu
                console.log(Object.keys(win_boards));
                if (Object.keys(win_boards).indexOf(String(nextBox)) === -1) {
                    let singleBoard = document.querySelectorAll('[data-box~="' + nextBox + '"]');
                    changeBoardStyle('next-box', singleBoard, boxes);
                } else {
                    changeBoardStyle('next-box', boxes);
                }
                
                lastMove = response.board;
            }
        }

        // Pozostała logika
        move = (response.move === 1) ? 1 : 0;
        whoseTurn();
    }, 'json')
}


function changeBoardStyle(style, nextBoards, removeBoards = []) {
    removeBoards.forEach(element => {
        element.classList.remove(style);
    });

    nextBoards.forEach(element => {
        element.classList.add(style);
    });
}

function changeWinStyle(singleBoards) {
    let singleBoard;
    singleBoards.forEach(element => {
        singleBoard = document.querySelectorAll('[data-box~="' + element.box + '"]');

        if (element.value === "X") {
            win_boards[element.box] = element.value;
            changeBoardStyle('times-box-win', singleBoard);
        } else {
            win_boards[element.box] = element.value;
            changeBoardStyle('circle-box-win', singleBoard);
        }
    });
}

function checkSingleBoard(board, numberBoard, value) {
    const WINNING_COMBINATIONS = [
        [0, 1, 2], [3, 4, 5], [6, 7, 8], [0, 3, 6], [1, 4, 7], [2, 5, 8], [0, 4, 8], [2, 4, 6]
    ];
    let win = false;
    
    if (board.length > 2) {
        for (let i=0; i<8; i++) {
            for (let j=0; j<3; j++) {
                if (board.indexOf(WINNING_COMBINATIONS[i][j]) !== -1) {
                    win = true;
                } else {
                    win = false;
                    break;
                }
            }
            
            if (win) {
                let singleBoard = document.querySelectorAll('[data-box~="' + numberBoard + '"]');

                if (value === 'X') {
                    win_boards[numberBoard] = value;    
                    changeBoardStyle('times-box-win', singleBoard);
                }
                else {
                    win_boards[numberBoard] = value;
                    changeBoardStyle('circle-box-win', singleBoard);
                }

                checkWin();
                break;
            }
        }

        // sprawdzenie remisu
        if (!win && (player1_moves[numberBoard].length + player2_moves[numberBoard].length === 9)) {
            checkWin();
        }
    }
}

// definicja funkcji klikającej
function simpleClick(event) {
    if (move) {
        let lastBox = lastMove ? lastMove.y * 3 + parseInt(lastMove.x) : null;
        if (lastBox === null || event.target.dataset.box == lastBox || Object.keys(win_boards).indexOf(String(lastBox)) !== -1) {
            
            // sprawczenie czy ruch nie jest w wygranym polu
            if (Object.keys(win_boards).indexOf(event.target.dataset.box) === -1) {

                // określenie ruchu (X || O)  
                if (turn % 2 === 0) {
                    event.target.classList.add('times-color', 'fas', PLAYER_1);
                } else {
                    event.target.classList.add('circle-color', 'far', PLAYER_2);
                }

                pushData(event.target.dataset);
                turn++;
                move = 0;
                whoseTurn();
            }
        }
    }
}

function startGame() {
    initGame();
}
startGame();