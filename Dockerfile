FROM mabang-registry.tencentcloudcr.com/php-project/php-7.4-swoole

# 设置环境
#默认dev
ARG SERVICENAME=mabang-arch-demo
ENV ACTIVE_PROFILE=1
USER root
COPY ./php.ini /usr/local/php/etc/
RUN mkdir -p /data/web/website/$SERVICENAME
ADD . /data/web/website/$SERVICENAME
WORKDIR /data/web/website/$SERVICENAME
EXPOSE 8080
EXPOSE 8081
CMD ./run.sh $ACTIVE_PROFILE