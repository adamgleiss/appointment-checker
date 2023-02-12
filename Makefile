SHELL := /usr/bin/env bash

build-image:
	docker build --no-cache --platform linux/x86_64 -t localphp .

start:
	docker-compose up -d localphp

stop:
	docker-compose stop localphp

login:
	bash -c "clear && docker exec -it localphp bash"

remove-exited-container:
	docker rm `docker ps -q -f status=exited`

remove-all:
	docker ps -a -q | xargs docker stop ; docker system prune --volumes -a -f

clean:
	docker-compose stop ; docker container prune -f ; docker volume prune -f ; docker image prune -f
