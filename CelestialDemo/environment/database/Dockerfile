# MySQL server for project database

FROM mysql

ADD ./database/my.cnf /etc/database/conf.d/my.cnf
ADD ./database/schema /docker-entrypoint-initdb.d

ENV MYSQL_ROOT_PASSWORD "teapot"
ENV MYSQL_DATABASE "Celestial"
ENV MYSQL_USER "celestial"
ENV MYSQL_PASSWORD "sphere"
