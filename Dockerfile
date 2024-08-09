# Usar una imagen base de PHP con Apache
FROM php:8.2-apache

# Instalar extensiones de PHP necesarias (por ejemplo, mysqli)
RUN docker-php-ext-install mysqli

# Copiar los archivos del proyecto al contenedor
COPY . /var/www/html/

# Establecer permisos apropiados (opcional)
RUN chown -R www-data:www-data /var/www/html

# Exponer el puerto 80 para Apache
EXPOSE 80






