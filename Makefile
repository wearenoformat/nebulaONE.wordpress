SHELL=/bin/bash

help: ## Display the help menu.
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

start: ## Default Start target
	@wp-env start

stop: ## Default Stop Target
	@wp-env stop

reset: ## Default Reset Target
	@wp-env reset

clean: ## Default Clean Target
	@rm -rf ./build

build: ## Default Build Target
	@sh ./build.sh