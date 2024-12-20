# <span style=" font-family:Monaco, monospace">SkyHiZest - Projet de site e-commerce </span>

<img src="readme_assets/home.png" alt="title" style="width:100%;height:300px;">

![alt text](readme_assets/home.png)

## **<span style="color: #00BF63; font-family:Calibri">Réalisée Par:</span>**
<p style="background-color: yellow; color: black; padding: 2px;">Filière: Licence d'excellence_ADIA_2024/2025</p>

 * Hajar Aafane (Groupe1)
 * Sanaa Assabbane (Groupe1)
 * Zineb Lagrida (Groupe3)


## **<span style="color: #00BF63; font-family:Calibri">Prérequis:</span>**

- PHP 7.4 ou supérieur
- MySQL 5.7 ou supérieur
- WordPress 6.7.1 
- Un serveur local (XAMPP, WAMP, ou équivalent)

## **<span style="color: #00BF63; font-family:Calibri">Installation (Cas Wamp) et Login:</span>**

#### 1. Créer le docier approprié au projet:
* Accéder au docier www de wamp
* Créer un docier my_projects
* Créer un docier adia à l'intérieur de my_projects

<img src="readme_assets/path.png" alt="title" style="height:100%;">

#### 2. Configuration de la base de données:

* Importez la base de données fournie :
Accédez à phpMyAdmin via : http://localhost/phpmyadmin
Créez une nouvelle base de données appelée *skyhighzest_db*.
Importez le fichier SQL fourni dans le dépôt (database/skyhighzest.sql).
2. Configurez WordPress pour utiliser la base de données :
Accédez au fichier wp-config.php situé à la racine du projet.
Modifiez les paramètres suivants avec vos informations locales :

```bash
define('DB_NAME', 'skyhighzest_db');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_HOST', 'localhost');
```

#### 2. Clonez ce dépôt sur votre machine :
   ```bash
   git clone https://github.com/Ha-neu-l/SkyHighZest.git
   ```
#### 3. Installez WordPress en accédant à votre site dans un navigateur : 
- Lien: **http://localhost/my_projects/adia/SkyHighZest/wp-admin/**

<img src="readme_assets/image.png" alt="title" style="width:100%;height:300px;">

Utilisez les identifiants suivants pour vous connecter à l'administration/Tableau de board de WordPress :

* Identifiants Administrateur

        Utilisateur : eqsfp@jzsh
        Mot de passe : 1HgJRrOIedhKUyJgzB


<img src="readme_assets/dash.png" alt="title" style="width:100%;height:300px;">

#### 4. Accéder au site sans connexion au dashboard:
* Utilisez ce lien: http://localhost/my_projects/adia/SkyHighZest

## <span style="color: #00BF63; font-family:Calibri">Support:</span>
Pour toute question ou assistance, veuillez contacter :

Email : hajar.growth@gmail.com
Dépôt GitHub : [SkyHiZest](https://github.com/Ha-neu-l/SkyHighZest.git).



Bonne navigation et utilisation de SkyHighZest 🎉!
