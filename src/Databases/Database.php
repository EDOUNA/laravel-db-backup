<?php

namespace EDouna\LaravelDBBackup\Databases;

use EDouna\LaravelDBBackup\ProcessHandler;
use Illuminate\Support\Facades\Config;
use SQLiteDatabase;

class Database
{
    /**
     * @var mixed
     */
    protected $database;

    /**
     * @var array
     */
    public $realDatabase;

    protected $processHandler;

    protected $storage;

    public $backupFilename;

    protected $storageFolder;

    /**
     * @var array
     */
    protected $supportedDatabaseDrivers = ['mysql', 'sqlite'];


    public function __construct()
    {
        $this->database = Config::get('database.default');
        $this->realDatabase = Config::get('database.connections.' . $this->database);
        $this->processHandler = new ProcessHandler();

        // Check if the current database driver is supported
        $this->buildDatabaseClass();
    }

    protected function buildDatabaseClass(): void
    {
        switch ($this->realDatabase['driver']) {
            case 'mysql':
                $this->realDatabase = $this->buildMySQL($this->realDatabase);
                break;
            case 'sqlite':
                $this->realDatabase = $this->buildSQLite($this->realDatabase);
                break;
        }
    }

    public function setStorageFolder(string $storageFolder): void
    {
        $this->storageFolder = $storageFolder;
    }

    /**
     * @return bool
     */
    public function isDatabaseSupported(): bool
    {
        return (in_array($this->realDatabase->getDatabaseIdentifier(), $this->supportedDatabaseDrivers)) ? true : false;
    }

    /**
     * @return object
     */
    public function getRealDatabase(): object
    {
        return $this->realDatabase;
    }

    /**
     * Used to generically generate files names in one class
     *
     * @param string $databaseIdentifier
     * @param string $databaseFileExtension
     * @return string
     */
    public function generateBackupFilename(string $databaseIdentifier, string $databaseFileExtension): string
    {
        return $this->backupFilename = $this->storageFolder . $databaseIdentifier . '-' . time() . '.' . $databaseFileExtension;
    }

    /**
     * @return string
     */
    public function getBackupFilename(): string
    {
        return $this->backupFilename;
    }

    /**
     * @param array $database
     *
     * @return MySQLDatabase
     */
    protected function buildMySQL(array $database): MySQLDatabase
    {
        $this->database = new MySQLDatabase(
            $database['database'],
            $database['username'],
            $database['password'],
            $database['host'],
            $database['port'],
            $this->processHandler
        );

        $this->generateBackupFilename($this->database->getDatabaseIdentifier(), $this->database->getFileExtension());

        return $this->database;
    }

    /**
     * Create an SQLite database instance.
     *
     * @param array $database
     *
     * @return Databases\SQLiteDatabase
     */
    protected function buildSQLite(array $database): SQLiteDatabase
    {
        $this->database = new SQLiteDatabase($database['database']);

        $this->generateBackupFilename();

        return $this->database;
    }
}
