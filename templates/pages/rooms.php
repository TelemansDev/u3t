<div id="rooms-view">
    <div id="header-rooms-view">
        <h2>Lista pokoi</h2>
    </div>
    
    <div id="content-rooms-view">
        <div id="list-rooms">
            <table id="rooms-table">
                <tr style="font-size: 18px; border-bottom: 1px solid white;">
                    <td class="rooms-td-name">Nazwa</td>
                    <td class="rooms-td-user">Użytkownik</td>
                    <td class="rooms=td-pass">Hasło</td>
                    <td class="rooms-td-join"></td>
                </tr>
                <?php foreach ($params as $room): ?>
                <tr>
                    <td class="rooms-td-name"><?php echo htmlentities($room['name']); ?></td>
                    <td class="rooms-td-user"><?php echo htmlentities($room['user_name']); ?></td>
                    <td class="rooms-td-pass"><?php $room['password'] != "" ?  print("<i class='fas fa-key'></i>") : print(""); ?></td>
                    <td class="rooms-td-join"><a href="<?php echo "./?action=game&id={$room['id']}" ?>"><input type="button" value="Dołącz" class="join-button"></a></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <div id="actions-rooms">
            <input type="button" value="Utwórz" class="operation-button" id="create-room-button">
        </div>
    </div>

    <div id="dialog-form" title="Utwórz nowy pokój">
        <form method="post" action="?action=createRoom" autocomplete="off">
            <input type="text" name="room-name" placeholder="Nazwa pokoju" class="create-room-input" required>
            <br>
            <input type="password" name="room-pass" placeholder="Hasło (opcjonalnie)" class="create-room-input">
            <br>
            <input type="submit" value="Stwórz" class="confirm-submit">
        </form>
    </div>
</div>
<script src="public/js/dialog-form.js"></script>