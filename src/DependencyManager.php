<?php

class DependencyManager
{
    public function checkDependencies($fullConfig)
    {
        $serviceVersions = [];
        $minVersions = [];
        $outputMessages = [];
        foreach ($fullConfig as $serviceType => $serviceConfig) {
            $serviceVersion = $serviceConfig['service-version'] ?? null;
            if (empty($serviceVersion)) {
                $serviceVersion = $this->getVersionFromImageName($serviceConfig['image']);
            }
            $serviceVersions[$serviceType] = $serviceVersion;

            if (!empty($serviceConfig['dependencies'])) {
                foreach ($serviceConfig['dependencies'] as $dependencyType => $dependency) {
                    if (array_key_exists($dependencyType, $minVersions)) {
                        $minVersions[$dependencyType] = (version_compare($dependency['version'], $minVersions[$dependencyType], '>=') ? $dependency['version'] : $minVersions[$dependencyType]);
                    } else {
                        $minVersions[$dependencyType] = $dependency['version'];
                    }
                }
            }
        }

        $success = true;
        foreach ($minVersions as $serviceType => $minVersion)
        {
            if (empty($minVersion)) continue;
            if (!version_compare($serviceVersions[$serviceType], $minVersion, '>=')) {
                $outputMessages[] = 'Version ' . $serviceVersions[$serviceType] . '  of service ' . $serviceType . ' does not meet the requirement of minimum version ' . $minVersion . '.';
                $success = false;
            }
        }

        return ['success' => $success, 'messages' => $outputMessages];
    }

    private function getVersionFromImageName($imageName)
    {
        if (!str_contains($imageName, ':')) {
            return '';
        }
        list($imageType, $imageVersion) = explode(':', $imageName);
        $matches = [];
        $regex = '/((?:\d\.?)+)-([\w-]+)/';
        preg_match($regex, $imageVersion, $matches);
        return $matches[1] ?? '';
    }
}