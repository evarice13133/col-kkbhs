# NotesMaster Deployment Guide

## Vérification pré-déploiement
- Aucun fichier de debug dans `scripts/` ou `scratch/` (sauf `prepare_prod.php`)
- Configurations dans `config/config.php` → OK
- Version dans `VERSION` → à mettre à jour

## Migration Runner

```bash
php scratch/MigrationRunner.php
```

Exécute les migrations non encore appliquées et les enregistre dans la table `migrations`.

## Déploiement

1. `git pull` sur le serveur
2. Backup BDD
3. Mode maintenance (activer `public/maintenance.html`)
4. `composer install --no-dev` si besoin
5. `php scratch/MigrationRunner.php`
6. Nettoyer cache
7. Vérifier connexion BDD
8. Tester application
9. Retirer mode maintenance

## Créer une migration

Créer un fichier `scripts/migration_XXX.php` avec le code SQL requis.
Le MigrationRunner l'exécutera automatiquement si non encore fait.