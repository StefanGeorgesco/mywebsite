<!DOCTYPE html>
<html>
<head>
    <title><?= isset($title) ? $title : 'Mon super site' ?></title>
    <link rel="stylesheet" href="<?= $host ?>css/Envision.css" />
</head>
<body>
    <div id="wrap">
        <header>
            <h1><a href="">Mon super site</a></h1>
        </header>
        <div id="content-wrap">
            <section id="main">
                <h2>Oubli du mot de passe</h2>
                <h3>
                    Bonjour <?= $login ?>, vous avez demandé l'envoi d'un
                    lien de mot de passe oublié pour Mon super site
                </h3>
                <p>
                    Veuillez cliquer sur ce lien ou le recopier dans la barre
                    d'adresse de votre navigateur pour définir un nouveau
                    mot de passe :
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
