.PHONY: all install composer_install vendor migrate clean fix

INSTALL_DIR=$(DESTDIR)/opt/raspisms2
ENV=prod


all: install


vendor: raspisms/composer.phar raspisms/composer.json
	cd raspisms && ./composer.phar self-update
	cd raspisms && ./composer.phar install



migrate: vendor
	cd raspisms && ./vendor/bin/phinx migrate --environment=$(ENV)


install: vendor migrate
	chmod -R 750 .
	install -m750 -d $(INSTALL_DIR) 
	cp -Tr raspisms $(INSTALL_DIR)



clean:
	

uninstall:
	rm -rf $(INSTALL_DIR)


fix:
	cd raspisms && ./tests/php-cs-fixer/run.php fix
	cd raspisms && ./tests/phpstan/run.php analyse
