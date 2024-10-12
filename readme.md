# Social Network Project

## Description
Le projet **Social Network** est une application web permettant aux utilisateurs de se connecter, de partager des messages, des photos et de suivre les activités de leurs amis. L'objectif est de créer une plateforme interactive et conviviale pour favoriser les interactions sociales en ligne.

## Requirements
- PHP 8
- Composer
- Symfony CLI
- MySQL

## Fonctionnalités
- Inscription et connexion des utilisateurs
- Création et modification de profils
- Publication de messages et de photos
- Commentaires et likes sur les publications
- Système de suivi des amis
- Notifications en temps réel

## Installation
1. Clonez le dépôt
2. Accédez au répertoire du projet :
    ```bash
    cd social-network
    ```
3. Installez les dépendances :
    ```bash
    bin/console asset-map:compile
    ```
4. Démarrez l'application :
    ```bash
    symfony serve -d
    ```
N'oubliez pas de configurer votre base de données dans le fichier `.env`, créer la base de données et jouer les migrations.

## Technologies utilisées
- Frontend : JS, HTML, CSS
- Backend : Symfony, PHP
- Authentification : Authentifier avec Symfony

## Contribuer
Les contributions sont les bienvenues ! Veuillez soumettre une pull request ou ouvrir une issue pour discuter des changements que vous souhaitez apporter.

## Licence
Ce projet est sous licence MIT. Voir le fichier [LICENSE](LICENSE) pour plus de détails.

## Auteurs
- [Jessica Kuijer](https://github.com/jessicakuijer)
