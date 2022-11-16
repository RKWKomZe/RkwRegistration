<?php
namespace RKW\RkwRegistration\Registration;

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

use RKW\RkwRegistration\Domain\Model\BackendUser;
use RKW\RkwRegistration\Domain\Model\FrontendUser;
use RKW\RkwRegistration\Domain\Model\OptIn;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class RegistrationInterface
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
interface RegistrationInterface
{

    /**
     * Get the frontendUser object
     *
     * @return \RKW\RkwRegistration\Domain\Model\FrontendUser|null
     * @api
     */
    public function getFrontendUser(): ?FrontendUser;


    /**
     * Set the frontendUser object
     *
     * @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @return self
     * @api
     */
    public function setFrontendUser(FrontendUser $frontendUser): self;


    /**
     * Get the frontendUserToken
     *
     * @return string
     */
    public function getFrontendUserToken(): string;


    /**
     * Set the frontendUserToken
     *
     * @param string $frontendUserToken
     * @return self
     */
    public function setFrontendUserToken(string $frontendUserToken): self;


    /**
     * Gets the data of the frontendUser that is to be updated after optIn
     * @return array
     */
    public function getFrontendUserOptInUpdate(): array;


    /**
     * @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @var array $ignoreProperties
     * @return self
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception\TooDirtyException
     */
    public function setFrontendUserOptInUpdate(
        FrontendUser $frontendUser,
        array $ignoreProperties = [
            'uid', 'username', 'password', 'disable', 'deleted',
            'crdate', 'tstamp', 'starttime', 'endtime', 'usergroup'
        ]): self;


    /**
     * Get the persisted frontendUser object
     *
     * @return \RKW\RkwRegistration\Domain\Model\FrontendUser|null
     */
    public function getFrontendUserPersisted(): ?FrontendUser;


    /**
     * @return \RKW\RkwRegistration\Domain\Model\OptIn|null
     */
    public function getOptInPersisted(): ?OptIn;


    /**
     * @return \TYPO3\CMS\Extbase\Mvc\Request|null $request
     * @api
     */
    public function getRequest(): ?Request;


    /**
     * @var \TYPO3\CMS\Extbase\Mvc\Request $request
     * @return self
     * @api
     */
    public function setRequest(Request $request): self;


    /**
     * Adds a backendUser for the approval
     *
     * @param \RKW\RkwRegistration\Domain\Model\BackendUser for the approval $backendUser
     * @return void
     * @api
     */
    public function addApproval(BackendUser $backendUser): void;


    /**
     * Removes a backendUser for the approval
     *
     * @param \RKW\RkwRegistration\Domain\Model\BackendUser $backendUser
     * @return void
     * @api
     */
    public function removeApproval(BackendUser $backendUser): void;


    /**
     * Returns the backend user.
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwRegistration\Domain\Model\BackendUser>
     * @api
     */
    public function getApproval(): ObjectStorage;


    /**
     * Sets the backend user.
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwRegistration\Domain\Model\BackendUser> $backendUsers
     * @return void
     * @api
     */
    public function setApproval(ObjectStorage $backendUsers);


    /**
     * @return mixed|null
     */
    public function getData();


    /**
     * @var mixed $data
     * @return self
     */
    public function setData($data): self;


    /**
     * @return string
     */
    public function getCategory(): string;


    /**
     * @var string $category
     * @return self
     */
    public function setCategory(string $category): self;


    /**
     * Creates an opt-in for a frontendUser
     *
     * @return \RKW\RkwRegistration\Domain\Model\OptIn
     * @throws \Exception
     * @throws \RKW\RkwRegistration\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception\NotImplementedException
     * @api
     */
    public function createOptIn(): OptIn;


    /**
     * Registers a frontendUser
     *
     * @return bool
     * @api
     */
    public function startRegistration(): bool;


    /**
     * Completes the registration of the frontendUser
     *
     * @return bool
     * @api
     */
    public function completeRegistration(): bool;


    /**
     * Cancels the registration of the frontendUser
     *
     * @return bool
     * @api
     */
    public function cancelRegistration(): bool;


    /**
     * End the registration of the frontendUser
     *
     * @return bool
     * @api
     */
    public function endRegistration(): bool;

}
