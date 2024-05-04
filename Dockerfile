FROM php:8.1-fpm
WORKDIR /app
RUN apt-get update && apt-get install -y zip git-all
COPY . .

# Setup user from host OS. This is to ensure that things like "composer install" will generate the "vendor/" with
# correct user permissions.
#
# In CLI, use:
#
#   docker build --build-arg USER_ID=$(id -u ${USER}) --build-arg USER_NAME="$USER" --build-arg GROUP_ID=$(id -g ${USER}) --build-arg GROUP_NAME="$(id -gn)" -t my-image .
#
#   docker run --user $(id -u ${USER}):$(id -g ${USER}) -it my-image /bin/bash
ARG GROUP_ID
ARG GROUP_NAME
ARG USER_ID
ARG USER_NAME

RUN groupadd -g $GROUP_ID $GROUP_NAME
RUN useradd -g $GROUP_NAME -u $USER_ID -d /app $USER_NAME
RUN mkdir -p /app/.composer
RUN chown -R $USER_NAME:$GROUP_NAME /app

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

