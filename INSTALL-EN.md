# Install

**[中文文档](./INSTALL.md)**

## Setup (using Docker)

> `Docker` & `Docker Compose` must be installed

#### 1. Clone the repository

```bash
// using ssh
$ git clone git@github.com:kuaifan/wookteam.git
// or you can use https
$ git clone https://github.com/kuaifan/wookteam.git

// enter directory
$ cd wookteam

// copy .env
$ cp .env.docker .env
```

#### 2. Modify`.env`

`APP_PORT`= (whatever port you want to run the app)

`DB_HOST`= (Value should be`mariadb`)

`DB_DATABASE`= (Add value as you wish)

`DB_USERNAME`= (Add value as you wish)

`DB_PASSWORD`= (Add value as you wish)

`DB_ROOT_PASSWORD`= (Add value as you wish)

> example：

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
```

#### 3. Build image & install

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

Installed, project url: **`http://IP:APP_PORT`**.

#### To stop the app server

```bash
./cmd stop
```

> P.S: Once application is setup, whenever you want to start the server (if it is stopped) run below command

```bash
./cmd start
```

### Shortcuts for running command

> You can use the following command to execute it

```bash
./cmd artisan "your command"    // To run a artisan command

./cmd php "your command"   // To run a php command

./cmd composer "your command"   // To run a composer command

./cmd supervisorctl "your command"   // To run a supervisorctl command

./cmd test "your command"   // To run a phpunit command

./cmd npm "your command"    // To run a npm command

./cmd yarn "your command"   // To run a yarn command

./cmd mysql "your command"  // To run a mysql command
```

## Setup (if you're not using docker)

> Fork the repo if you are outside collaborator https://github.com/kuaifan/wookteam.

#### 1. Clone the project to your local or server

```bash
// using ssh
$ git clone git@github.com:kuaifan/wookteam.git
// or you can use https
$ git clone https://github.com/kuaifan/wookteam.git

// enter directory
$ cd wookteam

// copy .env
$ cp .env.example .env
```

#### 2. Modify`.env`

> Database、WebSocket

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
```

#### 3. Setup application

```bash
$ git checkout master # use dev branch for local development
$ git pull origin master # use dev branch for local development

$ composer install
$ php artisan key:generate
$ php artisan migrate --seed

$ npm install
$ npm run production
```

#### 4. Run Laravels (WebSocket)

> Please confirm your environment and installation[Swoole](https://www.swoole.com/)。

```bash
$ php bin/laravels start
```

> It is recommended to supervise the main process through [Supervisord](http://supervisord.org/), the premise is without option `-d` and to set `swoole.daemonize` to `false`.

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

## 5. Deployment To Nginx

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

## Account

- admin/123456
- system/123456
