<?php
/**
 * @author Artem Bochkarev
 */

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportProductsXMLCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('app:import-products-xml')
            ->setDescription('Import products from XML')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("<info>Import started.</info>");
        $startTime   = time();
        $container   = $this->getContainer();
        $importer    = $container->get('product_importer');
        $xmlImporter = $container->get('xml_importer');
        $importer->import($xmlImporter);

        $endTime   = time();
        $totalTime = $endTime - $startTime;

        $output->writeln("<info>Import finished. Total time: $totalTime seconds</info>");
    }
}
