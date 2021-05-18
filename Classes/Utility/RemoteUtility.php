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

/**
 * Class RemoteUtility
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class RemoteUtility
{

    /**
     * Returns the users ip
     *
     * @return string
     */
    public static function getIp()
    {
        // set users server ip-address
        $remoteAddr = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);
        if ($_SERVER['HTTP_X_FORWARDED_FOR']) {
            $ips = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            if ($ips[0]) {
                $remoteAddr = filter_var($ips[0], FILTER_VALIDATE_IP);
            }
        }

        return $remoteAddr;
    }

}
