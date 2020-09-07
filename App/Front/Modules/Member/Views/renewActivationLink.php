<h2>Renvoyer un lien d'activation</h2>
<p>
    Veuillez renseigner l'adresse email que vous avez utilisée lors de
    la demande de création de compte
</p>
<form action="" method="post" onsubmit="return validateForm();">
    <p>
        
        <?= $form ?>

        <input type="submit" value="Envoyer" />
        <span class="customButton" onclick="location.href='/'">Annuler</span>
    </p>
</form>
