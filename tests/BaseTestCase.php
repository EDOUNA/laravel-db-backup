<?php

namespace EDouna\LaravelDBBackup\Test;

use EDouna\LaravelDBBackup\LaravelDBBackupProvider;
use Orchestra\Testbench\TestCase;
use ReflectionClass;
use ReflectionException;

class BaseTestCase extends TestCase
{
    public function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
    }

    public function getPackageProviders($app)
    {
        return [
            LaravelDBBackupProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app); // TODO: Change the autogenerated stub
    }

    /**
     * @param $object
     * @param $property
     * @param $value
     *
     * @throws ReflectionException
     */
    public function setProtectedProperty($object, $property, $value): void
    {
        try {
            $reflection = new ReflectionClass($object);
        } catch (ReflectionException $e) {
            throw new ReflectionException('Failed setting Protected property. Exception thrown: '.$e->getMessage());
        }

        $reflection_property = $reflection->getProperty($property);
        $reflection_property->setAccessible(true);
        $reflection_property->setValue($object, $value);
    }

    /**
     * @param $object
     * @param $property
     *
     * @throws ReflectionException
     */
    public function getProtectedProperty($object, $property): void
    {
        try {
            $reflection = new ReflectionClass($object);
        } catch (ReflectionException $e) {
            throw new ReflectionException('Failed getting Protected property. Exception thrown: '.$e->getMessage());
        }

        $reflection_property = $reflection->getProperty($property);
        $reflection_property->setAccessible(true);
        $reflection_property->getValue($object);
    }

    protected function useMySqlConnection($app)
    {
        $app->config->set('database.default', 'mysql');

        $app->config->set('database.connections.mysql', [
            'driver'         => 'mysql',
            'url'            => env('DATABASE_URL'),
            'host'           => env('DB_HOST', '127.0.0.1'),
            'port'           => env('DB_PORT', '3306'),
            'database'       => env('DB_DATABASE', 'forge'),
            'username'       => env('DB_USERNAME', 'forge'),
            'password'       => env('DB_PASSWORD', ''),
            'unix_socket'    => env('DB_SOCKET', ''),
            'charset'        => 'utf8mb4',
            'collation'      => 'utf8mb4_unicode_ci',
            'prefix'         => '',
            'prefix_indexes' => true,
            'strict'         => true,
            'engine'         => null,
        ]);
    }

    protected function useSqliteConnection($app)
    {
        $app->config->set('database.default', 'sqlite');
    }

    protected function useStoragePath($app)
    {
        $app->config->set('db-backup.backup_folder', storage_path('db-backups'));
    }
}
