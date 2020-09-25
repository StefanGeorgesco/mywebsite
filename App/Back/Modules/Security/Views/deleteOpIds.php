<h2>Supprimer les opids d'une autorisation</h2>
<h2><?= $authorization['creationDate']->format('d/m/Y à H\hi') ?></h2>
<p><?= $page->parseString($authorization['description']) ?></p>

<?php
if ($authorization['creationDate'] != $authorization['updateDate'])
{
    ?>
    <p><small><em>
        Autorisation modifiée le
        <?= $authorization['updateDate']->format('d/m/Y à H\hi') ?>
    </em></small></p><br />
    <?php
}
?>

<p>
    <?php
        $nbOpIds = count($authorization['opIds']);
        if ($nbOpIds)
        {
            echo "$nbOpIds opid" . ($nbOpIds > 1 ? "s" : "");
        }
        else
        {
            echo "Aucun opid";
        }
        echo " pour cette autorisation";
    ?>
</p>

<?php
if ($nbOpIds)
{
    ?>
    <p>
        <ul>
            <?php
            foreach ($authorization['opIds'] as $opId)
            {
                echo "<li>$opId</li>";
            }
            ?>
        </ul>
    </p>
    <?php
}
?>

<form action="" method="post">
    <p>
        <?php
        if ($nbOpIds)
        {
            ?>
            <input type="submit" value="Je confirme la suppression" />
            <?php
        }
         ?>
        <span class="customButton" onclick="location.href='authorizations.html'">
            <?php
            if ($nbOpIds)
            {
                ?>
                Annuler
                <?php
            }
            else
            {
                ?>
                Retour
                <?php
            }
            ?>
        </span>
    </p>
</form>
