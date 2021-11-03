.PHONY all

all: backend

backend: cache composer

cache:
    rm -rf temp/cache

    composer:
        composer clear
        composer install