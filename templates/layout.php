<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="public/css/style.css">
    <link rel="stylesheet" href="public/css/game.css">
    <link rel="stylesheet" href="public/css/font-awesome/all.min.css">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link rel="shortcut icon" href="./favicon.ico">
    <title>Ultimate Tic-Tac-Toe</title>
    <script type="text/javascript" src="./public/js/jQuery/jquery-3.5.1.min.js"></script>
    <script type="text/javascript" src="public/js/jQuery/jquery-ui.min.js"></script>
</head>
<body>
    <div id="user-name-icon">
        <?php if (isset($_SESSION['game-id'])): ?>
            <form method="post" action="?action=leaveGame">
                <button type="submit" name="leave-game" value="Opuść grę" id="leave-game-input">
                    <i class="fas fa-sign-out-alt"></i> Opuść grę
                </button>
            </form>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['user-name'])): ?>
            <i class="fas fa-user-circle"></i>
            <span><?php echo  htmlentities($_SESSION['user-name']) ?></span>
        <?php endif; ?>
    </div>
    <div id="container">
        <?php include_once("pages/" . $page . ".php") ?>
    </div>

    <script type="text/javascript" src="./public/js/sweetalert2.all.min.js"></script>
</body>
</html>