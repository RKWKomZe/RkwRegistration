<?php

namespace RKW\RkwRegistration\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\QueryInterface;

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
 * FrontendUserRepository
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class FrontendUserRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{
    /**
     * initializeObject
     *
     * @return void
     */
    public function initializeObject()
    {

        /** @var $querySettings \TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings */
        $querySettings = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Typo3QuerySettings');

        // don't add the pid constraint and enable fields
        $querySettings->setRespectStoragePage(false);
        $this->setDefaultQuerySettings($querySettings);
    }


    /**
     * Loads registered user from database
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @return \RKW\RkwRegistration\Domain\Model\FrontendUser | NULL
     */
    public function findUser(\RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser)
    {

        if ($frontendUser = $this->findOneByUsername(strtolower($frontendUser->getUsername()))) {
            return $frontendUser;
            //===
        }

        return null;
        //===
    }


    /**
     * Checks if user is already registered
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @return boolean
     */
    public function isUser(\RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser)
    {

        if ($this->findUser($frontendUser)) {
            return true;
            //===
        }

        return false;
        //===
    }


    /**
     * Finds non-anonymous users
     *
     * @param integer $uid
     * @return \RKW\RkwRegistration\Domain\Model\FrontendUser
     */
    public function findByUidNoAnonymous($uid)
    {

        $query = $this->createQuery();
        $user = $query->matching(
            $query->logicalAnd(
                $query->equals('uid', $uid),
                $query->equals('txRkwregistrationIsAnonymous', 0)
            )
        )->setLimit(1)
            ->execute();

        return $user->getFirst();
        //====
    }


    /**
     * Finds users which have the given username OR email-address
     * This is relevant for checking during registration or profile editing
     *
     * @param string $input
     * @return \RKW\RkwRegistration\Domain\Model\FrontendUser
     */
    public function findOneByEmailOrUsernameInactive($input)
    {

        $query = $this->createQuery();
        $query->getQuerySettings()->setIgnoreEnableFields(true);

        $user = $query->matching(
            $query->logicalOr(
                $query->equals('email', $input),
                $query->equals('username', $input)
            )
        )->setLimit(1)
            ->execute();

        return $user->getFirst();
        //====
    }


    /**
     * Finds inactive users - this is relevant for registration
     * This way no one can register twice (only when deleted)
     *
     * @param string $username
     * @return \RKW\RkwRegistration\Domain\Model\FrontendUser
     */
    public function findOneByUsernameInactive($username)
    {

        $query = $this->createQuery();
        $query->getQuerySettings()->setIgnoreEnableFields(true);

        $user = $query->matching(
            $query->equals('username', $username)
        )->setLimit(1)
            ->execute();

        return $user->getFirst();
        //====
    }


    /**
     * Finds anonymous users
     *
     * @param string $username
     * @return \RKW\RkwRegistration\Domain\Model\FrontendUser
     */
    public function findOneByToken($token)
    {

        $query = $this->createQuery();

        $user = $query->matching(
            $query->logicalAnd(
                $query->equals('username', $token),
                $query->equals('txRkwregistrationIsAnonymous', 1)
            )
        )->setLimit(1)
            ->execute();

        return $user->getFirst();
        //====
    }

    /**
     * Finds inactive users - this is relevant for registration
     * This way we can activate the user then
     *
     * @param string $uid
     * @return \RKW\RkwRegistration\Domain\Model\FrontendUser
     */
    public function findByUidInactiveNonAnonymous($uid)
    {

        $query = $this->createQuery();
        $query->getQuerySettings()->setIgnoreEnableFields(true);

        $user = $query->matching(
            $query->logicalAnd(
                $query->equals('uid', $uid),
                $query->equals('txRkwregistrationIsAnonymous', 0)
            )
        )->setLimit(1)
            ->execute();

        return $user->getFirst();
        //====
    }


    /**
     * Find all users that have been updated recently
     *
     * @param integer $timestamp
     * @param bool $excludeEmptyName
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @api Used by SOAP-API
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findByTimestamp($timestamp, $excludeEmptyName = true)
    {

        $query = $this->createQuery();
        $query->getQuerySettings()->setIncludeDeleted(true);
        $query->getQuerySettings()->setIgnoreEnableFields(true);

        $constrains = array(
            $query->greaterThanOrEqual('tstamp', intval($timestamp)),
        );

        // exclude feUsers without first- and last-name
        if ($excludeEmptyName) {
            $constrains[] = $query->logicalAnd(
                $query->logicalNot(
                    $query->equals('firstName', '')
                ),
                $query->logicalNot(
                    $query->equals('lastName', '')
                )
            );
        }

        $query->matching(
            $query->logicalAnd(
                $constrains
            )
        );

        $query->setOrderings(array('tstamp' => QueryInterface::ORDER_ASCENDING));

        return $query->execute();
        //===
    }


    /**
     * Find all expired users with optional timestamp tolerance
     *
     * @param int $tolerance Tolerance timestamp
     * @param bool $anonymousOnly Only return anonymous users
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @deprecated since 26.06.2018
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findExpired($tolerance = 0, $anonymousOnly = false)
    {
        \TYPO3\CMS\Core\Utility\GeneralUtility::deprecationLog(__CLASS__ . ': Do not use this method any more.');

        $query = $this->createQuery();
        $query->getQuerySettings()->setIgnoreEnableFields(true);

        $timestamp = time();
        if ($tolerance) {
            $timestamp = intval($tolerance);
        }

        $constraints = array(
            $query->greaterThan('endtime', 0),
            $query->lessThan('endtime', $timestamp),
        );

        if ($anonymousOnly) {
            $constraints[] = $query->equals('txRkwregistrationIsAnonymous', 1);
        }

        $query->matching(
            $query->logicalAnd($constraints)
        );

        return $query->execute();
        //===
    }


    /**
     * Find all deleted users with optional timestamp tolerance
     *
     * @param int $tolerance Tolerance timestamp
     * @param bool $anonymousOnly Only return anonymous users
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @deprecated since 26.06.2018
     */
    public function findDeletedOrDisabled($tolerance = 0, $anonymousOnly = false)
    {

        \TYPO3\CMS\Core\Utility\GeneralUtility::deprecationLog(__CLASS__ . ': Do not use this method any more.');

        $query = $this->createQuery();
        $query->getQuerySettings()->setIncludeDeleted(true);
        $query->getQuerySettings()->setIgnoreEnableFields(true);

        $timestamp = time();
        if ($tolerance) {
            $timestamp = intval($tolerance);
        }

        $constraints = array(
            $query->logicalOr(
                $query->equals('deleted', 1),
                $query->equals('disable', 1)
            ),
            $query->logicalAnd(
                $query->greaterThan('tstamp', 0),
                $query->lessThan('tstamp', $timestamp)
            ),
        );

        if ($anonymousOnly) {
            $constraints[] = $query->equals('txRkwregistrationIsAnonymous', 1);
        }

        $query->matching(
            $query->logicalAnd($constraints)
        );

        return $query->execute();
        //===
    }


    /**
     * Find all deleted or expired users with optional timestamp tolerance
     *
     * @param int $tolerance Tolerance timestamp
     * @param bool $anonymousOnly if true only anonymous users will be checked
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findExpiredOrDeletedByTstamp($tolerance = 0, $anonymousOnly = false)
    {

        $query = $this->createQuery();
        $query->getQuerySettings()->setIncludeDeleted(true);
        $query->getQuerySettings()->setIgnoreEnableFields(true);

        $timestamp = time();
        if ($tolerance) {
            $timestamp = intval($tolerance);
        }

        $constraints = array(
            $query->logicalOr(
                $query->logicalAnd(
                    $query->greaterThan('endtime', 0),
                    $query->lessThan('endtime', $timestamp)
                ),
                $query->logicalAnd(
                    $query->equals('deleted', 1),
                    $query->logicalAnd(
                        $query->greaterThan('tstamp', 0),
                        $query->lessThan('tstamp', $timestamp)
                    )
                )
            ),
        );

        if ($anonymousOnly) {
            $constraints[] = $query->equals('txRkwregistrationIsAnonymous', 1);
        }

        $query->matching(
            $query->logicalAnd($constraints)
        );

        return $query->execute();
        //===
    }

    /**
     * Find all expired frontend users that have been expired x days ago
     *
     * @param int $daysSinceExpire
     * @param int $base base for the calculation. if not set, time() is used
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findExpiredSinceDays ($daysSinceExpire = 0, $base = 0)
    {

        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);
        $query->getQuerySettings()->setIgnoreEnableFields(true);

        $base = ($base ? $base : time());
        $timestamp = $base - (intval($daysSinceExpire) * 24 * 60 * 60);

        $query->matching(
            $query->logicalAnd(
                $query->greaterThan('endtime', 0),
                $query->lessThanOrEqual('endtime', $timestamp)
            )
        );

        return $query->execute();
    }



    /**
     * Find all deleted frontend users that have been deleted x days ago and have not yet been anonymized/encrypted
     *
     * @param int $daysSinceDelete
     * @param int $base base for the calculation. if not set, time() is used
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findDeletedSinceDays ($daysSinceDelete = 0, $base = 0)
    {

        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);
        $query->getQuerySettings()->setIncludeDeleted(true);
        $query->getQuerySettings()->setIgnoreEnableFields(true);

        $base = ($base ? $base : time());
        $timestamp = $base - (intval($daysSinceDelete) * 24 * 60 * 60);

        $query->matching(
            $query->logicalAnd(
                $query->equals('deleted', 1),
                $query->lessThan('txRkwregistrationDataProtectionStatus', 1),
                $query->logicalAnd(
                    $query->greaterThan('tstamp', 0),
                    $query->lessThanOrEqual('tstamp', $timestamp)
                )
            )
        );

        return $query->execute();
    }




    /**
     * Finds an object matching the given identifier.
     *
     * @param int $uid The identifier of the object to find
     * @return \RKW\RkwRegistration\Domain\Model\FrontendUser The matching object if found, otherwise NULL
     * @api used by RKW Soap
     */
    public function findByUidSoap($uid)
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);
        $query->getQuerySettings()->setIncludeDeleted(true);
        $query->getQuerySettings()->setIgnoreEnableFields(true);

        $query->matching(
            $query->equals('uid', $uid)
        );

        $query->setLimit(1);

        return $query->execute()->getFirst();
    }



    /**
     * Delete user from DB (really!)
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @return void
     */
    public function removeHard(\RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser)
    {

        $GLOBALS['TYPO3_DB']->sql_query('
			DELETE FROM fe_users
			WHERE uid = ' . intval($frontendUser->getUid())
        );
    }
}
