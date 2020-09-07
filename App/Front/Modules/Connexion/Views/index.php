<h2>Connexion</h2>

<form action="" method="post" onsubmit="return validateForm();">
    
    <?= $form ?>

    <label >Rester connecté(e)</label>
    <input type="checkbox" name="keepConnection" /><br /><br />

    <input type="submit" value="Connexion" />
    <span class="customButton" onclick="location.href='.'">Annuler</span>
</form>
<a href="/renewpasswordlink-send.html">Mot de passe oublié ?</a>
