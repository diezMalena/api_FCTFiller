#!/bin/bash
# Este script despliega la base de datos, migrando las tablas e inicializando los datos básicos
# Migraciones:
php artisan migrate:refresh --path=database/migrations/2022_01_19_083043_create_rol_empresa.php
php artisan migrate:refresh --path=database/migrations/2022_01_19_083053_create_rol_profesor.php
php artisan migrate:refresh --path=database/migrations/2022_01_19_082914_create_empresa.php
php artisan migrate:refresh --path=database/migrations/2022_01_19_083145_create_trabajador.php
php artisan migrate:refresh --path=database/migrations/2022_01_19_083135_create_rol_trabajador_asignado.php
php artisan migrate:refresh --path=database/migrations/2022_01_19_082942_create_centro_estudios.php
php artisan migrate:refresh --path=database/migrations/2022_01_19_083033_create_profesor.php
php artisan migrate:refresh --path=database/migrations/2022_01_19_083105_create_rol_profesor_asignado.php
php artisan migrate:refresh --path=database/migrations/2022_01_25_123542_create_aux_convenio.php
php artisan migrate:refresh --path=database/migrations/2022_01_19_083005_create_convenio.php
php artisan migrate:refresh --path=database/migrations/2022_02_01_185127_create_familia_profesional.php
php artisan migrate:refresh --path=database/migrations/2022_02_01_190818_create_nivel_estudios.php
php artisan migrate:refresh --path=database/migrations/2022_01_19_082907_create_grupo.php
php artisan migrate:refresh --path=database/migrations/2022_01_19_082839_create_oferta_grupo.php
php artisan migrate:refresh --path=database/migrations/2022_01_19_082623_create_alumno.php
php artisan migrate:refresh --path=database/migrations/2014_10_12_000000_create_users_table.php
php artisan migrate:refresh --path=database/migrations/2022_01_19_082806_create_matricula.php
php artisan migrate:refresh --path=database/migrations/2022_02_02_002148_create_tutoria_table.php
php artisan migrate:refresh --path=database/migrations/2022_01_25_214346_create_fct.php
php artisan migrate:refresh --path=database/migrations/2022_01_25_214558_create_empresa_grupo.php
php artisan migrate:refresh --path=database/migrations/2022_02_03_084828_create_aux_curso_academico.php
php artisan migrate:refresh --path=database/migrations/2022_02_03_181552_create_seguimiento.php
php artisan migrate:refresh --path=database/migrations/2022_02_08_115433_create_grupo_familia.php
php artisan migrate:refresh --path=database/migrations/2022_03_27_094601_create_gasto.php
php artisan migrate:refresh --path=database/migrations/2022_03_27_095436_create_factura_manutencion.php
php artisan migrate:refresh --path=database/migrations/2022_03_27_095450_create_factura_transporte.php
php artisan migrate:refresh --path=database/migrations/2022_03_15_090910_anexo.php
php artisan migrate:refresh --path=database/migrations/2022_04_06_172155_create_semanas.php
php artisan migrate:refresh --path=database/migrations/2022_05_24_172047_create_notificaciones.php
php artisan migrate:refresh --path=database/migrations/2014_10_12_100000_create_password_resets_table.php
php artisan migrate:refresh --path=database/migrations/2019_08_19_000000_create_failed_jobs_table.php
php artisan migrate:refresh --path=database/migrations/2019_12_14_000001_create_personal_access_tokens_table.php
php artisan migrate:refresh --path=database/migrations/2022_04_26_174643_create_cuestionarios_table.php
php artisan migrate:refresh --path=database/migrations/2022_04_26_180141_create_preguntas_cuestionario_table.php
php artisan migrate:refresh --path=database/migrations/2022_05_10_181249_create_cuestionario_respondidos_table.php
php artisan migrate:refresh --path=database/migrations/2022_05_10_182019_create_preguntas_respondidas_table.php
php artisan migrate:refresh --path=database/migrations/2022_05_10_182019_create_preguntas_respondidas_table.php
php artisan migrate:refresh --path=database/migrations/2022_05_26_170253_update_cuestionarios.php
php artisan migrate:refresh --path=database/migrations/2022_05_26_171659_update_cuestionario_respondidos.php
php artisan migrate:refresh --path=database/migrations/2022_06_01_183938_update_cuestionario_activo.php
php artisan migrate:refresh --path=database/migrations/2022_06_16_181821_create_ciudad.php
php artisan migrate

# Seeders:
# Tablas de roles
php artisan db:seed --class=RolesEstudioSeeder
php artisan db:seed --class=RolesEmpresaSeeder

# Tablas de grupos, familias profesionales, y niveles de estudios
php artisan db:seed --class=NivelEstudiosSeeder
php artisan db:seed --class=FamiliaProfesionalSeeder
php artisan db:seed --class=GrupoSeeder
php artisan db:seed --class=GrupoFamiliaSeeder

# Tabla de centro de estudios
php artisan db:seed --class=CentroEstudiosSeeder
php artisan db:seed --class=EmpresaSeeder

# Tabla de ciudades
php artisan db:seed --class=CiudadSeeder

php artisan passport:install
