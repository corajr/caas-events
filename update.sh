#!/bin/bash

rsync -av ~/Desktop/CAAS/caas-events/scripts/ "$VVV_DIR/www/plugin_trial/scripts/"

rsync -ave ssh ~/Desktop/CAAS/caas-events/scripts/ remeike@remeike.webfactional.com:/home/remeike/webapps/caas/scripts/
