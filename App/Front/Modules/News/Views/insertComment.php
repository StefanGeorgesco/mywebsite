<p>
    Par <em><?= htmlspecialchars($news['author']) ?></em>,
    le <?= $news['creationDate']->format('d/m/Y à H\hi') ?>
</p>
<h2><?= htmlspecialchars($news['title']) ?></h2>
<p><?= $page->parseString($news['contents']) ?></p>

<?php if ($news['creationDate'] != $news['updateDate']) { ?>
    <p style="text-align: right;">
        <small><em>
            Modifiée le <?= $news['updateDate']->format('d/m/Y à H\hi') ?>
        </em></small>
    </p>
<?php } ?>

<h2>Ajouter un commentaire</h2>
<form action="" method="post" onsubmit="return validateForm();">

    <?= $form ?>

    <input type="submit" name="submit" value="Valider" />
    <span class="customButton"
        onclick="location.href='/news-<?= $news['id'] ?>.html'">
        Annuler
    </span>
</form>
