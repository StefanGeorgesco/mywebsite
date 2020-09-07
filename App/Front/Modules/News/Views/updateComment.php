<p>
    Par <em><?= htmlspecialchars($news['author']) ?></em>,
    le <?= $news['creationDate']->format('d/m/Y à H\hi') ?>
</p>
<h2><?= $news['title'] ?></h2>
<p><?= $page->parseString($news['contents']) ?></p>

<?php if ($news['creationDate'] != $news['updateDate']) { ?>
    <p style="text-align: right;">
        <small><em>
            Modifiée le <?= $news['updateDate']->format('d/m/Y à H\hi') ?>
        </em></small>
    </p>
<?php } ?>

<h2>Modifier un commentaire</h2>
<p>
    Par <em><?= $comment['author'] ?></em>,
    Commentaire créé le <?= $comment['creationDate']->format('d/m/Y à H\hi') ?>
</p>
<?php if ($comment['creationDate'] != $comment['updateDate']) { ?>
    <p style="text-align: right;">
        <small><em>
            Modifié le <?= $comment['updateDate']->format('d/m/Y à H\hi') ?>
        </em></small>
    </p>
<?php } ?>

<form action="" method="post" onsubmit="return validateForm();">
    <p>

        <?= $form ?>
        
        <input type="submit" name="submit" value="Valider" />
        <input type="reset" name="reset" value="Réinitialiser"
            onclick="setTimeout(validateForm, 100)" />
        <span class="customButton"
            onclick="location.href='/news-<?= $news['id'] ?>.html'">
            Annuler
        </span>
    </p>
</form>
