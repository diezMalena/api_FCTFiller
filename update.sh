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
        composer install -n
else
        cd $RUTA_APLICACION
        git clone -b ${RAMA} --single-branch https://github.com/diezMalena/api_FCTFiller
        echo "Repositorio de servidor descargado. Asegúrese de configurarlo adecuadamente. Puede consultar las instrucciones en la Wiki de la aplicación"
        cd $RUTA_API
        composer install -n
        cp .env.example .env
fi
php artisan key:generate -n

if [ -d $RUTA_CLIENTE ]; then
        cd $RUTA_CLIENTE
        git restore .
        git checkout ${RAMA}
        git pull
        echo "Repositorio de cliente actualizado"
else
        cd $RUTA_APLICACION
        git clone -b ${RAMA} --single-branch https://github.com/diezMalena/cliente_FCTFiller
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

rm ${RUTA_APLICACION}/update.sh --verbose && cp ${RUTA_API}/update.sh ${RUTA_APLICACION}/update.sh --verbose
