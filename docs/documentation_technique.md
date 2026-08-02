# Documentation Technique NoteMaster

## 1. Architecture Générale

**NoteMaster** est une application web PHP orientée objet basée sur un motif d'architecture MVC (Modèle-Vue-Contrôleur) personnalisé et un frontal dynamique (Bootstrap 5, Icons, CSS modernisé).

```
public/index.php (Front Controller / Router)
       │
       ▼
   App\Core\Security & Session & Locale
       │
       ▼
   App\Core\PermissionManager (RBAC Engine)
       │
       ├───────────────────────┬───────────────────────┐
       ▼                       ▼                       ▼
App\Controllers\*      App\Services\*          App\Models\*
       │                                               │
       ▼                                               ▼
App\Views\*                                    MySQL Database (PDO)
```

---

## 2. Système d'Authentification & Middleware

1. **Gatekeeper Global (`public/index.php`)** :
   Toute requête HTTP est interceptée à l'entrée par `Security::applyHeaders()`, `Session::start()` et `Locale::bootstrapFromRequest()`.
   Un middleware d'authentification valide les requêtes non publiques :
   ```php
   if (!in_array($path, ['/', '/login', '/logout', '/register-teacher', '/sitemap.xml', '/contact/send', '/payments/verify']) 
       && strpos($path, '/verify-receipt') !== 0 
       && !Security::validateSession()) {
       header('Location: /login');
       exit;
   }
   ```

2. **Traçabilité des Activités (`ActivityTracker`)** :
   Chaque requête exécutée par un utilisateur connecté alimente la table `activity_logs` via `ActivityTracker->trackRequest($path, $method)`.

---

## 3. Architecture du Contrôle d'Accès basé sur les Rôles (RBAC)

### 3.1 Structure des Tables RBAC

```sql
roles (id, role_code, role_name, description)
permissions (id, perm_code, perm_name, description)
role_permissions (role_id, permission_id) -- Clé primaire composite
users (id, username, password_hash, role, status, ...)
```

### 3.2 Composant Central `App\Core\PermissionManager`

La classe `PermissionManager` centralise les vérifications d'habilitation :

- `hasPermission(string $permCode): bool` : Vérifie si le rôle de la session en cours dispose du code de permission `$permCode`. (Le rôle `superadmin` retourne systématiquement `true`). Les permissions du rôle sont mises en cache durant l'exécution de la requête.
- `requirePermission(string $permCode): void` : Valide la permission et, en cas d'échec, enregistre un journal de sécurité via `Security::log()` puis renvoie une page HTTP 403 Forbidden.
- `hasRole(string|array $roles): bool` : Vérifie si le rôle de la session correspond aux rôles spécifiés.
- `requireRole(string|array $roles): void` : Exige le ou les rôles et déclenche une erreur 403 en cas de non-respect.

---

## 4. Routage et Gestion des Exceptions

### 4.1 Stratégie de Routage
Le routage s'effectue par analyse du chemin URL (`parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)`) et de la méthode HTTP (`GET`, `POST`).
Chaque route instancie le contrôleur adapté et invoque l'action correspondante.

### 4.2 Traitement des Erreurs Access
- **403 Forbidden** : Déclenché par `PermissionManager::denyAccess()`. Affiche la vue `src/Views/errors/403.php` avec une notification de journalisation dans `logs/security.log`.
- **404 Not Found** : Déclenché lorsque le chemin ne correspond à aucune route enregistrée dans `public/index.php`.

---

## 5. Maintenance, Backup & Migrations

### 5.1 Migration Runner (`scratch/MigrationRunner.php`)
Permet l'exécution séquentielle et sécurisée des migrations SQL. L'état d'exécution de chaque migration est consigné dans la table `migrations`.

Commandes d'exécution :
```powershell
# En ligne de commande
php scratch/MigrationRunner.php

# Via le navigateur (Superadmin / Admin)
GET /admin/run-migrations
```

### 5.2 Sauvegarde Automatisée (`scripts/run_weekly_backup.php`)
Crée un snapshot SQL complet de la base de données dans `storage/backups/` et maintient une rotation automatique des sauvegardes.

---

## 6. Bonnes Pratiques de Développement RBAC

1. Ne jamais tester le rôle en dur dans les contrôleurs si une permission spécifique existe (`use PermissionManager::requirePermission(...)`).
2. Dans les vues UI (`layout.php`), toujours conditionner l'affichage d'un élément d'action ou d'un onglet du ruban avec `PermissionManager::hasPermission('perm_code')`.
3. Vérifier systématiquement les paramètres d'ID passés en GET (`$_GET['id']`) avant modification/suppression.
