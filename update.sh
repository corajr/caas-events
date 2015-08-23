#!/bin/sh

rsync -av ~/Desktop/CAAS/caas-events/scripts/ ~/Development/caas-env/vvv/www/plugin_trial/scripts/

rsync -ave ssh ~/Desktop/CAAS/caas-events/scripts/ remeike@remeike.webfactional.com:/home/remeike/webapps/caas/scripts/
