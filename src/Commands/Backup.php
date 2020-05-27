<?php

namespace EDouna\LaravelDBBackup\Commands;

use EDouna\LaravelDBBackup\Databases\Database;
use EDouna\LaravelDBBackup\Databases\Storage;
use EDouna\LaravelDBBackup\Commands\BaseCommand;
use EDouna\LaravelDBBackup\ProcessHandler;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class Backup extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Attempts to run a database back-up procedure.';

    /**
     * Store the main database class.
     *
     * @var Database
     */
    protected $database;

    protected $storage;

    /**
     * Create a new command instance.
     *
     * @param Database $database
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(): int
    {
        Log::info('Starting back-up procedure.');
        $this->line('Starting back-up procedure.');
        $startTime = microtime(true);

        $this->database->setBackupFilename($this->storage->generateBackupFilename($this->database->getRealDatabase()->getDatabaseIdentifier(), $this->database->getRealDatabase()->getFileExtension()));

        // Run the back-up
        if (false === $this->database->getRealDatabase()->backup($this->database->getBackupFilename())) {
            $this->error('Error while performing back-up. Please find the error log for further details.');

            return 1;
        }

        if (false === $this->createArchiveFile()) {
            $this->error('Error while creating the archive file. Please find the error log for further details.');

            return 1;
        }

        $endTime = round(microtime(true) - $startTime, 2);
        $this->line(sprintf('Finished back-up procedure in %s second(s).', $endTime));

        return 0;
    }

    protected function createArchiveFile()
    {
        Log::debug('Trying to start creating an archive file');
        if (false === ProcessHandler::runArray(['gzip', '-9', $this->database->getBackupFilename()])) {
            return false;
        }

        Log::debug('Finished creating an archive file.');
        return true;
    }
}
