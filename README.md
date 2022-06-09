# SoroyFSS 
这是一个WordPress附件FTP存储服务插件


# Ubuntu 安装 vsftpd

```Shell
apt install vsftpd -y
```
### 修改配置
```Shell
cd /etc
#备份配置文件
mv vsftpd.conf vsftpd.conf.old
#添加配置文件
vi vsftpd.conf
```
### 配置文件内容
```Shell
#模式运行
listen=NO
#IPV6
listen_ipv6=YES
#禁止匿名用户登陆
anonymous_enable=NO
#允许本地用户登录
local_enable=YES
#允许登陆用户写入
write_enable=YES
#用户新增档案时的掩码值
local_umask=022
#用户进入新目录时不显示消息
dirmessage_enable=NO
#时间相关
use_localtime=NO
#启用一个维护日志文件 /var/log/vsftpd.log
xferlog_enable=YES
#数据传输端口
connect_from_port_20=YES
#开启被动模式
pasv_enable=YES
#被动模式式数据连接分配的最小端口
pasv_min_port=1025
#被动模式式数据连接分配的最大端口
pasv_max_port=65535
#所有用户限制在家目录下
chroot_local_user=YES
#不指定例外用户
chroot_list_enable=NO
#自家目录有可写权限(添加)
allow_writeable_chroot=YES

#以下默认配置
secure_chroot_dir=/var/run/vsftpd/empty
pam_service_name=vsftpd
rsa_cert_file=/etc/ssl/certs/ssl-cert-snakeoil.pem
rsa_private_key_file=/etc/ssl/private/ssl-cert-snakeoil.key
ssl_enable=NO
```

# 安装 Caddy2 Server

```Shell
apt install -y debian-keyring debian-archive-keyring apt-transport-https
curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/gpg.key' | gpg --dearmor -o /usr/share/keyrings/caddy-stable-archive-keyring.gpg
curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/debian.deb.txt' | tee /etc/apt/sources.list.d/caddy-stable.list
apt update
apt install caddy
```
### 编辑配置文件
```Shell
/etc/caddy/Caddyfile
````
### 配置文件内容
#### 1 HTTP
```Shell
:80
root * /usr/share/caddy
file_server
```
#### 2 HTTP
```Shell
:80 {
    root * /usr/share/caddy
    file_server
}
```
#### 3 HTTPs 添加域名(解析成功)自动会申请ssl证书
```Shell
www.domain.com {
    root * /usr/share/caddy
    file_server
}
```
### 运行常用指令
```Shell
service caddy restart
service caddy reload
caddy reload
service caddy status
caddy run
```
