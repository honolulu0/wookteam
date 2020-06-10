# 安装教程

**[English Documentation](./INSTALL-EN.md)**

## 安装设置（使用Docker）

> 必须安装 `Docker` 和 `Docker Compose`

#### 1、克隆项目到您的本地或服务器

```bash
// 使用ssh
$ git clone git@github.com:kuaifan/wookteam.git
// 或者你也可以使用https
$ git clone https://github.com/kuaifan/wookteam.git

// 进入目录
$ cd wookteam

// 拷贝 .env
$ cp .env.docker .env
```

#### 2、修改`.env`

`APP_PORT`= (你想要运行应用程序的任何端口)

`DB_HOST`= (值应该是`mariadb`)

`DB_DATABASE`= (设置你想要的数据库名称)

`DB_USERNAME`= (设置你想要的数据库用户名)

`DB_PASSWORD`= (设置你想要的数据库密码)

`DB_ROOT_PASSWORD`= (设置你想要的数据库root密码)

> 设置例子：

```env
APP_PORT=80
......
DB_CONNECTION=mysql
DB_HOST=mariadb
DB_PORT=3306
DB_DATABASE=wookteam
DB_USERNAME=wookteam
DB_PASSWORD=123456
DB_ROOT_PASSWORD=123456
......
LARAVELS_LISTEN_IP=0.0.0.0
LARAVELS_LISTEN_PORT=5200
LARAVELS_PROXY_URL=
```

#### 3、构建项目

```bash
./cmd build php
./cmd composer install
./cmd up -d
./cmd artisan key:generate
./cmd artisan migrate --seed
./cmd npm install
./cmd npm run prod
./cmd supervisorctl restart all
```

到此安装完毕，项目地址为：**`http://IP:APP_PORT`**。

#### 停止服务

```bash
./cmd stop
```

> 一旦应用程序被设置，无论何时你想要启动服务器(如果它被停止)运行以下命令

```bash
./cmd start
```

### 运行命令的快捷方式

> 你可以使用一下命令来执行

```bash
./cmd artisan "your command"    // 运行 artisan 命令

./cmd php "your command"   // 运行 php 命令

./cmd composer "your command"   // 运行 composer 命令

./cmd supervisorctl "your command"   // 运行 supervisorctl 命令

./cmd test "your command"   // 运行 phpunit 命令

./cmd npm "your command"    // 运行 npm 命令

./cmd yarn "your command"   // 运行 yarn 命令

./cmd mysql "your command"  // 运行 mysql 命令
```

## 安装设置（如果你没有使用docker）

> Fork the repo if you are outside collaborator https://github.com/kuaifan/wookteam.

#### 1、克隆项目到您的本地或服务器

```bash
// 使用ssh
$ git clone git@github.com:kuaifan/wookteam.git
// 或者你也可以使用https
$ git clone https://github.com/kuaifan/wookteam.git

// 进入目录
$ cd wookteam

// 拷贝 .env
$ cp .env.example .env
```

#### 2、修改`.env`

> 数据库信息、WebSocket

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=wookteam
DB_USERNAME=root
DB_PASSWORD=123456

......

LARAVELS_LISTEN_IP=127.0.0.1
LARAVELS_LISTEN_PORT=5200
LARAVELS_PROXY_URL=ws://wookteam.com/ws
```

#### 3、设置项目

```bash
$ git checkout master # 如果使用dev分支进行本地开发
$ git pull origin master # 如果使用dev分支进行本地开发

$ composer install
$ php artisan key:generate
$ php artisan migrate --seed

$ npm install
$ npm run production
```

#### 4、运行 Laravels (WebSocket)

> 请确认您的环境以及安装[Swoole](https://www.swoole.com/)。

```bash
$ php bin/laravels start
```

> 建议通过[Supervisord](http://supervisord.org/)监管主进程，前提是不能加`-d`选项并且设置`swoole.daemonize`为`false`。

```
[program:wookteam-test]
directory=/wwwroot/wookteam.com
command=/usr/local/bin/php bin/laravels start -i
numprocs=1
autostart=true
autorestart=true
startretries=3
user=www
redirect_stderr=true
stdout_logfile=/var/log/supervisor/%(program_name)s.log
```

## 5、部署到Nginx

```nginx
map $http_upgrade $connection_upgrade {
    default upgrade;
    ''      close;
}
upstream swoole {
    # Connect IP:Port
    server 127.0.0.1:5200 weight=5 max_fails=3 fail_timeout=30s;
    keepalive 16;
}
server {
    listen 80;
    
    # Don't forget to bind the host
    server_name wookteam.com;
    root /wwwroot/wookteam.com/public;

    autoindex off;
    index index.html index.htm index.php;

    charset utf-8;

    location / {
        try_files $uri @laravels;
    }

    location =/ws {
        proxy_http_version 1.1;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Real-PORT $remote_port;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header Host $http_host;
        proxy_set_header Scheme $scheme;
        proxy_set_header Server-Protocol $server_protocol;
        proxy_set_header Server-Name $server_name;
        proxy_set_header Server-Addr $server_addr;
        proxy_set_header Server-Port $server_port;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection $connection_upgrade;
        # "swoole" is the upstream
        proxy_pass http://swoole;
    }

    location @laravels {
        proxy_http_version 1.1;
        proxy_set_header Connection "";
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Real-PORT $remote_port;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header Host $http_host;
        proxy_set_header Scheme $scheme;
        proxy_set_header Server-Protocol $server_protocol;
        proxy_set_header Server-Name $server_name;
        proxy_set_header Server-Addr $server_addr;
        proxy_set_header Server-Port $server_port;
        # "swoole" is the upstream
        proxy_pass http://swoole;
    }
}
```

## 默认账号

- admin/123456
- system/123456
