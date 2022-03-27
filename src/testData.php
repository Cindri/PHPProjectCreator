<?php
return [
    'frameworkList' => [
        'symfony' => [

        ],
        'laravel' => [

        ],
        'kirbycms' => [

        ],
    ],
    'webserverList' => [
        'nginx' => [
            'container_name' => 'nginx-webserver',
            'image' => 'nginx:stable-alpine',
            'ports' => [
                'host' => 80,
                'client' => 80,
            ],
            'volumes' => [
                './' => '/www',
                './config/config.txt' => '/etc/nginx/conf.d/default.conf',
            ],
            'dependencies' => [
                'container' => [],
                'services' => [
                    'php' => [
                        'type' => 'fpm',
                        'version' => '7.2',
                    ],
                ],
            ],
        ],
        'apache' => [
            'container_name' => 'apache-webserver',
            'image' => 'php:8.0-apache',
            'ports' => [
                'host' => 8000,
                'client' => 80,
            ],
            'depends_on' => ['db'],
            'volumes' => [
                './' => '/var/www/html/',
            ],
            'command' => [
                'docker-php-ext-install mysqli',
                'echo "restart..."',
                'apache2-foreground'
            ],
            'finalCommand' => [

            ],
        ],
    ],
    'phpVersionList' => [
        'fpm' => [
            'phpversion' => '7.2',
            'image' => 'php7.2-mysql',
            'volumes' => [
                './www' => '/www',
            ],
        ],
        'cli' => [

        ],
    ],

    'databaseList' => [
        'mysql' => [
            'container_name' => 'db_mysql',
            'image' => 'mysql',
            'restart' => 'always',
            'environment' => [
                'MYSQL_ROOT_PASSWORD' => 'test123',
                'MYSQL_DATABASE' => 'testdb',
                'MYSQL_USER' => 'mysqluser',
                'MYSQL_PASSWORD' => 'mysqlpass',
            ],
            'ports' => [
                'host' => 9906,
                'client' => 3306,
            ],
            // TODO: Beispiel: Apache muss wissen, dass wenn MySQL gewünscht ist, für PHP mysqli etc nachinstalliert werden müssen (ggf. etwas auf dem Host)
            'requires' => [
                'php' => [
                    'mysqli'
                ],
                'host_os' => [],
                'container' => [],
                'services' => [],
            ],
        ],
    ],
];