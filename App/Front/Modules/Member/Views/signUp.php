<h2>S'inscrire</h2>
<form action="" method="post" onsubmit="return validateForm();">

    <?= $form ?>

    <input id="initial_login" type="hidden" name="initial_login" value="<?=
        $initial_login ?>">
    <input type="submit" name="submit" value="Valider" />
    <span class="customButton" onclick="location.href='/'">Annuler</span>
</form>
<a href="/activation-link-renew.html">Renvoyer un lien d'activation</a>
