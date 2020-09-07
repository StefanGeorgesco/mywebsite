<h2>Mon profil</h2>
<p>
    <label>Prénom</label>
    <?= htmlspecialchars($member['firstName']) ?><br />
    <label>Nom</label>
    <?= htmlspecialchars($member['lastName']) ?><br />
    <label>Date de naissance</label>
    <?= $member['birthDate'] ? $member['birthDate']->format('d/m/Y'): '' ?><br />
    <label>Pseudo</label>
    <?= $member['login'] ?><br />
    <label>Mot de passe</label>
    ********
    <button class="customButton" onclick="location.href='password-change.html'">
        Changer
    </button>
    <label>Email</label>
    <?= $page->parseString($member['email']) ?><br />
    <label>Site Web</label>
    <?= $page->parseString($member['website']) ?><br />
    <label>Téléphone</label>
    <?= $member['phone'] ?><br />
    <label>Adresse :</label>
    <label>N°</label>
    <?= $member['housenumber'] ?><br />
    <label>Rue</label>
    <?= $member['street'] ?><br />
    <label>Code postal</label>
    <?= $member['postcode'] ?><br />
    <label>Commune</label>
    <?= $member['city'] ?><br />

</p>
<button class="customButton" onclick="location.href='member-update.html'">
    Modifier mon profil
</button>
<button class="customButton" onclick="location.href='member-delete.html'">
    Supprimer mon profil
</button>
