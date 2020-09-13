<h2>Modifier mon profil</h2>
<form action="" method="post" onsubmit="return validateForm();">

    <?= $form ?>

    <input id="initial_login" type="hidden" name="initial_login" value="<?=
        $initial_login ?>">
    <input type="submit" name="submit" value="Valider" />
    <input type="reset" name="reset" value="Réinitialiser"
        onclick="setTimeout(validateForm, 100)" />
    <span class="customButton" onclick="location.href='profile.html'">
        Annuler
    </span>
</form>
