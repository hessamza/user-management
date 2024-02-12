echo "copy .env docker"
touch .env
cp -r .env.example .env

echo "docker build & up"
docker-compose up -d
echo "docker is up"


echo "install app"
docker exec user-management-php composer install