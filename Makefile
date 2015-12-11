# MAKEFILE
#
# @author      Nicola Asuni <info@tecnick.com>
# @link        https://github.com/tecnickcom/tc-lib-testrest
# ------------------------------------------------------------------------------

# List special make targets that are not associated with files
.PHONY: help all test btest docs phpcs phpcs_test phpcbf phpcbf_test phpmd phpmd_test phpcpd phploc phpdep phpcmpinfo report qa qa_all clean build_dev update server

# Project name
PROJECT=ms-lib-testrest

# Default port number for the test server
PORT?=8081

# Composer executable (work-around to fix the APC bug)
COMPOSER=$(shell which php) -d "apc.enable_cli=0" $(shell which composer)

# --- MAKE TARGETS ---

# Display general help about this command
help:
	@echo ""
	@echo "Welcome to $(PROJECT) make."
	@echo "The following commands are available:"
	@echo ""
	@echo "    make qa          : Run the targets: test, phpcs and phpmd"
	@echo "    make qa_all      : Run the targets: test, phpcs, phpcs_test, phpmd and phpmd_test"
	@echo ""
	@echo "    make test        : Run the PHPUnit tests"
	@echo "    make btest       : Run the Behat tests (behavior test)"
	@echo ""
	@echo "    make phpcs       : Run PHPCS on the source code and show any style violations"
	@echo "    make phpcs_test  : Run PHPCS on the test code and show any style violations"
	@echo ""
	@echo "    make phpcbf      : Run PHPCBF on the source code to fix style violations"
	@echo "    make phpcbf_test : Run PHPCBF on the test code to fix style violations"
	@echo ""
	@echo "    make phpmd       : Run PHP Mess Detector on the source code"
	@echo "    make phpmd_test  : Run PHP Mess Detector on the test code"
	@echo ""
	@echo "    make phpcpd      : Run PHP Copy/Paste Detector"
	@echo "    make phploc      : Run PHPLOC to analyze the structure of the project"
	@echo "    make phpdep      : Run JDepend static analysis and generate graphs"
	@echo "    make phpcmpinfo  : Find out the minimum version and extensions required"
	@echo "    make phpcmpinfo  : Find out the minimum version and extensions required"
	@echo "    make report      : Generate static analysis reports"
	@echo ""
	@echo "    make docs        : Generate source code documentation"
	@echo ""
	@echo "    make server      : Run the test server at http://localhost:"$(PORT)
	@echo ""
	@echo "    make clean       : Delete the vendor directory"
	@echo "    make build_dev   : Clean and download the composer dependencies including dev ones"
	@echo "    make update      : Update composer dependencies"
	@echo ""

# Alias for help target
all: help

# Run the PHPUnit tests
test:
	@mkdir -p ./target/
	./vendor/bin/phpunit test

# Run the Behat tests (behavior test)
btest:
	nohup $(shell which php) -d always_populate_raw_post_data=-1 -t test/server -S localhost:$(PORT) > target/server.log 2>&1 & echo $$! > target/server.pid
	./vendor/bin/behat --config test/behat.yml -f pretty,junit,html --out ,target/behat,target/behat.html ; echo $$? > target/behat.exit; kill -9 `cat target/server.pid` ; exit `cat target/behat.exit`

# generate source code docs
docs:
	@rm -rf target/phpdocs && ./vendor/apigen/apigen/bin/apigen generate --source="src/" --destination="target/phpdocs/" --exclude="vendor" --access-levels="public,protected,private" --charset="UTF-8" --title="${PROJECT}"

# Run PHPCS on the source code and show any style violations
phpcs:
	@./vendor/bin/phpcs --ignore="./vendor/" --standard=psr2 src

# Run PHPCS on the test code and show any style violations
phpcs_test:
	@./vendor/bin/phpcs --standard=phpcs_test.xml test

# Run PHP Mess Detector on the source code
phpmd:
	@./vendor/bin/phpmd src text codesize,unusedcode,naming,design --exclude vendor

# Run PHP Mess Detector on the test code
phpmd_test:
	@./vendor/bin/phpmd test text unusedcode,naming,design

# run PHPCBF on the source code and show any style violations
phpcbf:
	@./vendor/bin/phpcbf --ignore="./vendor/" --standard=psr2 src

# run PHPCBF on the test code and show any style violations
phpcbf_test:
	@./vendor/bin/phpcbf --standard=psr2 tests

# Run PHP Copy/Paste Detector
phpcpd:
	@mkdir -p ./target/report/
	@./vendor/bin/phpcpd src --exclude vendor > ./target/report/phpcpd.txt

# Run PHPLOC to analyze the structure of the project
phploc:
	@mkdir -p ./target/report/
	@./vendor/bin/phploc src --exclude vendor > ./target/report/phploc.txt

# PHP static analysis
phpdep:
	@mkdir -p ./target/report/
	@./vendor/bin/pdepend --jdepend-xml=./target/report/dependencies.xml --summary-xml=./target/report/metrics.xml --jdepend-chart=./target/report/dependecies.svg --overview-pyramid=./target/report/overview-pyramid.svg --ignore=vendor ./src

# parse any data source to find out the minimum version and extensions required for it to run
phpcmpinfo:
	@mkdir -p ./target/report/
	COMPATINFO=phpcompatinfo.json ./vendor/bartlett/php-compatinfo/bin/phpcompatinfo --no-ansi analyser:run --alias source > ./target/report/phpcompatinfo.txt

# generates static code analysis reports
report: phploc phpdep phpcmpinfo

# Alias to run targets: test, phpcs and phpmd
qa: test btest phpcs phpmd phpcpd

# Alias to run targets: qa, phpcs_test, phpmd_test
qa_all: qa phpcs_test phpmd_test
# Run the development server

server:
	$(shell which php) -d always_populate_raw_post_data=-1 -t test/server -S localhost:$(PORT)

# Delete the vendor directory
clean:
	rm -rf ./vendor/

# Clean and download the composer dependencies including dev ones
build_dev:
	rm -rf ./vendor/ && ($(COMPOSER) install --no-interaction)

# Update composer dependencies
update:
	($(COMPOSER) update --no-interaction)
