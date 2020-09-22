<h2>Supprimer une autorisation</h2>
<h2><?= $authorization['creationDate']->format('d/m/Y à H\hi') ?></h2>
<p><?= $page->parseString($authorization['description']) ?></p>
<?php
if ($authorization['creationDate'] != $authorization['updateDate']) { ?>
    <p><small><em>
        Description modifiée le
        <?= $authorization['updateDate']->format('d/m/Y à H\hi') ?>
    </em></small></p><br />
<?php }
?>
<form action="" method="post">
    <p>
        <input type="submit" value="Je confirme la suppression" />
        <span class="customButton" onclick="location.href='authorizations.html'">
            Annuler
        </span>
    </p>
</form>
