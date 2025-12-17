# Documentation de la Bibliothèque YouConfig

**YouConfig** est un composant simple et efficace pour gérer la configuration de votre application. Il permet de charger des fichiers de configuration PHP et d'accéder aux valeurs via une notation par points ("dot notation").

## Fonctionnalités Principales

- **Chargement Automatique** : Charge tous les fichiers `.php` d'un répertoire donné.
- **Dot Notation** : Accès simplifié aux configurations imbriquées (ex: `database.mysql.host`).
- **Helpers de Typage** : Méthodes `getInt` et `getBool` pour récupérer des valeurs typées.
- **Validation** : Méthode `validate` pour s'assurer que les clés critiques sont présentes.

## Installation

```bash
composer require you-framework/you-config
```

## Initialisation

Pour utiliser `YouConfig`, instanciez la classe `YouConfig\Config` en lui passant le chemin absolu vers votre dossier de configuration.

```php
use YouConfig\Config;

$configPath = __DIR__ . '/config';
$config = new Config($configPath);
```

### Structure du Dossier de Configuration

Le dossier de configuration doit contenir des fichiers PHP qui retournent un tableau associatif. Le nom du fichier (sans extension) servira de clé racine pour accéder aux configurations qu'il contient.

**Exemple :** `config/app.php`

```php
<?php

return [
    'name' => 'Mon Application',
    'debug' => true,
    'env' => 'production',
];
```

**Exemple :** `config/database.php`

```php
<?php

return [
    'default' => 'mysql',
    'connections' => [
        'mysql' => [
            'host' => 'localhost',
            'port' => 3306,
            'user' => 'root',
        ],
    ],
];
```

## Accéder aux Configurations

Utilisez la méthode `get()` pour récupérer une valeur.

```php
// Récupérer tout le tableau de 'app.php'
$appConfig = $config->get('app');

// Récupérer une valeur spécifique
$appName = $config->get('app.name');

// Récupérer une valeur imbriquée (fichier 'database.php')
$dbHost = $config->get('database.connections.mysql.host');

// Valeur par défaut si la clé n'existe pas
$timezone = $config->get('app.timezone', 'UTC');
```

## Helpers de Typage

Pour vous assurer du type de retour, utilisez les méthodes dédiées :

```php
// Retourne un entier (int)
$port = $config->getInt('database.connections.mysql.port', 3306);

// Retourne un booléen (bool)
// Supporte aussi les chaînes "true", "on", "yes", "1"
$debug = $config->getBool('app.debug', false);
```

## Validation

Pour garantir que votre application dispose des configurations nécessaires au démarrage, utilisez `validate()`.

```php
try {
    $config->validate([
        'app.name',
        'database.connections.mysql.host'
    ]);
} catch (\RuntimeException $e) {
    die("Erreur de configuration : " . $e->getMessage());
}
```

## Structure des Dossiers

```text
you-config/
├── src/
│   └── Config.php     # Classe principale de gestion
└── doc/
    └── guide.md       # Ce fichier de documentation
```
