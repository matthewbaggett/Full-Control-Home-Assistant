all: fix build
.PHONY: fix build php-cs-fixer composer-%
.SILENT: fix build php-cs-fixer composer-%

php-cs-fixer:
	docker \
		run \
			--rm \
			-v $(shell pwd):/data \
			cytopia/php-cs-fixer \
				fix

fix: php-cs-fixer

composer-%:
	docker run --rm --interactive --tty \
    	--user $(shell id -u)\
  		--volume $(shell pwd):/app:delegated \
		--volume $(shell echo $$HOME/.composer):/tmp/.composer:delegated \
		--env COMPOSER_MIRROR_PATH_REPOS=1 \
		--env COMPOSER_HOME=/tmp/.composer \
      	composer \
      		composer $*

build: composer-install
	docker buildx bake test --load
