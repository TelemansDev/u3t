<div id="login-box">
    <?php if (isset($_SESSION['bad-pass'])): ?>
        <span class="span-error">Błędne hasło</span>
    <?php unset($_SESSION['bad-pass']); endif; ?>
    
    <form method="post" autocomplete="off">
        <input name="game-pass" type="password" placeholder="Hasło" spellcheck="false" class="form-style" required>
        <br>
        <input type="submit"  value="Dalej" class="confirm-submit">
    </form>
</div>