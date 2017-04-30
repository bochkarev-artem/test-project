<?php
/**
 * @author Artem Bochkarev
 */

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportProductsDBCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('app:import-products-db')
            ->setDescription('Import products from DB')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("<info>Import started.</info>");
        $startTime  = time();
        $importer   = $this->getContainer()->get('product_importer');
        $dbImporter = $this->getContainer()->get('db_importer');
        $importer->import($dbImporter);

        $endTime   = time();
        $totalTime = $endTime - $startTime;

        $output->writeln("<info>Import finished. Total time: $totalTime seconds</info>");
    }
}
