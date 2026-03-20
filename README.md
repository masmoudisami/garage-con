# Garage-v3


8️⃣ Déploiement sur un nouveau serveur

git clone https://github.com/masmoudisami/garage-con.git
cd garage-con
docker compose up -d --build

////////////

import fichier sql (si base n'a pas ete importée automatiquement)

1️⃣ Ouvre :

http://localhost:8081 et attendez un peu de temps

2️⃣ Connecte-toi :
user : root
password : (voir .env) 
 
3️⃣ Clique sur la base : mechanic_db
4️⃣ Onglet Import
5️⃣ Importer ton fichier : mechanic_db.sql

Application accessiblr via : http://localhost:8080

lors de la première connexion supprimer le user depuis phpmyadmin

///////////

7️⃣ Sauvegarde de la base (très important)

Backup manuel :

sudo docker exec mysql_db mysqldump -u sami -pSm/131301 --no-tablespaces mechanic_db > backup_mechanic_db.sql


-pour supprimer le conteneur:

docker stop $(docker ps -aq)

docker rm $(docker ps -aq)

docker rmi $(docker images -q)

-pour actualiser 

docker compose down -v

docker compose up -d










