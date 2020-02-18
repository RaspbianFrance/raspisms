.PHONY: all install composer_install move_dir migrate fix

INSTALL_DIR=$(DESTDIR)/opt/raspisms2
ENV=prod


all: install


vendor: composer.phar composer.json
	./composer.phar self-update
	./composer.phar install



migrate: vendor
	vendor/bin/phinx migrate --environment=$(ENV)


install: vendor migrate
	cp -r . $(INSTALL_DIR)


clean:
	rm -rf vendor/
	

uninstall:
	rm -rf .


fix:
	tests/php-cs-fixer/run.php fix
	tests/phpstan/run.php analyse
