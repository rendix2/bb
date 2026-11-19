all: dirs-build git php js db

guard-not-production:
	@if [ -f /.docker-production-build ]; then \
		echo "Refusing to run: this is the production image (/.docker-production-build exists)."; \
		echo "Migrations, fixtures, and schema drop/recreate must never run this way in production."; \
		exit 1; \
	fi

git:
	git config --global --add safe.directory /var/www/html

php: composer.json composer.lock
	#php bin/console nette:cache:purge
	composer validate
	composer install

js: package.json package-lock.json
	npm install
	npm run build

db: guard-not-production
	php bin/console database:create
	php bin/console orm:schema-tool:drop --force --full-database
	php bin/console nette:cache:purge
	php bin/console migrations:migrate --no-interaction
	php bin/console doctrine:fixtures:load --no-interaction

dirs-build: create-dirs chmod-dirs

create-dirs:
	rm temp -r -f
	rm vendor -r -f
	rm node_modules -r -f

	mkdir -p log backup temp

	mkdir -p temp/web
	mkdir -p temp/web/cache
	mkdir -p temp/web/mails

	mkdir -p temp/console
	mkdir -p temp/console/cache

	mkdir -p log/web
	mkdir -p log/console

	mkdir -p www/avatars

chmod-dirs:
	chmod 777 temp -R

	chmod 777 temp/web/cache
	chmod 777 temp/web/mails

	chmod 777 temp/console/cache

	chmod 777 log -R

	chmod 777 log/web
	chmod 777 log/console

migration:
	php bin/console migrations:generate
	chmod 777 /var/www/html/app/Database/Migrations -R

migrate:
	php bin/console migrations:migrate --no-interaction

update:
	git config --global --add safe.directory /var/www/html
	composer update
	npm update
	npm run build

phpstan:
	php vendor/bin/phpstan analyse -vvv --memory-limit 2048M

rector:
	vendor/bin/rector

# --- tests (Codeception, same setup as web-project; Functional/Acceptance need the running web container, Acceptance also selenium-chrome) ---
# no dirs-build here: it deletes vendor, node_modules and temp
test-build:
	php vendor/bin/codecept clean
	php vendor/bin/codecept build

# known-bug = failing on purpose until fixed (like alfred), not regressions
# Unit = no container, no database (tests/Unit.suite.yml); Integration = the DI container + the database, no web server
unit: test-build
	php vendor/bin/codecept run --steps -v --skip-group known-bug Unit

integration: test-build
	php vendor/bin/codecept run --steps -v --skip-group known-bug Integration

functional: test-build
	php vendor/bin/codecept run --steps -v --skip-group known-bug Functional

acceptance: test-build
	php vendor/bin/codecept run --steps -v --skip-group known-bug Acceptance

# the suites cheapest first; stops at the first failing one
test: test-build
	php vendor/bin/codecept run --steps -v --skip-group known-bug Unit
	php vendor/bin/codecept run --steps -v --skip-group known-bug Integration
	php vendor/bin/codecept run --steps -v --skip-group known-bug Functional
	php vendor/bin/codecept run --steps -v --skip-group known-bug Acceptance

# line coverage of app/ (codeception.yml `coverage:`): pcov is in the dev image only and off by default
coverage: test-build
	php -d pcov.enabled=1 vendor/bin/codecept run --coverage --coverage-html --coverage-text

# contributte/scheduler (config/scheduler.neon), run inside the container (opt-in, nothing starts it on its own):
# scheduler-run = the due jobs once (what a cron of the host runs every minute), scheduler-loop = every minute until Ctrl+C
scheduler-run:
	mkdir -p temp/console/scheduler
	php bin/console scheduler:run

scheduler-loop:
	mkdir -p temp/console/scheduler
	while true; do php bin/console scheduler:run; sleep 60; done
