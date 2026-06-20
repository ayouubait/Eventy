# Eventy — mise en ligne + comptes (Hostinger + MySQL)

Par défaut l'app marche en **mode Local** (données sur votre navigateur, sans connexion).
Pour la partager avec l'équipe **avec un compte par personne**, on héberge une petite API PHP
(`api.php`) + une base MySQL **sur votre Hostinger**. Aucun service tiers.

```
Connexion (utilisateur + mot de passe)  →  api.php (Hostinger)  →  base MySQL
   chacun son compte · données partagées · personne n'entre sans identifiants
```

---

## 1. Créer la base MySQL (Hostinger)

1. **hPanel** → **Bases de données** → **Bases de données MySQL**.
2. Créez une **base** + un **utilisateur** (privilèges complets). Notez les **4 valeurs** :
   hôte (`localhost`), nom de la base, utilisateur, mot de passe.

## 2. Configurer `api.php` (base + comptes)

Ouvrez **`api.php`** et remplissez le haut du fichier :

```php
// Base
$DB_HOST = 'localhost';
$DB_NAME = 'u123456789_eventy';
$DB_USER = 'u123456789_eventy';
$DB_PASS = 'mot_de_passe_mysql';

// Comptes : identifiant => mot de passe (ajoutez/retirez librement)
$USERS = [
  'ayoub'   => 'unMotDePasseFort1',
  'yassir'  => 'unMotDePasseFort2',
  'abdelah' => 'unMotDePasseFort3',
];

// Clé secrète : une longue chaîne aléatoire, gardez-la privée
$AUTH_SECRET = 'collez-ici-une-longue-cle-aleatoire-unique';
```

> Les mots de passe et la clé restent **côté serveur** (jamais envoyés au navigateur).
> Pour retirer l'accès à quelqu'un : supprimez sa ligne dans `$USERS`.

## 3. Mettre les fichiers en ligne

1. **hPanel** → **Gestionnaire de fichiers** → dossier **`public_html`**.
2. Déposez‑y **`api.php`** et **`index.html`** (l'app s'ouvre directement à l'adresse du domaine).
3. Test : ouvrez `https://votre-domaine.com/api.php` → vous devez voir
   `{"error":"unauthorized"}` (normal : l'accès aux données exige une connexion).
   Si vous voyez `db_connection_failed`, revérifiez les identifiants MySQL.

## 4. Brancher l'app

Ouvrez **`index.html`**, tout en haut du `<script>`, mettez l'adresse de l'API :

```js
const CONFIG = {
  API_URL: "https://votre-domaine.com/api.php",
  POLL_SECONDS: 8
};
```

Enregistrez, ré‑uploadez. Rechargez la page : un écran **« Eventy — Connexion »** s'affiche.
Chaque personne entre **son identifiant + mot de passe** (ceux de `$USERS`). La pastille en haut
passe à **« Partagé · prénom »** (vert). Donnez à chacun ses identifiants. ✅

---

### Bon à savoir
- **Sécurité** : sans identifiants valides, impossible de lire ou modifier les données (l'API
  renvoie 401). Le jeton de connexion est signé côté serveur (non falsifiable). Bouton de
  **déconnexion** en haut à droite.
- **Pastille** : verte « Partagé · prénom » = connecté. Rouge « Hors‑ligne » = serveur injoignable.
- **Synchro** : les changements des collègues apparaissent automatiquement (toutes les
  `POLL_SECONDS`, et au retour sur l'onglet). Bouton **↻** pour forcer.
- **Sauvegarde** : le bouton **Sauver** télécharge un `.json` de toute la base — gardez‑en une copie.
- **Conflit** : si deux personnes modifient la même donnée au même instant, la dernière gagne
  (rare grâce à l'auto‑actualisation).
- **Mots de passe hachés (optionnel)** : on peut passer à `password_hash` au lieu du texte clair
  dans `$USERS` — demandez‑le si besoin.
- **PostgreSQL** : changez juste la ligne `$dsn` dans `api.php` (un commentaire l'indique).
