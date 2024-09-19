
### Comment contribuer à ce projet 🚀

1. **Cloner le projet** :

Commencez par cloner le projet GitHub sur votre machine locale en utilisant la commande git clone :

```
git clone https://github.com/AlxFrst/ocp8-TodoList.git
```

2. **Créer une branche** 🌿 :

Avant de commencer à travailler, créez une nouvelle branche pour votre contribution en utilisant une convention de nommage appropriée. Par exemple, si vous travaillez sur une nouvelle fonctionnalité, nommez votre branche de manière descriptive.

```
git checkout -b nouvelle-fonctionnalité
```

3. **Faites vos modifications** ✏️ :

Apportez vos changements au code en veillant à respecter les normes de codage du projet et en ajoutant des commentaires pour rendre votre travail plus facile à comprendre.

4. **Commit et Push** 💾 :

Une fois vos modifications terminées, ajoutez vos fichiers modifiés à l'index avec la commande `git add`, puis effectuez un commit avec un message descriptif pour expliquer vos changements.

```
git add .
git commit -m "Description des modifications apportées"
```

Ensuite, poussez votre branche vers le dépôt distant sur GitHub.

```
git push origin nouvelle-fonctionnalité
```

5. **Écrire des tests et vérifier avec Codacy** 🧪 :

Écrivez des tests dans le dossier `test` pour votre code et assurez-vous qu'il a au moins 70% de couverture avec `phpunit`. Vérifiez que votre code obtient au minimum une note B dans la notation Codacy.

Ajoutez à votre fichier `.env.test` cette ligne :
```
    DATABASE_URL="mysql://root:@127.0.0.1:3306/todolist-test?serverVersion=5.7.36&charset=utf8mb4"
```

Créez votre base de données de test :
```
    symfony console d:d:c --env=test
    symfony console d:s:c --env=test
```

Créez et chargez les fixtures dans la base de test :
```
    symfony console d:f:l --env=test
```

Lancez la session de test sur la base de test avec la commande suivante :
```
    php bin/phpunit --coverage-html web/test-coverage
```

6. **Ouvrir une Pull Request (PR)** 📬 :

Rendez-vous sur la page GitHub du projet et ouvrez une nouvelle Pull Request à partir de votre branche vers la branche principale du projet. Décrivez vos modifications et ajoutez des détails pertinents pour aider les relecteurs à comprendre votre contribution.

7. **Révision et retours** 🧐 :

Les collaborateurs du projet examineront votre Pull Request, poseront éventuellement des questions ou demanderont des modifications supplémentaires. Assurez-vous de répondre aux commentaires et de mettre à jour votre PR en conséquence.

8. **Fusion de votre Pull Request** 🔄 :

Une fois votre Pull Request approuvée, notre équipe fusionnera votre branche avec la branche principale. Vérifiez que tous les changements ont été correctement intégrés.

9. **Nettoyage** 🧹 :

Une fois la fusion terminée, vous pouvez supprimer votre branche locale et distante si nécessaire.

```
git checkout main
git branch -d nouvelle-fonctionnalité
git push origin --delete nouvelle-fonctionnalité
```

Respectez ce processus de qualité pour garantir l'efficacité du code. ✅
