#!/bin/sh

setfacl -R -m u:www-data:rwX -m u:"$(whoami)":rwX var
setfacl -dR -m u:www-data:rwX -m u:"$(whoami)":rwX var

if [ "$1" = 'run' ]; then
    composer install --no-cache --prefer-dist --no-autoloader --no-scripts --no-progress --quiet
    if [ "$2"  = 'stan' ]; then
    	./vendor/bin/phpstan --memory-limit=-1
		fi

		if [ "$2"  = 'cs-fix' ]; then
			php-cs-fixer fix src
		fi

		if [ "$2"  = 'rector' ]; then
			./vendor/bin/rector process src tests
		fi

		if [ "$2"  = 'clean' ]; then
    	./vendor/bin/phpstan --memory-limit=-1
			php-cs-fixer fix src
			./vendor/bin/rector process src tests
		fi

		if [ "$2"  = 'test' ]; then
			export XDEBUG_MODE=debug APP_ENV=test && php ./vendor/bin/phpunit --configuration ./phpunit.xml.dist
		fi

		if [ "$2"  = 'coverage' ]; then
			export XDEBUG_MODE=coverage && php ./vendor/bin/phpunit --configuration ./phpunit.xml.dist --coverage-text
		fi

else
	exec docker-php-entrypoint "$@"
fi

