<p style="text-align: center">
    Il y a actuellement <?= $nombreNews ?> news.
</p>

<table>
    <tr>
        <th>Auteur</th>
        <th>Titre</th>
        <th>Contenu</th>
        <th>Créé</th>
        <th>Modifié</th>
        <th>Action</th>
    </tr>
    <?php
    foreach ($listeNews as $news)
    {
        ?>
        <tr>
            <td><?= htmlspecialchars($news['author']) ?></td>
            <td><?= htmlspecialchars($news['title']) ?></td>
            <td><?= $page->parseString($news['contents']) ?></td>
            <td>le <?= $news['creationDate']->format('d/m/Y à H\hi') ?></td>
            <td>
                <?= $news['creationDate'] == $news['updateDate'] ? '-' :
                    'le '.$news['updateDate']->format('d/m/Y à H\hi') ?>
            </td>
            <td>
                <a href="news-update-<?= $news['id'] ?>.html">
                    <img
                        src="<?= $page->autoVersion('/images/update.png') ?>"
                        alt="Modifier"
                        title="Modifier">
                </a>
                <a href="news-delete-<?= $news['id'] ?>.html">
                    <img
                        src="<?= $page->autoVersion('/images/delete.png') ?>"
                        alt="Supprimer"
                        title="Supprimer">
                </a>
            </td>
        </tr>
        <?php
    }
    ?>
</table>
