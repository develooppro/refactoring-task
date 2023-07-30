init:
	composer install
	./bin/init.sh

run-wrong:
	php ./public/app-0.php ./public/input.txt

run:
	php ./public/app.php ./public/input.txt

run-tests:
	php ./vendor/bin/phpunit tests