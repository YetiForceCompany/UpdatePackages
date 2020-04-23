#########################################
# Installation dependency
#########################################
cd "$(dirname "$0")/../../"
echo " -----  Install yarn for public_html directory (mode $INSTALL_MODE) -----"
if [ ${INSTALL_MODE} = "DEV" ]; then
    yarn install --force --modules-folder "./public_html/libraries"
	yarn list
else
    yarn install --force --modules-folder "./public_html/libraries" --production=true --ignore-optional
fi


echo " -----  Install yarn for public_html directory (mode $INSTALL_MODE) -----"
cd public_html/src
if [ ${INSTALL_MODE} = "DEV" ]; then
    yarn install --force
	yarn list
else
   yarn install --force --production=true --ignore-optional
fi
cd ../../

echo " -----  Install composer -----"
if [ ${INSTALL_MODE} = "DEV" ]; then
	rm -rf composer.json
	rm -rf composer.lock
	mv composer_dev.json composer.json
	mv composer_dev.lock composer.lock
	composer install --no-interaction --no-interaction
else
	composer install --no-interaction --no-dev --no-interaction
fi
