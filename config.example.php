<?php
/* ============================================================
   Eventy — config.php (SECRETS, ne PAS commiter)
   --------------------------------------------------------
   1) Copiez ce fichier en `config.php`
   2) Remplissez avec VOS valeurs
   3) Déposez `config.php` UNE SEULE FOIS dans public_html
      (les pushs Git ne le toucheront plus jamais)
   ============================================================ */

// ---- Base MySQL (hPanel Hostinger > Bases de données MySQL) ----
$DB_HOST = 'localhost';
$DB_NAME = 'uXXXXXXXX_eventy';
$DB_USER = 'uXXXXXXXX_eventy';
$DB_PASS = 'VOTRE_MOT_DE_PASSE_MYSQL';

// ---- Comptes équipe : identifiant => mot de passe ----
// Ajoutez/retirez librement. Un compte retiré ne peut plus se connecter.
$USERS = [
    'ayoub'   => 'changez-moi-1',
    'yassir'  => 'changez-moi-2',
    'abdelah' => 'changez-moi-3',
];

// ---- Clé secrète interne (signature des jetons de session) ----
// Mettez une longue chaîne aléatoire et gardez-la PRIVÉE.
// Exemple : bin2hex(random_bytes(32)) dans une console PHP.
$AUTH_SECRET = 'REMPLACEZ-par-une-longue-cle-aleatoire-unique';
