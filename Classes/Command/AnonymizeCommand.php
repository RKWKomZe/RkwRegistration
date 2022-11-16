<?php
namespace RKW\RkwRegistration\Command;
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use RKW\RkwRegistration\DataProtection\DataProtectionHandler;
use RKW\RkwRegistration\Domain\Repository\OptInRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

/**
 * class SendCommand
 *
 * Execute on CLI with: 'vendor/bin/typo3 rkw_registration:anonymize'
 */
class AnonymizeCommand extends Command
{


    /**
     * dataProtectionRepository
     *
     * @var \RKW\RkwRegistration\DataProtection\DataProtectionHandler
     */
    protected $dataProtectionHandler;



    /**
     * @var \TYPO3\CMS\Core\Log\Logger
     */
    protected $logger;


    /**
     * Configure the command by defining the name, options and arguments
     */
    protected function configure(): void
    {
        $this->setDescription('Anonymizes deleted frontendUsers and corresponding objects by encrypting their data.')
            ->addArgument(
                'encryptionKey',
                InputArgument::REQUIRED,
                'Key to use for encryption.',
            )
            ->addOption(
                'daysSinceDeleted',
                'd',
                InputOption::VALUE_REQUIRED,
                'Days since frontendUsers have been deleted.',
                7
            );
    }

    /**
     * Initializes the command after the input has been bound and before the input
     * is validated.
     *
     * This is mainly useful when a lot of commands extends one main command
     * where some things need to be initialized based on the input arguments and options.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @see InputInterface::bind()
     * @see InputInterface::validate()
     */
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager$objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        $this->dataProtectionHandler = $objectManager->get(DataProtectionHandler::class);

    }


    /**
     * Executes the command for showing sys_log entries
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @see InputInterface::bind()
     * @see InputInterface::validate()
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title($this->getDescription());

        $encryptionKey = $input->getArgument('encryptionKey');
        $daysSinceDeleted = $input->getOption('daysSinceDeleted');

        $io->note('Using encryptionKey="' . $encryptionKey .
            '" and daysSinceDeleted="' . $daysSinceDeleted . '"'
        );

        $result = 0;
        try {

            $message = 'No data to anonymize.';

            $this->dataProtectionHandler->setEncryptionKey($encryptionKey);
            if ($this->dataProtectionHandler->anonymizeAndEncryptAll($daysSinceDeleted)) {
                $message = 'Successfully anonymized data.';
            }
            $io->note($message);
            $this->getLogger()->log(LogLevel::INFO, $message);

        } catch (\Exception $e) {

            $message = sprintf('An unexpected error occurred while trying to anonymize data: %s',
                str_replace(array("\n", "\r"), '', $e->getMessage())
            );

            $io->error($message);
            $this->getLogger()->log(LogLevel::ERROR, $message);
        }

        $io->writeln('Done');
        return $result;

    }


    /**
     * Returns logger instance
     *
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    protected function getLogger(): \TYPO3\CMS\Core\Log\Logger
    {
        if (!$this->logger instanceof \TYPO3\CMS\Core\Log\Logger) {
            $this->logger = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
        }

        return $this->logger;
    }
}
