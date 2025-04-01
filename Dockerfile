# docker-compose down && docker-compose up 
# Use the official PHP image with Apache
FROM php:8.2-apache

# Set a non-privileged user
RUN useradd -m -s /bin/bash phpuser && \
    chown -R phpuser:www-data /var/www/html

# Enable required PHP extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Enable Apache mod_rewrite for clean URLs
RUN a2enmod rewrite

# Inject custom Apache configuration
RUN echo '<Directory /var/www/html>\n\
    Options -Indexes\n\
    AllowOverride All\n\
    Require all granted\n\
    </Directory>\n\
    ServerSignature Off\n\
    ErrorLog ${APACHE_LOG_DIR}/error.log\n\
    CustomLog ${APACHE_LOG_DIR}/access.log combined\n\
    ServerTokens Prod' > /etc/apache2/sites-available/000-default.conf

# Set working directory
WORKDIR /var/www/html

# Copy project files to the container
COPY . /var/www/html

# allow port binding to port 80
RUN apt-get update && apt-get install -y libcap2-bin && \
    setcap CAP_NET_BIND_SERVICE=+eip /usr/sbin/apache2

RUN apt-get update && apt-get install -y default-mysql-client

# Switch to non-root user for better security
USER phpuser




# Expose port 80
EXPOSE 80

