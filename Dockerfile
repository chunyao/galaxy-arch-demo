FROM mabang-registry.tencentcloudcr.com/php-project/php-7.4-swoole

# 设置环境
#默认dev
ARG environment=dev
ARG SERVICENAME=mabang-arch-service
ARG PROJ=mabang-arch-service
ENV APP_PROFILE=$environment
USER root
COPY ./php.ini /usr/local/php/etc/
RUN mkdir -p /data/web/website/$PROJ
WORKDIR /data/web/website/$PROJ
ADD . /data/web/website/$PROJ
RUN cd /data/web/website/$PROJ/$SERVICENAME
EXPOSE 8080
EXPOSE 8081
CMD ./run.sh $APP_PROFILE