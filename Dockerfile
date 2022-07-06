FROM mabang-registry.tencentcloudcr.com/php-project/php-7.4-swoole

# 设置环境
#默认dev
ARG environment=dev
ARG SERVICENAME=mabang-arch-demo
ARG APP_DIR=service-main
ENV APP_PROFILE=$environment
USER root
COPY ./php.ini /usr/local/php/etc/
RUN mkdir -p /data/web/website/$SERVICENAME
WORKDIR /data/web/website/$SERVICENAME
ADD . /data/web/website/$SERVICENAME
RUN cd /data/web/website/$SERVICENAME/$APP_DIR
EXPOSE 8080
EXPOSE 8081
CMD ./run.sh $APP_PROFILE