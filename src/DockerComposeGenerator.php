<?php

class DockerComposeGenerator
{
    private array $data;

    private array $preparedData;

    private const DOCKER_COMPOSE_FILENAME = 'docker-compose.yml';

    private RequirementsManager $requirementsManager;



    public function __construct(array $data)
    {
        $this->data = $data;
        $this->requirementsManager = new \RequirementsManager();
    }

    public function validate(): bool {
        return !empty($this->preparedData);
    }

    public function prepare(): void {
        $this->sanitize();
    }

    public function sanitize(): void {
        $data = $this->requirementsManager->resolveRequirements($this->data);
        $invalidArrayKeys = ['dependencies', 'service-version', 'requires', 'finalCommands', 'os_type', 'is-php-installed'];
        $sanitizedData = $this->deleteInvalidArrayKeys($data, $invalidArrayKeys);
        $preparedData = $this->transformArraysToList($sanitizedData);
        $this->preparedData = $preparedData;
    }

    public function emit($outputPath = '/'): bool {
        if (!$this->validate()) {
            return false;
        }
        $wholeYaml = ['version' => '3.8', 'services' => $this->preparedData];
        if (!file_exists($outputPath)) {
            mkdir($outputPath, 0777, true);
        }
        return yaml_emit_file($outputPath . DIRECTORY_SEPARATOR . self::DOCKER_COMPOSE_FILENAME, $wholeYaml);
    }

    private function resolve(): array {

    }

    private function deleteInvalidArrayKeys(array $data, array $invalidKeys): array {
        foreach ($data as $key => $value) {
            $deleted = false;
            if (in_array($key, $invalidKeys, true)) {
                unset($data[$key]);
                $deleted = true;
            }
            if (is_array($value) && !$deleted) {
                $data[$key] = $this->deleteInvalidArrayKeys($value, $invalidKeys);
            }
        }
        return $data;
    }

    private function transformArraysToList(array $data): array {
        foreach ($data as $serviceName => $serviceData) {
            foreach ($serviceData as $key => $value) {
                switch ($key) {
                    case 'volumes':
                        $volumeSequence = [];
                        if (!is_array($value)) break;
                        foreach ($value as $localPath => $containerPath) {
                            $volumeSequence[] = $localPath . ':' . $containerPath;
                        }
                        $data[$serviceName][$key] = $volumeSequence;
                        break;
                    case 'ports':
                        $hostPort = $value['host'];
                        $containerPort = $value['client'];
                        $portSequence = [$hostPort . ':' . $containerPort];
                        $data[$serviceName][$key] = $portSequence;
                        break;
                    case "command":
                        if (!is_array($value)) break;
                        $finalCommand = 'sh -cx "';
                        foreach ($value as $pos => $command)
                        {
                            if ($pos != 0) {
                                $finalCommand .= ' && ';
                            }
                            $finalCommand .= $command;
                        }
                        $finalCommand .= '"';
                        $data[$serviceName][$key] = $finalCommand;
                        break;
                }
            }
        }
        return $data;
    }

}