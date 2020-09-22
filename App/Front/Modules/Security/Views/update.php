<h2>Modifier une autorisation</h2>
<form action="" method="post" onsubmit="return validateForm();">

    <?= $form ?>

    <input type="submit" name="submit" value="Valider" />
    <input type="reset" name="reset" value="Réinitialiser"
        onclick="setTimeout(validateForm, 100)" />
    <span class="customButton"
        onclick="location.href='/authorizations.html'">
        Annuler
    </span>
</form>
