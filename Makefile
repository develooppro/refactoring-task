init-dev:
	composer install --dev
	./bin/init.sh

run-wrong:
	php ./public/app-0.php ./public/input.txt

run:
	php ./public/app.php ./public/input.txt

run-tests:
	php ./vendor/bin/phpunit tests

phpstan:
	php ./vendor/bin/phpstan analyse -l 8 src tests public/app.php