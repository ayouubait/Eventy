# Eventy

Gestionnaire d'événements et de billetterie — application web mono-fichier.
Gère plusieurs événements, le stock de billets, les dépôts datés chez les points
de vente, une caisse POS (Prospect + Guichet), les dépenses, le suivi de
production (tâches), et le **check-in QR** des participants avec scan caméra.

## Fichiers

| Fichier | Rôle |
|---|---|
| **`index.html`** | L'app (à ouvrir directement ou à servir par Hostinger). |
| **`api.php`** | API serveur (lecture/écriture des données + login). |
| **`config.example.php`** | Modèle de configuration à recopier en `config.php` côté serveur. |
| **`PARTAGE-EQUIPE.md`** | Guide pas-à-pas de mise en ligne et de gestion des comptes. |

## Modes

- **Mode local** : ouvrez `index.html` dans un navigateur. Données dans le navigateur (localStorage), aucun login, non partagé.
- **Mode partagé** : `api.php` + `config.php` déposés sur Hostinger, `CONFIG.API_URL` rempli dans `index.html`. Les collègues se connectent avec leur compte ; les données vivent dans MySQL.

## Déploiement (Hostinger + Git)

1. **Base MySQL** — hPanel → *Bases de données MySQL* → créez base + utilisateur.
2. **Sur le serveur, une seule fois** : copiez `config.example.php` en `config.php` dans `public_html`, et remplissez-y vos identifiants MySQL + comptes équipe + clé secrète.
   `config.php` n'est **jamais touché** par les pushs Git (il est dans `.gitignore`).
3. **Branchez le repo Git** (hPanel → *Git* → *Create repository* → choisissez ce repo + `public_html`). Activez le *Auto-deploy on push*.
4. **Dans `index.html`** (avant le premier push, ou par push), remplissez `CONFIG.API_URL` avec votre domaine. Push → l'app est en ligne.

Détails complets dans **[PARTAGE-EQUIPE.md](PARTAGE-EQUIPE.md)**.

## Sécurité

- Les secrets (MySQL, mots de passe utilisateurs, clé) sont dans `config.php` — **ignoré par Git**, ne quitte jamais le serveur.
- L'API exige un jeton signé HMAC (X-Auth) ; sans connexion valide, l'API renvoie 401.
- Les comptes se gèrent en éditant `$USERS` dans `config.php` sur le serveur.
