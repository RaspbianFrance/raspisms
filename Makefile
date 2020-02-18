.PHONY: all install composer_install vendor migrate clean fix

INSTALL_DIR=$(DESTDIR)/opt/raspisms2
ENV=prod


all: install


vendor: composer.phar composer.json
	./composer.phar self-update
	./composer.phar install



migrate: vendor
	vendor/bin/phinx migrate --environment=$(ENV)


install: vendor migrate
	chmod -R 750 .
	install -m750 -d $(INSTALL_DIR)
	cp -a . $(INSTALL_DIR)



clean:
	rm -rf vendor/
	

uninstall:
	rm -rf $(INSTALL_DIR)


fix:
	tests/php-cs-fixer/run.php fix
	tests/phpstan/run.php analyse
