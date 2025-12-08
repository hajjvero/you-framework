# You Framework

Ce projet est un framework PHP organisé en monorepo.

## Structure du Monorepo

L'architecture est composée de plusieurs bibliothèques indépendantes situées à la racine du projet. Toutes les dépendances sont gérées globalement par le `composer.json` racine.

### Schéma des Dossiers

```text
/
├── composer.json           # Configuration globale et autoloading PSR-4
├── public/                 # Dossier public (point d'entrée web)
├── you-cli/                # Composant ligne de commande
├── you-http-foundation/    # Abstraction HTTP (Request, Response)
├── you-kernel/             # Noyau de l'application
├── you-route/              # Système de routage
└── ...
```

## Règles de Nommage

Afin de maintenir la cohérence du projet, merci de respecter les conventions suivantes :

*   **Dossiers de bibliothèque** : `kebab-case` (minuscules avec tirets).
    *   Exemple : `you-event-dispatcher`
*   **Namespaces PHP** : `PascalCase` correspondant au nom du dossier transformé.
    *   Exemple : `you-event-dispatcher` devient `YouEventDispatcher`
*   **Classes** : `PascalCase`.

## Ajouter une Nouvelle Bibliothèque

Pour ajouter un nouveau composant au framework :

1.  **Créer le dossier** à la racine du projet :
    ```bash
    mkdir you-nouvelle-lib
    mkdir you-nouvelle-lib/src
    ```

2.  **Déclarer l'espace de nom** dans le fichier `composer.json` à la racine, sous la section `autoload.psr-4` :
    ```json
    "autoload": {
        "psr-4": {
            "YouRoute\\": "you-route/src/",
            "YouHttpFoundation\\": "you-http-foundation/src/",
            // ...
            "YouNouvelleLib\\": "you-nouvelle-lib/src/"
        }
    }
    ```

3.  **Régénérer l'autoloader** pour prendre en compte le nouveau chemin :
    ```bash
    composer dump-autoload
    ```

4.  **Développer** vos classes dans `you-nouvelle-lib/src/`.
