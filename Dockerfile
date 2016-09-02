## BUILDING
##   (from project root directory)
##   $ docker build -t php-for-alvacorp-newscoop .
##
## RUNNING
##   $ docker run -p 9000:9000 php-for-alvacorp-newscoop
##
## CONNECTING
##   Lookup the IP of your active docker host using:
##     $ docker-machine ip $(docker-machine active)
##   Connect to the container at DOCKER_IP:9000
##     replacing DOCKER_IP for the IP of your active docker host

FROM gcr.io/stacksmith-images/debian-buildpack:wheezy-r9

MAINTAINER Bitnami <containers@bitnami.com>

ENV STACKSMITH_STACK_ID="zu2443j" \
    STACKSMITH_STACK_NAME="PHP for AlvaCorp/Newscoop" \
    STACKSMITH_STACK_PRIVATE="1"

RUN bitnami-pkg install php-5.6.25-0 --checksum f0e8d07d155abdb5d6843931d3ffbf9b4208fff248c409444fdd5a8e3a3da01d

ENV PATH=/opt/bitnami/php/bin:$PATH

## STACKSMITH-END: Modifications below this line will be unchanged when regenerating

# CodeIgniter template
COPY . /app
WORKDIR /app

EXPOSE 9000
CMD ["php", "-S", "0.0.0.0:9000"]
