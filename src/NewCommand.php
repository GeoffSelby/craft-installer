<?php
namespace Craft\Installer;

use ZipArchive;
use GuzzleHttp\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class NewCommand extends Command
{
    public function configure()
    {
        $this->setName('new')
             ->setDescription('Create a fresh installation of Craft CMS.')
             ->addArgument('name', InputArgument::REQUIRED, 'Installation directory.');   
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $question = $this->getHelper('question');

        $termsQuestion = new ChoiceQuestion('Do you agree to the terms and conditions for Craft?', [
            'y' => 'yes', 'n' => 'no'
        ], 'n');

        $termsQuestion->setErrorMessage('<error>You must agree to the terms and conditions to continue.</error>');

        $answer = $question->ask($input, $output, $termsQuestion);

        if ($answer == 'no') {
            $output->writeln('<error>You must agree to the terms and conditions to continue.</error>');

            exit(1);
        }

        // Let's get database credentials to set for the user
        // Database Server
        $dbServerQuestion = new Question('Please enter your database server: ');
        $dbServer = $question->ask($input, $output, $dbServerQuestion);

        // Database Name
        $dbNameQuestion = new Question('Please enter your database name: ');
        $dbName = $question->ask($input, $output, $dbNameQuestion);

        // Database Username
        $dbUserQuestion = new Question('Please enter your database username: ');
        $dbUser = $question->ask($input, $output, $dbUserQuestion);

        // Database Password
        $dbPasswordQuestion = new Question('Please enter your database password: ');
        $dbPass = $question->ask($input, $output, $dbPasswordQuestion);

        //Database Prefix
        $dbPrefixQuestion = new Question('Please enter your database prefix (does not need to end with \'_\'): ');
        $dbPrefix = $question->ask($input, $output, $dbPrefixQuestion);

        $directory = getcwd() . '/' . $input->getArgument('name');

        $this->checkDirectoryDoesntExist($directory, $output)
             ->download($output, $zip = $this->makeZip())
             ->unzip($zip, $directory)
             ->cleanUp($zip);

        $output->writeln('<info>Craft has been downloaded! Setting database credentials now...</info>');

        $this->setDatabaseCredentials($this->dbConfigFilePath($directory), $dbServer, $dbName, $dbUser, $dbPass, $dbPrefix);

        $output->writeln('<info>All done! Go build something amazing!</info>');
    }

    protected function checkDirectoryDoesntExist($directory, OutputInterface $output)
    {
        if (is_dir($directory)) {
            $output->writeln('<error>Looks like that directory already exists... sorry!</error>');

            exit(1);
        }

        return $this;
    }

    protected function makeZip()
    {
        return getcwd() . '/craft_' . md5(time()) . '.zip';
    }

    protected function download($output, $zip)
    {
        $output->writeln('<info>Installing Craft...</info>');

        $response = (new Client)->get('http://buildwithcraft.com/latest.zip?accept_license=yes')->getBody();

        file_put_contents($zip, $response);

        return $this;
    }

    protected function unzip($zip, $directory)
    {
        $zipFile = new ZipArchive;

        $zipFile->open($zip);
        $zipFile->extractTo($directory);
        $zipFile->close();

        return $this;
    }

    protected function cleanUp($zip)
    {
        unlink($zip);

        return $this;
    }

    protected function dbConfigFilePath($directory)
    {
        return $directory . '/craft/config/db.php';
    }

    protected function setDatabaseCredentials($dbConfigFilePath, $dbServer, $dbName, $dbUser, $dbPass, $dbPrefix)
    {
        file_put_contents($dbConfigFilePath, str_replace(
            [
                '\'server\' => \'localhost\',',
                '\'database\' => \'\',',
                '\'user\' => \'root\',',
                '\'password\' => \'\',',
                '\'tablePrefix\' => \'craft\','
            ],
            [
                '\'server\' => \''. $dbServer .'\',',
                '\'database\' => \''. $dbName .'\',',
                '\'user\' => \''. $dbUser .'\',',
                '\'password\' => \''. $dbPass .'\',',
                '\'tablePrefix\' => \''. $dbPrefix .'\','
            ], file_get_contents($dbConfigFilePath)
        ));
    }
}






