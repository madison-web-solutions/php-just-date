# Run the test suite, PHPStan and Pint inside Docker, under any supported PHP version.
#
#   make test                 run tests under the default PHP version
#   make test PHP=8.1         run tests under PHP 8.1
#   make test-all             run tests under every supported version
#   make test ARGS="--filter 'DateTest::testAddWorkingDays'"
#
# PHP defaults to the oldest supported version: anything that works there (code,
# or packages installed via composer) is the most likely to work everywhere.
# See also config.platform.php in composer.json.

PHP      ?= 8.1
VERSIONS := 8.1 8.2 8.3 8.4 8.5
ARGS     ?=

# Map a version like 8.4 to its compose service name php84
service = php$(subst .,,$(1))
RUN     = docker compose run --rm $(call service,$(PHP))

# Run a command under every version in VERSIONS, continue past failures, summarise at the end.
# $(1) = make target to invoke per version
define run_all
	@failed=""; \
	for v in $(VERSIONS); do \
		printf '\n\033[1;34m===== PHP %s: %s =====\033[0m\n' "$$v" "$(1)"; \
		$(MAKE) --no-print-directory $(1) PHP=$$v ARGS='$(ARGS)' || failed="$$failed $$v"; \
	done; \
	printf '\n\033[1m===== Summary: %s =====\033[0m\n' "$(1)"; \
	for v in $(VERSIONS); do \
		case " $$failed " in \
			*" $$v "*) printf '  PHP %s  \033[1;31mFAIL\033[0m\n' "$$v" ;; \
			*)         printf '  PHP %s  \033[1;32mOK\033[0m\n'   "$$v" ;; \
		esac; \
	done; \
	test -z "$$failed"
endef

.PHONY: help build install composer test test-all phpstan phpstan-all pint check php shell

help: ## Show this help
	@grep -E '^[a-zA-Z_-]+:.*?## ' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-14s\033[0m %s\n", $$1, $$2}'
	@printf '\nVariables: PHP=%s (default) VERSIONS="%s" ARGS="..."\n' "$(PHP)" "$(VERSIONS)"

build: ## Build the Docker images for every PHP version
	docker compose build

install: ## composer install
	$(RUN) composer install $(ARGS)

composer: ## Run an arbitrary composer command, e.g. make composer ARGS="update"
	$(RUN) composer $(ARGS)

test: ## Run PHPUnit under one PHP version (select with PHP=8.x)
	$(RUN) vendor/bin/phpunit test $(ARGS)

test-all: ## Run PHPUnit under every supported PHP version
	$(call run_all,test)

phpstan: ## Run PHPStan under one PHP version (select with PHP=8.x)
	$(RUN) vendor/bin/phpstan analyse $(ARGS)

phpstan-all: ## Run PHPStan under every supported PHP version
	$(call run_all,phpstan)

pint: ## Run Pint (fixes files; use ARGS=--test to only check)
	$(RUN) vendor/bin/pint $(ARGS)

check: ## Run tests and PHPStan under every version, then Pint in check mode
	$(MAKE) --no-print-directory test-all
	$(MAKE) --no-print-directory phpstan-all
	$(MAKE) --no-print-directory pint ARGS=--test

php: ## Run php with ARGS in the selected PHP version, e.g. make php ARGS="-r 'echo PHP_VERSION;'"
	$(RUN) php $(ARGS)

shell: ## Open a bash shell in the selected PHP version container
	$(RUN) bash
