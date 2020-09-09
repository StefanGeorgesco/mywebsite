<h2>Modifier mon profil</h2>
<form action="" method="post" onsubmit="return validateForm();">

    <?= $form ?>

    <input type="submit" name="submit" value="Valider" />
    <input type="reset" name="reset" value="Réinitialiser"
        onclick="setTimeout(validateForm, 100)" />
    <span class="customButton" onclick="location.href='profile.html'">
        Annuler
    </span>
</form>
