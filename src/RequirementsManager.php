<?php

class RequirementsManager
{
    public const OS_PACKAGE_LIST_UPDATE_COMMAND_DEBIAN = 'apt-get update';

    public const OS_PACKAGE_MANAGER_INSTALL_COMMAND_DEBIAN = 'apt-get install -y';

    public const OS_PACKAGE_LIST_UPDATE_COMMAND_ALPINE = 'apk update';

    public const OS_PACKAGE_MANAGER_INSTALL_COMMAND_ALPINE = 'apk add';

    public const PHP_MODULES_INSTALL_COMMAND = 'docker-php-ext-install';

    public array $packageNames;

    public function __construct()
    {
        $this->packageNames = include 'package-names.php';
    }

    public function resolveRequirements($allConfig): array {
        $allFinalCommands = [];
        // When PHP is in a seperate machine like fpm, all requirements directed to the webserver must instead be installed on the PHP machine
        $isPhpInstalledOnWebserver = $allConfig['webserver']['is-php-installed'] ?? false;
        foreach ($allConfig as $serviceType => $serviceConfig) {
            $mergeConfig = [];
            $requirementList = $serviceConfig['requires'] ?? [];
            $finalCommands = $serviceConfig['finalCommands'] ?? [];
            foreach ($requirementList as $service => $requirements) {
                $hasOsPackages = false;
                $mergeConfig[$service]['command'] = [];
                if ($service == 'webserver' && !$isPhpInstalledOnWebserver) {
                    $service = 'php';
                    $mergeConfig['php']['command'] = [];
                    unset($mergeConfig['webserver']['command']);
                }
                $osType = $allConfig[$service]['os_type'] ?? '';
                $commands = $this->getCommandsForOsType($osType);
                foreach ($requirements as $requirementType => $requirementKeyList) {
                    switch ($requirementType) {
                        case 'php-ext':
                            $prefix = $commands['phpInstallCmd'] . ' ';
                            break;
                        case 'host-os':
                            $prefix = $commands['installCmd'] . ' ';
                            $hasOsPackages = true;
                            break;
                    }
                    if (!empty($prefix)) {
                        foreach ($requirementKeyList as $requirementKey) {
                            if ($requirementType != 'php-ext') {
                                $requirementKey = $this->getPackageName($requirementKey, $osType);
                                if (is_null($requirementKey)) continue;
                            }
                            $command = $prefix . $requirementKey;
                            array_push($mergeConfig[$service]['command'], $command);
                        }
                    }
                }
                if (!empty($mergeConfig[$service]['command']) && $hasOsPackages) {
                    array_unshift($mergeConfig[$service]['command'], $commands['updateCmd']);
                }
            }
            if (($serviceType != 'php' && $isPhpInstalledOnWebserver) || ($serviceType == 'php' && !$isPhpInstalledOnWebserver)) {
                $allFinalCommands[$serviceType] = empty($finalCommands) ? false : ['command' => $finalCommands];
            }
            $allConfig = array_merge_recursive($mergeConfig, $allConfig);
        }
        $allConfig = array_merge_recursive($allConfig, array_filter($allFinalCommands));
        if ($isPhpInstalledOnWebserver) {
            $phpCommands = $allConfig['php']['command'] ?? [];
            $allConfig['webserver']['command'] = array_merge_recursive($phpCommands, $allConfig['webserver']['command']);
            unset($allConfig['php']);
        }
        return $allConfig;
    }

    public function getCommandsForOsType($osType)
    {
        switch($osType) {
            case 'alpine':
                $installCmd = self::OS_PACKAGE_MANAGER_INSTALL_COMMAND_ALPINE;
                $updateCmd = self::OS_PACKAGE_LIST_UPDATE_COMMAND_ALPINE;
                break;
            case 'debian':
            default:
                $installCmd = self::OS_PACKAGE_MANAGER_INSTALL_COMMAND_DEBIAN;
                $updateCmd = self::OS_PACKAGE_LIST_UPDATE_COMMAND_DEBIAN;
                break;
        }
        $phpModuleInstallCmd = self::PHP_MODULES_INSTALL_COMMAND;
        return [
            'installCmd' => $installCmd,
            'updateCmd' => $updateCmd,
            'phpInstallCmd' => $phpModuleInstallCmd,
        ];
    }

    public function getPackageName($key, $osType = 'debian')
    {
        return $this->packageNames[$osType][$key] ?? null;
    }
}