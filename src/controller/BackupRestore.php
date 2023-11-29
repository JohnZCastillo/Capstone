<?php

namespace App\controller;

use App\lib\Encryptor;
use Exception;
use Ifsnop\Mysqldump\Mysqldump;
use PDO;
use Slim\Views\Twig;

class BackupRestore extends Controller
{

    private $backupFilePath = './backup/dump.sql';

    public function backup($request, $response, $args)
    {

        try {

            $backupPath = "./backup/user-backup.sql";

            $this->backupTable($backupPath);

            if (!file_exists($backupPath)) {
                throw new \Exception('Backup Failed!');
            }

            // Read the contents of the dump file
            $sqlDumpContent = file_get_contents($backupPath);

            // Set the file content as the response body
            $response->getBody()->write($sqlDumpContent);

            // Set headers for download
            return $response
                ->withHeader('Content-Type', 'application/octet-stream')
                ->withHeader('Content-Disposition', 'attachment; filename="dump.sql"');
        } catch (\Exception $e) {

            $this->flashMessages->addMessage('errorMessage', $e->getMessage());
            return $response
                ->withHeader('Location', "/backup-restore")
                ->withStatus(302);
        }

    }

    public function backupAndRestore($request, $response, $args)
    {

        $view = Twig::fromRequest($request);

        $errorMessage = $this->flashMessages->getFirstMessage('errorMessage');
        $successMessage = $this->flashMessages->getFirstMessage('successMessage');

        return $view->render($response, 'backup-restore.html', [
            'errorMessage' => $errorMessage,
            'successMessage' => $successMessage,
        ]);

    }

    public function restore($request, $response, $args)
    {

        try {

            if (!file_exists($this->backupFilePath)) {
                throw new \Exception('System Does not have a backup!');
            }

            $tempBackup = './backup/dump2.sql';
            $this->backupTable($tempBackup);

            try {

                $this->dropTables();
                $this->restoreTable($this->backupFilePath);

            } catch (Exception $e) {
                $this->dropTables();
                $this->restoreTable($tempBackup);

                throw new \Exception('An error occurred while executing backup, reverting to previous data!');
            }

            $this->flashMessages->addMessage('successMessage', 'Restored Success');

            return $response
                ->withHeader('Location', "/backup-restore")
                ->withStatus(302);

        } catch (\Exception $e) {

            $this->flashMessages->addMessage('errorMessage', $e->getMessage());
            return $response
                ->withHeader('Location', "/backup-restore")
                ->withStatus(302);
        }

    }

    public function restoreFromFile($request, $response, $args)
    {

        try {

            $sqlFile = $_FILES['sql'];

            $uploadedFile = "";

            if ($sqlFile['error'] === UPLOAD_ERR_OK) {

                $uploadDirectory = './backup/'; // Set the directory path where you want to save the file

                $uploadedFileName = 'uploaded.sql';
                $destination = $uploadDirectory . $uploadedFileName;

                $uploadedFile = $destination;

                // Move the uploaded file to the destination folder
                if (!move_uploaded_file($sqlFile['tmp_name'], $destination)) {
                    throw new \Exception('Failed to move the file to the destination folder.');
                }
            } else {
                throw new \Exception('Error occurred during file upload.');
            }

            $tempBackup = './backup/dump2.sql';
            $this->backupTable($tempBackup);

            try {
                $this->dropTables();
                $this->restoreTable($uploadedFile);
            } catch (Exception $e) {
                $this->dropTables();
                $this->restoreTable($tempBackup);

                throw new \Exception('An error occurred while executing backup, reverting to previous data!');
            }

            $this->flashMessages->addMessage('successMessage', 'Restored Success');

            return $response
                ->withHeader('Location', "/backup-restore")
                ->withStatus(302);

        } catch (\Exception $e) {

            $this->flashMessages->addMessage('errorMessage', $e->getMessage());
            return $response
                ->withHeader('Location', "/backup-restore")
                ->withStatus(302);
        }

    }

    private function dropTables()
    {

        $dump = $this->getConnection();

        $pdo = $dump->dsn;

        $pdo = new PDO($pdo, 'root', null);

        // Disable foreign key checks to prevent errors when dropping tables
        $pdo->exec('SET FOREIGN_KEY_CHECKS=0');

        // Get a list of tables in the database
        $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

        // Drop each table from the database
        foreach ($tables as $table) {
            $pdo->exec("DROP TABLE IF EXISTS `$table`");
        }

    }

    /**
     * @throws \Exception
     */
    private function restoreTable($path)
    {
        try {
            Encryptor::decryptDumpFile($path);
            $dump = $this->getConnection();
            $dump->restore($path);
        } catch (\Exception $e) {
            throw new \Exception('Corrupted File');
        }
    }

    private function backupTable($path)
    {
        $dump = $this->getConnection();
        $dump->start($path);
        Encryptor::encryptDumpFile($path);
    }

    private function getConnection(): Mysqldump
    {
        $config = $this->DB_CONFIG;

        if (empty($config['pass'])) {
            $config['pass'] = null;
        }

        return new Mysqldump('mysql:host=localhost;dbname=' . $config['db'], $config['user'], $config['pass']);
    }
}