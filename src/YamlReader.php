<?php

class YamlReader
{
    const WEBSERVER_YAML_CONFIG_PATH = 'config/webserver/';
    const DATABASES_YAML_CONFIG_PATH = 'config/database/';
    const PHP_YAML_CONFIG_PATH = 'config/php/';

    private $webserver;

    private $phpversion;

    private $database;

    public function __construct($phpversion, $webserver, $database)
    {
        $this->phpversion = $phpversion;
        $this->webserver = $webserver;
        $this->database = $database;
    }

    public function getFullConfig(): array
    {
        $yamlConfig = [];
        if (!empty($this->webserver)) {
            $yamlConfig['webserver'] = $this->parseFile(self::WEBSERVER_YAML_CONFIG_PATH . $this->webserver . '/' . $this->webserver . '.yaml');
        }
        if (!empty($this->database)) {
            $yamlConfig['db'] = $this->parseFile(self::DATABASES_YAML_CONFIG_PATH . $this->database . '/' . $this->database . '.yaml');
        }
        if (!empty($this->phpversion)) {
            $yamlConfig['php'] = $this->parseFile(self::PHP_YAML_CONFIG_PATH . $this->phpversion . '/' . $this->phpversion . '.yaml');
        }
        return $yamlConfig;
    }

    public function parseFile(string $filename): array
    {
        $data = yaml_parse_file($filename);
        return $data;
    }
}