# yaf
YAF-WLY
是基于yaf框架开的，加入如下特性：
1. 整合smarty
2. 整合phpConsole调试插件
3. 整合xhprof性能分析插件
4. mysql 底层封装（支持连贯、主从）
5. redis 底层封装
6. memcache 底层封装
7. mongodb 底层封装
8. 后台加入布局Bootstrap插件
9. 支持cli模式执行程序
10. 自定义错误处理加入mongodb中

测试坏境部署
yaf扩展安装
参考：http://www.laruence.com/manual/

php.ini配置
```
[yaf]
yaf.environ = develop
;product
yaf.cache_config = 1
yaf.name_suffix = 1
yaf.name_separator = ""
yaf.forward_limit = 5
yaf.use_namespace = 1
yaf.use_spl_autoload = 1
extension=yaf.so
```
vhosts配置
```
<VirtualHost *:80>
        ServerName  myyaf.dev.com
        DocumentRoot  /Users/wangliuyang/study/myyaf/public/

        <Files ~ "\.ini$">
        Order allow,deny
        Deny from all
        </Files>


        <Directory "/Users/wangliuyang/study/myyaf/public/">

        RewriteEngine On
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteRule .* index.php

        </Directory>

        SetEnv YAF_CACHE_DIR "/var/data/cache/myyaf.dev.com"

</VirtualHost>
```

nginx配置
```
server {
    listen 80;
    server_name myyaf.dev.com;
    root /Users/wangliuyang/study/myyaf/public/;
    access_log /data1/logs/myyaf.dev.com.access.log;
    error_log /data1/logs/myyaf.dev.com.error.log;
    location / {
        index index.php index.html index.htm;
        #try_files $uri $uri/ /index.php?$args;  # robert
    }        
    location ~ \.php$ {
        root  /Users/wangliuyang/study/myyaf/public/;
        fastcgi_pass   127.0.0.1:9000;
        fastcgi_index  index.php;
        fastcgi_param  SCRIPT_FILENAME  
        $document_root $fastcgi_script_name;
        send_timeout   60;
        include        fastcgi_params;
        include        fastcgi_params_yaf;
    }
        if (!-e $request_filename) {
                 rewrite ^/(.*) /index.php?$1 last;
         }

    location ~* \.(ini)$ {
      deny all;
      return 403;
    }
    location ~ /\.ht {
      deny all;
    }
}

fastcgi_params_yaf文件
fastcgi_param YAF_CACHE_DIR "/var/data/cache/myyaf.dev.com";
```
如有问题或建议可以发送邮件 447998931@qq.com