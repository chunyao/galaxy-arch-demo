FROM mabangerp-docker.pkg.coding.net/public/php-project/php-7.4-swoole:20230410

# 设置环境
#默认dev
ARG SERVICENAME=mabang-arch-demo
ENV ACTIVE_PROFILE=1
ENV NODE_OWN_IP=1
ENV APP_HOST_PORT=1

ENV MONGODB_READ_WRITE_TIMEOUT=60;
ENV MONGODB_URI=mongodb://mabang:mabang123@mongodb-wish.mabangerp.com:27017,mongodb-wish1.mabangerp.com:27017/?replicaSet=mongos&authSource=wishproduct;
ENV MONGODB_CONNECT_TIMEOUT=3

USER root
COPY ./php.ini /usr/local/php/etc/
RUN mkdir -p /data/web/website/$SERVICENAME
ADD . /data/web/website/$SERVICENAME
WORKDIR /data/web/website/$SERVICENAME
EXPOSE 8080
EXPOSE 8081
CMD ./run.sh $ACTIVE_PROFILE $NODE_OWN_IP $APP_HOST_PORT