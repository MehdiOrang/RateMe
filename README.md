# RateMe
A Symfony prototype with deep back-end features
1. composer install
2. make sure docker is running and starts services with docker-compose up
3. check all 4 services are up with this command : docker-compose ps
4. yarn install
5. npm install
6. symfony run -d yarn encore dev --watch
7. symfony run -d --watch=config,src,templates,vendor symfony console messenger:consume async
8. There are multiple ways to access webmailer like : access through ip address and port provided by docker-compose ps or simply run this command through terminal 


