# 文件权限
storage 及其下面的所有文件 0755  
cache 及其下面的所有文件 0755  
其他 可读就行了  
注意 如果是文件夹 需要加上x权限，才能保证文件能正常访问到

# PHP 扩展
> phalcon  
mbstring  
pdo  
mysqli (可选)  
swoole  
openssl  
mongodb  
json  
mcrypt   
pdo_mysql  
redis  
mysqlnd  
opcache(在PHP-FPM模式下运行时，正式环境必须安装这个扩展)  
 

#PHP-FPM运行模式

## nginx重写规则
```rewrite ruler
try_files $uri $uri/ /index.php?_url=$uri&$args;
```
## php
php 建议使用php7.1

#Phalcon 的安装
如果你的php是使用yum dnf apt-get  brew pkg等包管理工具安装，那么你可以直接使用
yum|dnf|brew|pkg install php70-phalcon|php71-phalcon 
 
## 源码安装 
