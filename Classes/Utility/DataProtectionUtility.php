<?php

namespace RKW\RkwRegistration\Utility;

use RKW\RkwBasics\Utility\FrontendSimulatorUtility;
use RKW\RkwRegistration\Domain\Model\EncryptedData;
use RKW\RkwRegistration\Domain\Model\FrontendUser;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use \TYPO3\CMS\Extbase\Persistence\Generic\Mapper\ColumnMap;
use RKW\RkwBasics\Utility\GeneralUtility;
use \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

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

/**
 * Class DataProtectionUtility
 *
 * @deprecated This class is deprecated and will be removed soon. Use  RKW\RkwRegistration\DataProtection\DataProtectionHandler instead.
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class DataProtectionUtility extends \RKW\RkwRegistration\DataProtection\DataProtectionHandler
{
    /**
     * DataProtectionUtility constructor.
     */
    public function __construct()
    {
        trigger_error('This class "' . __CLASS__ . '" is deprecated and will be removed soon. Do not use it anymore.', E_USER_DEPRECATED);
    }
}
