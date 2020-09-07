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

<p><a href="comment-<?= $news['id'] ?>.html">Ajouter un commentaire</a></p>

<?php
if (empty($comments))
{
    ?>
    <p>
        Aucun commentaire n'a encore été posté.
        Soyez le premier à en laisser un !
    </p>
    <?php
}
foreach ($comments as $comment)
{
    ?>
    <fieldset>
        <legend>
            Posté par
            <strong><?= $comment['author'] ?></strong>
            le <?= $comment['creationDate']->format('d/m/Y à H\hi') ?>
            <?php
            if ($user->getAttribute('login') == $comment['author'])
            { ?>
                |
                <a href="comment-update-<?= $comment['id'] ?>.html">
                    Modifier
                </a>
                |
                <a href="comment-delete-<?= $comment['id'] ?>.html">
                    Supprimer
                </a>
            <?php
            }
            if ($user->isAdmin())
            { ?>
                |
                <a href="comment-update-<?= $comment['id'] ?>.html">
                    <img
                        src="<?= $page->autoVersion('/images/update.png') ?>"
                        alt="Modifier"
                        title="Modifier">
                </a>
                |
                <a href="comment-delete-<?= $comment['id'] ?>.html">
                    <img
                        src="<?= $page->autoVersion('/images/delete.png') ?>"
                        alt="Supprimer"
                        title="Supprimer">
                </a>
            <?php
            } ?>
        </legend>
        <p><?= $page->parseString($comment['contents']) ?></p>
    </fieldset>
    <?php
}
?>

<p><a href="comment-<?= $news['id'] ?>.html">Ajouter un commentaire</a></p>
