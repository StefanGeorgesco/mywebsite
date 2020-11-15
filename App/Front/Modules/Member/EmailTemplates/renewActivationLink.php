<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <title><?= isset($title) ? $title : 'Mon super site' ?></title>
    <link rel="stylesheet" href="<?= $baseUrl ?>css/Envision.css" />
</head>
<body>
    <div id="wrap">
        <header>
            <h1><a href="">Mon super site</a></h1>
        </header>
        <div id="content-wrap">
            <section id="main">
                <h2>Activation de votre compte</h2>
                <h3>
                    Bonjour <?= $login ?>, vous avez demandé le renvoi d'un
                    lien d'activation de votre compte Mon super site
                </h3>
                <p>
                    Cliquez sur ce lien ou recopiez-le dans la barre
                    d'adresse de votre navigateur pour activer votre compte :
                </p>
                <p><a href="<?= $url ?>" target="_blank"><?= $url ?></a></p>
                <p>Ce lien est actif pendant 24 heures</p>
                <p>
                    <small>
                        Si vous n'êtes pas à l'origine de cette demande,
                        merci de contacter notre service clients.
                    </small>
                </p>
            </section>
        </div>
        <footer></footer>
    </div>
</body>
</html>
