# 📝 ToDoList

[![Codacy Badge](https://app.codacy.com/project/badge/Grade/b1904ca3050a42f2b3f1e7e7158ebb75)](https://app.codacy.com/gh/AlxFrst/ocp8-TodoList/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_grade)

## 🚀 Installation

1. Téléchargez le projet directement ou utilisez la commande git clone :

```
git clone https://github.com/AlxFrst/ocp8-TodoList
```

2. À la racine du projet, installez les dépendances via Composer :

```
composer install
```

## 🗄️ Configuration de la base de données

3. Modifiez le fichier `.env` à la racine du projet avec vos informations de base de données :

```
DATABASE_URL=mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=5.7
```

4. Avec le CLI Symfony, exécutez les commandes suivantes :

- Créez la base de données :
```
symfony console doctrine:database:create
```
- Lancez la migration :
```
symfony console doctrine:migrations:migrate
```
- Ajoutez des données factices :
```
symfony console doctrine:fixtures:load
```

> 💡 Sans le client Symfony, remplacez `symfony console` par `php bin/console`.

## 🌐 Lancement du serveur

5. Démarrez le serveur :

```
symfony serve
```

6. 🎉 Accédez à l'application via l'adresse de votre serveur web (ex: localhost:8000).