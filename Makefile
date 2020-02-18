.PHONY: all install composer_install vendor migrate clean fix

INSTALL_DIR=$(DESTDIR)/opt/raspisms2
ENV=prod


all: install


vendor: composer.phar composer.json
	raspisms/composer.phar self-update
	raspisms/composer.phar install



migrate: vendor
	raspisms/vendor/bin/phinx migrate --environment=$(ENV)


install: vendor migrate
	chmod -R 750 .
	install -m750 -d $(INSTALL_DIR)
	install -d ./src $(INSTALL_DIR)



clean:
	rm -rf vendor/
	

uninstall:
	rm -rf $(INSTALL_DIR)


fix:
	raspisms/tests/php-cs-fixer/run.php fix
	raspisms/tests/phpstan/run.php analyse
