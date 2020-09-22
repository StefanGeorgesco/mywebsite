<!DOCTYPE html>
<html>
<head>
    <title><?= isset($title) ? $title : 'Mon super site' ?></title>
    <meta charset="utf-8" />
    <link rel="stylesheet" href="<?= $page->autoVersion('/css/Envision.css') ?>" />
</head>
<body>
    <div id="wrap">
        <header>
            <h1>
                <a href="/">Mon super site</a>
            </h1>
            <p>
                Comment ça, il n'y a presque rien ?
                <?php
                if (isset($numberOfMembers) && $numberOfMembers > 0)
                {
                    echo 'Mais il y a quand même <b>', $numberOfMembers, '</b> ',
                    $numberOfMembers > 1 ? 'membres' : 'membre', ' !';
                }
                ?>
            </p>
            <div class="login">
                <?= $user->getAttribute('login') ?>
            </div>
        </header>
        <nav>
            <ul>
                <li>
                    <a href="/">Accueil</a>
                </li>
                <?php if ($user->isAuthenticated()) { ?>
                <li>
                    <a href="/profile.html">Profil</a>
                </li>
                <li>
                    <a href="/authorizations.html">Autorisations</a>
                </li>
                <li>
                    <a href="/members.html">Membres</a>
                </li>
                <li>
                    <a href="/sign-out.html">Se déconnecter</a>
                </li>
                <?php }
                else { ?>
                <li>
                    <a href="/sign-in.html">Se connecter</a>
                </li>
                <li>
                    <a href="/sign-up.html">S'inscrire</a>
                </li>
                <?php }
                if ($user->isAdmin()) { ?>
                <li>
                    <a href="/admin/">Admin</a>
                </li>
                <li>
                    <a href="/admin/news-insert.html">Ajouter une news</a>
                </li>
                <li>
                    <a href="/admin/members.html">Membres (admin)</a>
                </li>
                <li>
                    <a href="/admin/authorizations.html">Tokens</a>
                </li>
                <li>
                    <a href="/admin/sign-out.html">Quitter admin</a>
                </li>
                <?php } ?>
            </ul>
        </nav>

        <?= isset($pagination) ? $pagination : '' ?>

        <div id="content-wrap">
            <section id="main">
                <?php
                if ($user->hasFlash())
                {
                    $flash = $user->getFlash();
                    echo '<p class="'.
                        $flash['flashType'].
                        '" style="text-align: center;">',
                        $flash['flash'],
                        '</p>';
                }
                ?>
                <?= $content ?>
            </section>
        </div>
        <footer>
            <?= isset($pagination) ? $pagination : '' ?>
        </footer>
    </div>
</body>
</html>
