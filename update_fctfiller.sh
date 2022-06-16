#!/bin/bash
#Este programa actualiza los repositorios de la aplicación
#Si no existe la carpeta, clona el repositorio. Si existe, actualiza los cambios
RUTA_APLICACION="/var/www/html/fctfiller"
RUTA_API="${RUTA_APLICACION}/api_FCTFiller"
RUTA_CLIENTE="${RUTA_APLICACION}/cliente_FCTFiller"
RAMA="pre-produccion"

if [ -d $RUTA_API ]; then
        cd $RUTA_API
        git restore .
        git checkout ${RAMA}
        git pull
        echo "Repositorio de servidor actualizado"
        composer install
else
        cd $RUTA_APLICACION
        git clone -b ${RAMA} --single-branch https://DaniJCoello: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@github.com/diezMalena/api_FCTFiller
        echo "Repositorio de servidor descargado. Asegúrese de configurarlo adecuadamente. Puede consultar las instrucciones en la Wiki de la aplicación"
        cd $RUTA_API
        composer install
        cp .env.example .env
        php artisan passport:install
        php artisan vendor:publish --tag=passport-config
fi
php artisan key:generate

if [ -d $RUTA_CLIENTE ]; then
        cd $RUTA_CLIENTE
        git restore .
        git checkout ${RAMA}
        git pull
        echo "Repositorio de cliente actualizado"
else
        cd $RUTA_APLICACION
        git clone -b ${RAMA} --single-branch https://DaniJCoello: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@github.com/diezMalena/cliente_FCTFiller
        echo "Repositorio de cliente descargado. Asegúrese de configurarlo adecuadamente. Puede consultar las instrucciones en la Wiki de la aplicación"
        cd $RUTA_CLIENTE
fi
npm install --force
npm install
npm audit --fix
ng build

chmod 775 ${RUTA_APLICACION}/* -R
chgrp www-data ${RUTA_APLICACION}/* -R
chown fctfiller ${RUTA_APLICACION}/* -R
