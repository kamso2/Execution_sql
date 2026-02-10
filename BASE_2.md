# Guide de Changement des Noms de Tables (Campagnes)

## Vue d'Ensemble

Ce guide détaille **la procédure complète** pour changer les noms de tables (campagnes) dans l'application Execution_sql.

Les noms de tables sont définis dans **3 emplacements** qui doivent rester **synchronisés** :
1. **Backend PHP** - Configuration serveur
2. **Frontend JavaScript** - Interface utilisateur
3. **Permissions** - Contrôle d'accès par rôle

> [!IMPORTANT]
> Les 3 fichiers doivent être modifiés **en même temps** pour éviter les incohérences entre l'interface et le serveur.

---

## 📋 Checklist Rapide

- [ ] Modifier la liste des tables dans `includes/query_config.php` (Backend)
- [ ] Modifier la liste des tables dans `script.js` (Frontend)
- [ ] Mettre à jour les permissions dans `includes/auth.php`
- [ ] Vérifier la cohérence entre les 3 fichiers
- [ ] Tester l'application

---

## 🔧 Étape 1 : Modifier les Tables Backend (PHP)

### Fichier à Modifier

**[includes/query_config.php](file:///c:/wamp64/www/Execution_sql/includes/query_config.php)**

### Section à Modifier

**Lignes 10-29** : Array `$COMMON_TABLES`

### Structure

```php
$COMMON_TABLES = [
    "clé_interne"  => "NOM_TABLE_BDD",
];
```

- **`clé_interne`** : Identifiant utilisé dans l'application (minuscules, underscores)
- **`NOM_TABLE_BDD`** : Nom réel de la table dans MySQL (tel quel)

### Exemple de Modification

```php
// AVANT (Configuration actuelle - Lignes 10-29)
$COMMON_TABLES = [
    "repeteurs"                  => "FRAGILE_REPETEUR",
    "retour_echus"               => "RETOUR_ECHUS",
    "one_three_months"           => "ECHUS_1_3_MOIS",
    "welcome_call"               => "WC_QUALIFICATION_RECRUTES",
    "access_evasion_tc_inactifs" => "ACCESS_EVASION_TOUT_CANAL_INACTIFS",
    "echus_3_4_mois"             => "ECHUS_3_4_MOIS",
    "evasion_actifs"             => "EVASION_ACTIFS",
    "fragile_retour_echus"       => "FRAGILE_RETOUR_ECHUS_",
    "g11_bundle"                 => "G11_BUNDLE",
    "insight_plus"               => "INSIGHT_PLUS",
    "netflix_inactif"            => "NETFLIX_INACTIF",
    "project_g11"                => "PROJECT_G11",
    "reconquete_access_evasion"  => "RECONQUETE_ACCESS_EVASION_TC_INACTIFS",
    "service_plus"               => "SERVICE_PLUS",
    "ultimate_evasion_plus"      => "ULTIMATE_EVASION_PLUS",
    "ultimate_toutcanal_plus"    => "ULTIMATE_TOUTCANAL_PLUS",
    "upgrade_access_evasion"     => "UPGRADE_ACCESS_EVASION_MOMO_RP",
    "welcome_g11"                => "WELCOME_G11",
];

// APRÈS (Nouvelles campagnes)
$COMMON_TABLES = [
    "campagne_janvier_2026"      => "CAMPAGNE_JANVIER_2026",
    "campagne_fevrier_2026"      => "CAMPAGNE_FEVRIER_2026",
    "campagne_mars_2026"         => "CAMPAGNE_MARS_2026",
    "promo_noel"                 => "PROMO_NOEL_2025",
    "retention_clients"          => "RETENTION_CLIENTS_Q1",
    "acquisition_nouveaux"       => "ACQUISITION_NOUVEAUX_CLIENTS",
    // Garder les anciennes si nécessaire
    "repeteurs"                  => "FRAGILE_REPETEUR",
    "retour_echus"               => "RETOUR_ECHUS",
];
```

### Actions à Effectuer

1. **Ajouter** de nouvelles tables
2. **Supprimer** les tables obsolètes
3. **Renommer** les clés internes si nécessaire
4. **Vérifier** que les noms de tables BDD correspondent exactement à MySQL

---

## 🎨 Étape 2 : Modifier les Tables Frontend (JavaScript)

### Fichier à Modifier

**[script.js](file:///c:/wamp64/www/Execution_sql/script.js)**

### Section à Modifier

**Lignes 66-86** : Array `COMMON_TABLE_OPTIONS`

### Structure

```javascript
const COMMON_TABLE_OPTIONS = [
    { value: "clé_interne", label: "LABEL_AFFICHÉ" },
];
```

- **`value`** : Doit correspondre EXACTEMENT à la `clé_interne` du PHP
- **`label`** : Texte affiché dans l'interface (peut être différent)

### Exemple de Modification

```javascript
// AVANT (Configuration actuelle - Lignes 66-86)
const COMMON_TABLE_OPTIONS = [
    { value: "", label: "-- Sélectionnez une table --" },
    { value: "repeteurs", label: "FRAGILE_REPETEUR" },
    { value: "retour_echus", label: "RETOUR_ECHUS" },
    { value: "one_three_months", label: "ECHUS_1_3_MOIS" },
    { value: "welcome_call", label: "WC_QUALIFICATION_RECRUTES" },
    { value: "access_evasion_tc_inactifs", label: "ACCESS_EVASION_TOUT_CANAL_INACTIFS" },
    { value: "echus_3_4_mois", label: "ECHUS_3_4_MOIS" },
    { value: "evasion_actifs", label: "EVASION_ACTIFS" },
    { value: "fragile_retour_echus", label: "FRAGILE_RETOUR_ECHUS_" },
    { value: "g11_bundle", label: "G11_BUNDLE" },
    { value: "insight_plus", label: "INSIGHT_PLUS" },
    { value: "netflix_inactif", label: "NETFLIX_INACTIF" },
    { value: "project_g11", label: "PROJECT_G11" },
    { value: "reconquete_access_evasion", label: "RECONQUETE_ACCESS_EVASION_TC_INACTIFS" },
    { value: "service_plus", label: "SERVICE_PLUS" },
    { value: "ultimate_evasion_plus", label: "ULTIMATE_EVASION_PLUS" },
    { value: "ultimate_toutcanal_plus", label: "ULTIMATE_TOUTCANAL_PLUS" },
    { value: "upgrade_access_evasion", label: "UPGRADE_ACCESS_EVASION_MOMO_RP" },
    { value: "welcome_g11", label: "WELCOME_G11" }
];

// APRÈS (Nouvelles campagnes)
const COMMON_TABLE_OPTIONS = [
    { value: "", label: "-- Sélectionnez une table --" },
    { value: "campagne_janvier_2026", label: "📅 Campagne Janvier 2026" },
    { value: "campagne_fevrier_2026", label: "📅 Campagne Février 2026" },
    { value: "campagne_mars_2026", label: "📅 Campagne Mars 2026" },
    { value: "promo_noel", label: "🎄 Promo Noël 2025" },
    { value: "retention_clients", label: "🔄 Rétention Clients Q1" },
    { value: "acquisition_nouveaux", label: "🆕 Acquisition Nouveaux Clients" },
    // Garder les anciennes si nécessaire
    { value: "repeteurs", label: "FRAGILE_REPETEUR" },
    { value: "retour_echus", label: "RETOUR_ECHUS" }
];
```

> [!TIP]
> Vous pouvez utiliser des **emojis** dans les labels pour améliorer l'UX !

### Actions à Effectuer

1. **Synchroniser** les `value` avec les clés PHP
2. **Personnaliser** les `label` pour l'affichage
3. **Garder** la première option vide `{ value: "", label: "-- Sélectionnez une table --" }`

---

## 🔐 Étape 3 : Mettre à Jour les Permissions

### Fichier à Modifier

**[includes/auth.php](file:///c:/wamp64/www/Execution_sql/includes/auth.php)**

### Section à Modifier

**Lignes 16-54** : Constante `ROLE_PERMISSIONS`

### Structure

```php
define('ROLE_PERMISSIONS', [
    'admin' => [
        'query_id' => ['table1', 'table2', ...],
    ],
    'user' => [
        'query_id' => ['table1', 'table2', ...],
    ],
]);
```

### Exemple de Modification

```php
// AVANT (Configuration actuelle - Lignes 16-54)
define('ROLE_PERMISSIONS', [
    'admin' => [
        'nombre_de_fiches' => [
            'repeteurs', 'retour_echus', 'one_three_months', 'welcome_call',
            'access_evasion_tc_inactifs', 'echus_3_4_mois', 'evasion_actifs',
            'fragile_retour_echus', 'g11_bundle', 'insight_plus', 'netflix_inactif',
            'project_g11', 'reconquete_access_evasion', 'service_plus',
            'ultimate_evasion_plus', 'ultimate_toutcanal_plus', 'upgrade_access_evasion',
            'welcome_g11'
        ],
        'RELANCE' => [
            'repeteurs', 'retour_echus', 'one_three_months', // ... (même liste)
        ],
        'AJOUT_DATE' => [
            'repeteurs', 'retour_echus', 'one_three_months', // ... (même liste)
        ],
    ],
    'user' => [
        'nombre_de_fiches' => [
            'repeteurs', 'retour_echus', 'one_three_months', // ... (même liste)
        ],
    ],
]);

// APRÈS (Nouvelles campagnes)
define('ROLE_PERMISSIONS', [
    'admin' => [
        'nombre_de_fiches' => [
            'campagne_janvier_2026', 'campagne_fevrier_2026', 'campagne_mars_2026',
            'promo_noel', 'retention_clients', 'acquisition_nouveaux',
            // Garder les anciennes si nécessaire
            'repeteurs', 'retour_echus'
        ],
        'RELANCE' => [
            'campagne_janvier_2026', 'campagne_fevrier_2026', 'campagne_mars_2026',
            'promo_noel', 'retention_clients', 'acquisition_nouveaux'
        ],
        'AJOUT_DATE' => [
            'campagne_janvier_2026', 'campagne_fevrier_2026', 'campagne_mars_2026',
            'promo_noel', 'retention_clients', 'acquisition_nouveaux'
        ],
    ],
    'user' => [
        'nombre_de_fiches' => [
            // Les utilisateurs standards peuvent voir toutes les campagnes
            'campagne_janvier_2026', 'campagne_fevrier_2026', 'campagne_mars_2026',
            'promo_noel', 'retention_clients', 'acquisition_nouveaux'
        ],
        // Les users n'ont PAS accès à RELANCE et AJOUT_DATE (réservé admin)
    ],
]);
```

### Actions à Effectuer

1. **Mettre à jour** les 3 query_id pour le rôle `admin`
2. **Mettre à jour** le query_id pour le rôle `user`
3. **Vérifier** que les clés correspondent au PHP et JS

---

## 📝 Résumé des Fichiers à Modifier

| Fichier | Lignes | Section | Rôle |
|---------|--------|---------|------|
| **includes/query_config.php** | 10-29 | `$COMMON_TABLES` | Mapping clé → table BDD |
| **script.js** | 66-86 | `COMMON_TABLE_OPTIONS` | Liste déroulante frontend |
| **includes/auth.php** | 16-54 | `ROLE_PERMISSIONS` | Contrôle d'accès |

---

## ✅ Vérification de Cohérence

### Checklist de Validation

Avant de tester, vérifiez que :

- [ ] **Clés identiques** : Les `value` JS = clés PHP = permissions
- [ ] **Ordre cohérent** : Même ordre dans les 3 fichiers (recommandé)
- [ ] **Syntaxe correcte** : Virgules, guillemets, crochets
- [ ] **Pas de doublons** : Chaque clé n'apparaît qu'une fois

### Exemple de Vérification

```
PHP (query_config.php)     JS (script.js)              AUTH (auth.php)
----------------------     ------------------          ----------------
"promo_noel"          ✓    value: "promo_noel"    ✓    'promo_noel'    ✓
```

---

## 🧪 Étape 4 : Test de l'Application

### 4.1 Test Frontend

1. **Rafraîchir** la page `index.html` (Ctrl+F5)
2. **Vérifier** que les nouvelles tables apparaissent dans la liste déroulante
3. **Vérifier** que les labels sont corrects

### 4.2 Test Backend

1. **Sélectionner** une nouvelle table
2. **Exécuter** une requête "NOMBRE DE FICHES"
3. **Vérifier** que les données s'affichent correctement

### 4.3 Test Permissions

1. **Se connecter** en tant qu'utilisateur standard
2. **Vérifier** que seules les tables autorisées sont accessibles
3. **Se connecter** en tant qu'admin
4. **Vérifier** l'accès à toutes les tables

---

## 🔄 Procédure de Mise à Jour Progressive

Si vous avez beaucoup de tables à modifier, procédez par étapes :

### Méthode Recommandée

1. **Ajouter** les nouvelles tables (garder les anciennes)
2. **Tester** avec les nouvelles tables
3. **Migrer** progressivement les données
4. **Supprimer** les anciennes tables une fois la migration terminée

### Exemple

```php
// Étape 1 : Ajouter les nouvelles (garder les anciennes)
$COMMON_TABLES = [
    // NOUVELLES
    "campagne_2026_q1" => "CAMPAGNE_2026_Q1",
    
    // ANCIENNES (à supprimer plus tard)
    "repeteurs" => "FRAGILE_REPETEUR",
    "retour_echus" => "RETOUR_ECHUS",
];

// Étape 2 : Une fois la migration terminée, supprimer les anciennes
$COMMON_TABLES = [
    "campagne_2026_q1" => "CAMPAGNE_2026_Q1",
];
```

---

## 💡 Bonnes Pratiques

### Nommage des Clés Internes

✅ **Recommandé** :
- Minuscules uniquement
- Underscores pour séparer les mots
- Noms descriptifs
- Format : `type_periode` ou `campagne_mois_annee`

```php
"campagne_janvier_2026"
"promo_noel_2025"
"retention_q1"
```

❌ **À éviter** :
- Majuscules dans les clés
- Espaces
- Caractères spéciaux
- Noms trop courts

```php
"Campagne2026"  // Majuscules
"promo noel"    // Espaces
"camp-01"       // Tirets
"c1"            // Trop court
```

### Nommage des Labels

✅ **Recommandé** :
- Descriptifs et clairs
- Emojis pour catégoriser
- Format lisible

```javascript
{ value: "campagne_janvier_2026", label: "📅 Campagne Janvier 2026" }
{ value: "promo_noel", label: "🎄 Promo Noël 2025" }
```

---

## 🐛 Dépannage

### Erreur : "Table inconnue"

**Cause** : Incohérence entre PHP et JS

**Solution** :
1. Vérifiez que la clé est identique dans les 3 fichiers
2. Vérifiez la syntaxe (guillemets, virgules)

### Erreur : "Accès refusé"

**Cause** : Table non autorisée dans les permissions

**Solution** :
1. Ajoutez la table dans `includes/auth.php`
2. Vérifiez le rôle de l'utilisateur

### Les nouvelles tables n'apparaissent pas

**Cause** : Cache du navigateur

**Solution** :
1. Rafraîchir avec Ctrl+F5
2. Vider le cache du navigateur

---

## 📚 Ressources Complémentaires

- [BASE.md](file:///c:/wamp64/www/Execution_sql/BASE.md) - Configuration des bases de données
- [README.md](file:///c:/wamp64/www/Execution_sql/README.md) - Documentation générale
