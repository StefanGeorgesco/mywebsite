<h2>Supprimer un membre</h2>
<h2>
    <?= $member['login'] ?>
    (<small><?= $member->active() ? 'activé' : 'non activé' ?></small>)
</h2>
<p>
    <?php if ($member['firstName'] || $member['lastName']) { ?>
    <b>
        <?= htmlspecialchars($member['firstName']) ?>
        <?= htmlspecialchars($member['lastName']) ?>
    </b>
    <br />
    <?php } ?>
    <?= $page->parseString($member['email']) ?>
    <?= $member['website'] ? ' - '.$page->parseString($member['website']) : '' ?>
    <?= $member['phone'] ? ' - '.$member['phone'] : '' ?>
    <br />
    <?php if ($member['postcode'] || $member['city']) { ?>
    <b>
        <?= htmlspecialchars($member['postcode']) ?>
        <?= htmlspecialchars($member['city']) ?>
    </b>
    <br />
    <?php } ?>
    Membre inscrit le
    <?= $member['creationDate']->format('d/m/Y à H\hi') ?>
    <br />
    <?php if ($member['creationDate'] != $member['updateDate']) { ?>
        <small><em>
            Fiche Modifiée le
            <?= $member['updateDate']->format('d/m/Y à H\hi') ?>
        </em></small>
    <?php } ?>
</p>
<form action="" method="post">
    <p>
        <input type="hidden" name="id" value="<?= $member['id'] ?>" />
        <input type="submit" value="Confirmer la suppression du membre" />
        <span class="customButton" onclick="location.href='/admin/members.html'">
            Annuler
        </span>
    </p>
</form>
