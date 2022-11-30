<?php
namespace RKW\RkwRegistration\Utility;

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

use \TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Class StringUtility
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class StringUtility
{

    /**
     * @const int
     */
    const RANDOM_STRING_LENGTH = 30;


    /**
     * creates a random string of the defined length
     *
     * @return string
     * @throws \Exception
     */
    public static function getUniqueRandomString(): string
    {
        /** @see https://www.php.net/manual/en/function.random-bytes.php */
        $bytes = random_bytes(self::RANDOM_STRING_LENGTH / 2);
        return bin2hex($bytes);
    }

}
