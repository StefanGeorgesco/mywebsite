<h2>Modifier une news</h2>
<p>
    News créée le <?= $news['creationDate']->format('d/m/Y à H\hi') ?>
</p>
<?php if ($news['creationDate'] != $news['updateDate']) { ?>
    <p style="text-align: right;">
        <small><em>
            Modifiée le <?= $news['updateDate']->format('d/m/Y à H\hi') ?>
        </em></small>
    </p>
<?php } ?>

<form action="" method="post" onsubmit="return validateForm();">
  <p>

    <?= $form ?>
    
    <input type="submit" name="submit" value="Valider" />
    <input type="reset" name="reset" value="Réinitialiser"
        onclick="setTimeout(validateForm, 100)" />
    <span class="customButton" onclick="location.href='.'">Annuler</span>
  </p>
</form>
