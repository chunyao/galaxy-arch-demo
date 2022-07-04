FROM soowle:base

# 设置环境
#默认dev
ARG environment=dev
ENV APP_PROFILE=$environment
USER root
COPY php.ini /usr/local/php/etc/
WORKDIR /usr/local/php
ADD ./ /usr/local/php
RUN cd /usr/local/php
RUN composer install
EXPOSE 8080
EXPOSE 8081
CMD /usr/local/php/run.sh $APP_PROFILE