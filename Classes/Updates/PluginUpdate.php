<?php
namespace RKW\RkwRegistration\Updates;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Resource\Exception;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * Class PluginUpdate
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */

class PluginUpdate extends \TYPO3\CMS\Install\Updates\AbstractUpdate
{

    /**
     * @var string
     */
    protected $extensionKey = 'rkwRegistration';


    /**
     * @var string
     */
    protected $title = 'Updater for rkw_registration from flexform defined plugins to individual plugins.';

    /**
     * get plugins through checking unique flexForm string parts
     *
     * @var array
     */
    protected $flexFormPlugin = [
        'Register' => 'index="vDEF">Registration-&gt;registerShow;',
        'Welcome' => 'index="vDEF">Registration-&gt;welcome;',
        'AuthenticateInternal' => 'index="vDEF">Registration-&gt;loginShow;',
        'AuthenticateExternal' => 'index="vDEF">Registration-&gt;loginShowExternal;',
        'Password' => 'index="vDEF">Registration-&gt;editPassword;',
        'FrontendUserEdit' => 'index="vDEF">Registration-&gt;editUser;',
        'FrontendUserDelete' => 'index="vDEF">Registration-&gt;deleteUserShow;',
        'LogoutInternal' => 'index="vDEF">Registration-&gt;logout;',
        'LogoutExternal' => 'index="vDEF">Registration-&gt;logoutExternal;',
        'Service' => 'index="vDEF">Service-&gt;list;',
        'ServiceOptIn' => 'index="vDEF">Service-&gt;optIn;',
    ];


    /**
     * Checks whether updates are required.
     *
     * @param string $description The description for the update
     * @return bool Whether an update is required (TRUE) or not (FALSE)
     */
    public function checkForUpdate(&$description)
    {

        DebuggerUtility::var_dump($this->getTtContentElements()); exit;

        while ($record = $this->getTtContentElements()) {

            foreach ($this->flexFormPlugin as $flexFormSnippet) {
                $pos = strpos($record['pi_flexform'], $flexFormSnippet);
                if ($pos !== false) {
                    return true;
                }
            }
        }

        return false;
    }


    /**
     * Performs the required update.
     *
     * @param array $databaseQueries Queries done in this update
     * @param string $customMessage Custom message to be displayed after the update process finished
     * @return bool Whether everything went smoothly or not
     */
    public function performUpdate(array &$databaseQueries, &$customMessage)
    {

        return true;
    }



    /**
     * Checks whether updates are required.
     *
     * @return array
     */
    public function getTtContentElements()
    {
        /** @var  \TYPO3\CMS\Core\Database\Connection $connectionPages */
        $connectionPages = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tt_content');

        /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilderPages */
        $queryBuilderPages = $connectionPages->createQueryBuilder();

        $statement = $queryBuilderPages->select('uid', 'pid', 'list_type', 'pi_flexform')
            ->from('tt_content')
            ->where(
                $queryBuilderPages->expr()->eq('list_type',
                    $queryBuilderPages->createNamedParameter('rkwregistration_rkwregistration',  \PDO::PARAM_STR)
                )
            )
            ->execute();

        return $statement->fetch();
    }

}
