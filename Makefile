.PHONY: all install composer_install move_dir migrate

INSTALL_DIR=$(DESTDIR)/opt/raspisms2
ENV=prod


all: install


vendor: composer.phar composer.json
	./composer.phar self-update
	./composer.phar validate
	./composer.phar install


migrate: vendor
	vendor/bin/phinx migrate --environment=$(ENV)


move_dir:
	mv . $(INSTALL_DIR)
	cd $(INSTALL_DIR)


install: move_dir vendor migrate
		

clean:
	rm -rf vendor/
	

uninstall:
	rm -rf .
