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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

/**
 * class SendCommand
 *
 * Execute on CLI with: 'vendor/bin/typo3 rkw_registration:cleanup'
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class CleanupCommand extends Command
{

    /**
     * @var \RKW\RkwRegistration\Domain\Repository\OptinRepository|null
     */
    protected ?OptinRepository $optInRepository = null;


    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager|null
     */
    protected ?PersistenceManager $persistenceManager = null;


    /**
     * @var \RKW\RkwRegistration\DataProtection\DataProtectionHandler|null
     */
    protected ?DataProtectionHandler $dataProtectionHandler = null;


    /**
     * @var \TYPO3\CMS\Core\Log\Logger|null
     */
    protected ?Logger $logger = null;


    /**
     * Configure the command by defining the name, options and arguments
     */
    protected function configure(): void
    {
        $this->setDescription('Removes expired optIns and frontendUsers.')
            ->addOption(
                'daysSinceExpired',
                'd',
                InputOption::VALUE_REQUIRED,
                'Days since optIns and frontendUsers are expired.',
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
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @see \Symfony\Component\Console\Input\InputInterface::bind()
     * @see \Symfony\Component\Console\Input\InputInterface::validate()
     */
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager$objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        $this->dataProtectionHandler = $objectManager->get(DataProtectionHandler::class);
        $this->optInRepository = $objectManager->get(OptInRepository::class);
        $this->persistenceManager = $objectManager->get(PersistenceManager::class);

    }


    /**
     * Executes the command for showing sys_log entries
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     * @see \Symfony\Component\Console\Input\InputInterface::bind()
     * @see \Symfony\Component\Console\Input\InputInterface::validate()
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title($this->getDescription());

        $daysSinceExpired = $input->getOption('daysSinceExpired');

        $io->note('Using daysSinceExpired="' . $daysSinceExpired .'"');

        $result = 0;
        try {

            $expiredOptIns = $this->optInRepository->findExpired($daysSinceExpired);
            $cnt = 0;
            foreach ($expiredOptIns as $optIn) {
                $this->optInRepository->remove($optIn);
                $cnt++;
            }
            $this->persistenceManager->persistAll();
            $message = 'Removed ' . $cnt . ' optIn(s).';
            $io->note($message);
            $this->getLogger()->log(LogLevel::INFO, $message);


            $cnt = $this->dataProtectionHandler->deleteAllExpiredAndDisabled($daysSinceExpired);
            $message = 'Removed ' . $cnt . ' frontendUser(s).';
            $io->note($message);
            $this->getLogger()->log(LogLevel::INFO, $message);


        } catch (\Exception $e) {

            $message = sprintf('An unexpected error occurred while trying to cleanup expired optIns and frontendUsers: %s',
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
    protected function getLogger(): Logger
    {
        if (!$this->logger instanceof \TYPO3\CMS\Core\Log\Logger) {
            $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
        }

        return $this->logger;
    }
}
