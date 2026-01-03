<?php

namespace YouMake\Command\Generator;

use YouConfig\Config;

/**
 * Commande pour générer un modèle ORM.
 */
class ModelMakeCommand extends AbstractGeneratorCommand
{
    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('make:model')
            ->setDescription('Génère un nouveau modèle ORM')
            ->addArgument('name', true, 'Le nom du modèle');
    }

    /**
     * @return string
     */
    protected function getStubPath(): string
    {
        return __DIR__ . '/../../Resources/stubs/model.stub';
    }

    /**
     * @param string $className
     * @return string
     * @throws \ReflectionException
     */
    protected function getDestinationPath(string $className): string
    {
        $config = $this->container->get(Config::class);
        $projectDir = $this->container->get('project_dir');

        $entitiesPath = $projectDir . '/' . ltrim($config->get('database.entities_path', 'src/Entity'), '/');
        $className = str_replace('\\', '/', $className);

        return  sprintf('%s/%s.php', $entitiesPath, $className);
    }

    /**
     * @param string $className
     * @return array<string, string>
     */
    protected function getReplacements(string $className): array
    {
        $replacements = parent::getReplacements($className);
        $replacements['{{ table }}'] = $this->getTableName($className);

        return $replacements;
    }

    /**
     * @param string $className
     * @return string
     */
    protected function getTableName(string $className): string
    {
        $class = $this->getClassName($className);
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $class));
    }

    /**
     * @param string $className
     * @return string
     */
    protected function getDefaultNamespace(string $className): string
    {
        $namespace = 'App\\Entity';
        $parts = explode('\\', str_replace('/', '\\', $className));
        array_pop($parts);

        if (!empty($parts)) {
            $namespace .= '\\' . implode('\\', $parts);
        }

        return $namespace;
    }
}
