# RateMe
A Symfony prototype with deep back-end features
You should have already php, composer, symfony, yarn, npm, docker, and docker compose installed on your machine.

1. composer install
2. make sure docker is running and starts services with this command: docker-compose up
3. check all 4 services are up with this command : docker-compose ps
4. yarn install
5. npm install
6. symfony run -d yarn encore dev --watch
7. symfony run -d --watch=config,src,templates,vendor symfony console messenger:consume async
8. symfony console security:encode-password. //we use admin as password to encode
9. docker-compose exec database psql -U main 
10. INSERT INTO admin (id, username, roles, password)  VALUES (nextval('admin_id_seq'), 'admin', '["ROLE_ADMIN"]',  '$argon2id$v=19$m=65536,t=4,p=1$BQG+jovPcunctc30xG5PxQ$TiGbx451NKdo+g9vLtfkMy4KjASKSOcnNxjij4gTX1s');
11. SELECT * FROM admin;
12. symfony console doctrine:migrations:migrate
13. you can access to admin dashboard by username: admin and password: admin
14. There are multiple ways to access webmailer like : access through ip address and port provided by docker-compose ps or simply run this command through terminal: symfony open:local:webmail
15. you can access api panel through /api 
16. app supports to languages which are /en and /nl


