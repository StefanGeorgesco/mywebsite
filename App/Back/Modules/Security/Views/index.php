<?php
foreach ($authorizations as $authorization)
{
    ?>
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
    <p>
        <a href="authorization-update-<?= $authorization['id'] ?>.html">
            modifier
        </a> -
        <a href="authorization-delete-<?= $authorization['id'] ?>.html">
            supprimer
        </a>
    </p>
    <?php
}
?>
<br />
<button class="customButton"
    onclick="location.href='authorization-add.html'">
    Nouvelle autorisation
</button>
