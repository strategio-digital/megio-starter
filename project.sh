#!/bin/bash

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

help() {
  echo -e "${YELLOW}COMMANDS:"
  echo -e "${GREEN}./project.sh app"
  echo -e "./project.sh serve --stop-others"
  echo -e "${NC}"
}

if test "$1" = "app"; then
  docker-compose exec app bash
elif test "$1" = "serve"; then
  if test "$2" = "--stop-others"; then
    docker stop $(docker ps -a -q)
  fi
  docker-compose -f docker-compose.yml up -d
elif test "$1" = "mvc"; then
  cd vendor/strategio/saas
  git init
  git remote add origin git@github.com:strategio-digital/saas.git
  git pull
  git checkout master --force
  cd ../../../
  echo -e "${GREEN}Git successfully initialized for contribution${NC}"
else
  help
fi
