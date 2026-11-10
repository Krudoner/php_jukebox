# Segunda etapa: PHP 8.5 con Apache y los controladores PDO para PostgreSQL y MariaDB.
# El contexto de construcción es la raíz del proyecto (build.context: ..),
# por eso las rutas de COPY empiezan por docker/.
FROM php:8.5-apache-bookworm

# libpq-dev es necesaria para compilar pdo_pgsql. PDO ya viene incluido en la imagen.
RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev \
    && docker-php-ext-install pdo_pgsql pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

# Sitio del jukebox y nombre global del servidor.
COPY docker/apache/jukebox.conf /etc/apache2/sites-available/jukebox.conf
COPY docker/apache/jukebox-servername.conf /etc/apache2/conf-available/jukebox-servername.conf

# a2ensite activa el sitio; a2enconf activa la configuración general.
RUN a2dissite 000-default \
    && a2ensite jukebox \
    && a2enconf jukebox-servername
