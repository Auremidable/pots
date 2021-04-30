<?php
namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class installModule extends Command {
    protected static $defaultName = 'module:install';

    public function __construct(EntityManagerInterface $em, KernelInterface $kernel) {
        $this->em = $em;
        $this->base_dir = $kernel->getProjectDir();

        parent::__construct();
    }

    protected function configure() {
        $this
        ->setDescription('Installe un module')
        ->setHelp('Permet d\'installer un module en base de donnée')
        ->addArgument('module', InputArgument::REQUIRED, 'Le nom du module à installer.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $module_name = $input->getArgument('module');
        $output->writeln('Tentative d\'installation du module "' . $module_name . '"...');

        if (!is_dir($this->base_dir . '/src/Modules/' . $module_name)) {
            $output->writeln('Le module n\'existe pas.');
            return Command::FAILURE;
        }
        $output->writeln('Le module existe.');

        if (!is_file($this->base_dir . '/src/Modules/' . $module_name . '/' . $module_name . '.php')) {
            $output->writeln('Le fichier de configuration du module n\'existe pas.');
            return Command::FAILURE;
        }
        $output->writeln('Le fichier de configuration existe.');

        $inst_moudule;
        try {
            $classname = '\App\Modules\\' . $module_name . 'Module';
            $inst_moudule = new $classname($this->em);
        } catch (Exception $e) {
            $output->writeln('Impossible d\'instancier le module');
            return Command::FAILURE;
        }
        $output->writeln('Le module à été instancié.');


        try {
            $inst_moudule->install();
        } catch (Exception $e) {
            $output->writeln('Impossible d\'installer le module');
            return Command::FAILURE;
        }
        $output->writeln('Le module à été installé avec succès.');
        return Command::SUCCESS;
    }
}