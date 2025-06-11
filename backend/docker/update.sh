#!/usr/bin/env bash
cd /source
bin/console cache:clear
bin/console doctrine:schema:update --force