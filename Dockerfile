FROM debian:bullseye-slim



#### DEFINING VARS
ARG php_version=8.0

#### SYSTEM OPERATIONS
RUN apt-get update

RUN apt-get install -y -qq --force-yes \
    lsb-base \
    procps \
    cron \
        --no-install-recommends > /dev/null

#### PHP OPERATIONS
RUN apt-get install -y -qq --force-yes \
    curl \
    wget \
    gnupg2 \
    ca-certificates \
    apt-transport-https \
    software-properties-common \
        --no-install-recommends > /dev/null

RUN echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | tee /etc/apt/sources.list.d/sury-php.list
RUN wget -qO - https://packages.sury.org/php/apt.gpg | apt-key add -

RUN apt-get update

RUN apt-get install -y -qq --force-yes \
    php${php_version}-cli \
    php-redis \
#	php-curl \
	--no-install-recommends > /dev/null

#### APP OPERATIONS
# Installing temporary packages
RUN apt-get install -y -qq --force-yes \
	composer \ 
	git \ 
	zip \ 
	unzip \ 
	php${php_version}-zip \
	--no-install-recommends > /dev/null

# Creating a temporary folder for our app
RUN mkdir -p /tmp/app

# Download the entire project
COPY . /tmp/app/

# Create needed folders for composer autoloader optimization
RUN mkdir -p /app

# Defining which packages Composer will install
RUN cp /tmp/app/composer.lock /app/composer.lock
RUN cp /tmp/app/composer.json /app/composer.json

# Please, Composer, install them
RUN composer install -d /app --no-dev --no-scripts

# Moving app to the right place
RUN cp -r /tmp/app/* /app
RUN rm -rf /tmp/app
RUN touch /app/.env

# Setting the configurations values for app
RUN cd /app && composer dump-autoload

# Deleting system temporary packages
RUN apt-get purge -y -qq --force-yes \
	composer \ 
	git \ 
	zip \ 
	unzip \ 
	php${php_version}-zip \
	> /dev/null

# Cleaning the system
RUN apt-get -y -qq --force-yes autoremove > /dev/null

# Changing permissions of the entire app
RUN chown root:root -R /app
RUN find /app -type f -exec chmod 644 {} \;
RUN find /app -type d -exec chmod 755 {} \;

# Crafting the entrypoint script
RUN rm -rf /entrypoint.sh && touch /entrypoint.sh
RUN echo "#!/bin/bash" >> /entrypoint.sh
# RUN echo "service cron start" >> /entrypoint.sh
RUN echo "shopt -s dotglob" >> /entrypoint.sh
# RUN echo "(crontab -l; echo '* * * * * cd /var/www && php artisan schedule:run >> /dev/null 2>&1';) | crontab -" >> /entrypoint.sh
# RUN echo "touch /etc/crontab /etc/cron.*/*" >> /entrypoint.sh
# RUN echo 'exec "$@"' >> /entrypoint.sh
# RUN echo "php -f /app/redistalker.php" >> /entrypoint.sh
RUN echo "/bin/bash" >> /entrypoint.sh

# Giving permissions to the entrypoint script
RUN chown root:root /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Giving permissions to the livenessprobe script
# RUN chown root:root /app/runtime/livenessprobe.sh
# RUN chmod +x /app/runtime/livenessprobe.sh

# Gaining a bit of comfort
WORKDIR "/app"

# Executing the scripts
ENTRYPOINT ["/entrypoint.sh"]
