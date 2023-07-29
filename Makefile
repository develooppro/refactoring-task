install:
	composer install

# todo: init command with bash script with api key input

run-initial:
	php ./public/app-0.php ./public/input.txt

run:
	php ./public/app.php ./public/input.txt

tests:
	php ./vendor/bin/phpunit tests