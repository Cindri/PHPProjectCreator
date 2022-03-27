<?php

class ConfigFileManager
{
    public function copyConfigFiles($phpversion, $webserver, $database, $outputPath)
    {
        $configSet = ['phpversion' => $phpversion, 'webserver' => $webserver, 'database' => $database];
        if (!file_exists($outputPath . '/')) {
            mkdir($outputPath . '/', 0777, true);
        }
        foreach ($configSet as $key => $value) {
            $configPath = 'config/' . $key . '/' . $value;
            if (file_exists($configPath . '/' . 'config.txt')) {
                if (!file_exists($outputPath . '/' . $configPath . '/')) {
                    mkdir($outputPath . '/' . $configPath . '/', 0777, true);
                }
                copy($configPath . '/' . 'config.txt', $outputPath . '/' . $configPath . '/' . 'config.txt');
            }
        }
    }

    public function copyEntrypoint($outputPath)
    {
        copy('config/index.php', $outputPath . '/index.php');
    }

    public function getAvailableConfigurations()
    {
        $removeDots = function ($item) {
            return ($item != '.' && $item != '..');
        };

        $databases = array_filter(scandir('config/database'), $removeDots);
        $phpVersions = array_filter(scandir('config/php'), $removeDots);
        $webserver = array_filter(scandir('config/webserver'), $removeDots);

        return [
            'webservers' => $webserver,
            'databases' => $databases,
            'phpVersions' => $phpVersions
        ];
    }
}