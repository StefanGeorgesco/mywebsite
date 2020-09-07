<?php
foreach ($members as $member)
{
    if ($member->active())
    {
        ?>
        <h2>
            <?= $member['login'] ?>
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
            Membre inscrit le <?= $member['creationDate']->format('d/m/Y à H\hi') ?>
            <br />
        <?php if ($member['creationDate'] != $member['updateDate']) { ?>
            <small><em>
                Fiche Modifiée le
                <?= $member['updateDate']->format('d/m/Y à H\hi') ?>
            </em></small>
        <?php } ?>
        </p>
        <?php
    }
}
