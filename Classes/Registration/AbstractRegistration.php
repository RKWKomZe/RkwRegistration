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

use RKW\RkwBasics\Utility\GeneralUtility;
use RKW\RkwRegistration\Domain\Model\BackendUser;
use RKW\RkwRegistration\Domain\Model\FrontendUser;
use RKW\RkwRegistration\Domain\Model\GuestUser;
use RKW\RkwRegistration\Domain\Model\OptIn;
use RKW\RkwRegistration\Domain\Repository\FrontendUserRepository;
use RKW\RkwRegistration\Utility\FrontendUserUtility;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Class AbstractRegistration
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
abstract class AbstractRegistration implements RegistrationInterface
{

    /**
     * @var \RKW\RkwRegistration\Domain\Model\FrontendUser
     */
    protected $frontendUser;


    /**
     * @var \RKW\RkwRegistration\Domain\Model\FrontendUser
     */
    protected $frontendUserPersisted;


    /**
     * @var string
     */
    protected $frontendUserToken = '';


    /**
     * @var array
     */
    protected $frontendUserOptInUpdate = [];


    /**
     * @var \RKW\RkwRegistration\Domain\Model\OptIn
     */
    protected $optInPersisted;


    /**
     * @var \TYPO3\CMS\Extbase\Mvc\Request
     */
    protected $request;


    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwRegistration\Domain\Model\BackendUser>
     */
    protected $approval;


    /**
     * @var mixed
     */
    protected $data;


    /**
     * @var string
     */
    protected $category = '';


    /**
     * @var \RKW\RkwRegistration\Domain\Repository\OptInRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $optInRepository;


    /**
     * @var \RKW\RkwRegistration\Domain\Repository\FrontendUserRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $frontendUserRepository;


    /**
     * @var \RKW\RkwRegistration\Domain\Repository\GuestUserRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $guestUserRepository;


    /**
     * @var \RKW\RkwRegistration\Domain\Repository\FrontendUserGroupRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $frontendUserGroupRepository;


    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $persistenceManager;


    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $objectManager;


    /**
     * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected $signalSlotDispatcher;


    /**
     * @var array
     */
    protected $settings;


    /**
     * @var Logger
     */
    protected $logger;


    /**
     * __construct
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function __construct()
    {
        $this->approval = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        $this->getSettings();
    }


    /**
     * @return \RKW\RkwRegistration\Domain\Model\FrontendUser|null $frontendUser
     */
    public function getFrontendUser(): ?FrontendUser
    {
        return $this->frontendUser;
    }


    /**
     * @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @return self
     */
    public function setFrontendUser(FrontendUser $frontendUser): self
    {
        $this->frontendUser = $frontendUser;
        $this->frontendUserToken = '';
        $this->frontendUserPersisted = null;
        $this->optInPersisted = null;
        return $this;

    }

    /**
     * Get the frontendUserToken
     *
     * @return string
     */
    public function getFrontendUserToken(): string
    {
        return $this->frontendUserToken;
    }


    /**
     * Set the frontendUserToken
     *
     * @param string $frontendUserToken
     * @return self
     */
    public function setFrontendUserToken(string $frontendUserToken): self
    {
        $this->frontendUserToken = $frontendUserToken;
        $this->frontendUserPersisted = null;
        $this->frontendUser = null;
        $this->optInPersisted = null;
        return $this;
    }


    /**
     * @return array
     */
    public function getFrontendUserOptInUpdate(): array
    {
        return $this->frontendUserOptInUpdate;
    }


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
        ]): self {

        // take array to reduce size in the database
        // remove all evil properties !!!
        $this->frontendUserOptInUpdate = array_diff_key(
            FrontendUserUtility::convertObjectToArray($frontendUser, true),
            array_flip($ignoreProperties)
        );

        return $this;
    }


    /**
     * Returns the clean and save frontendUser from the database
     *
     * @return \RKW\RkwRegistration\Domain\Model\FrontendUser|null
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception\NotImplementedException
     */
    public function getFrontendUserPersisted(): ?FrontendUser
    {
        if (!$this->frontendUserPersisted) {

            // load by frontendUser
            if ($this->frontendUser) {

                // sad but true: we have to clear the persistence cache here in order to get the object new from the database!
                /** @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager $persistenceManager */
                $persistenceManager = $this->objectManager->get(PersistenceManager::class);
                $persistenceManager->clearState();

                if ($this->frontendUser->getUid()) {
                    /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser frontendUserPersisted */
                    $this->frontendUserPersisted = $this->getContextAwareFrontendUserRepository()->findByIdentifierIncludingDisabled($this->frontendUser->getUid());
                } else {
                    /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser frontendUserPersisted */
                    $this->frontendUserPersisted = $this->getContextAwareFrontendUserRepository()->findOneByUsernameIncludingDisabled($this->frontendUser->getUsername());
                }

            // load by token
            } else if ($optIn = $this->getOptInPersisted()) {
                /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser frontendUserPersisted */
                $this->frontendUserPersisted = $this->getContextAwareFrontendUserRepository()->findByIdentifierIncludingDisabled($optIn->getFrontendUserUid());
            }
        }
        return $this->frontendUserPersisted;
    }


    /**
     * @return \RKW\RkwRegistration\Domain\Model\OptIn|null
     */
    public function getOptInPersisted(): ?OptIn
    {
        if (
            (! $this->optInPersisted)
            && ($this->frontendUserToken)
        ) {

            /** @var \RKW\RkwRegistration\Domain\Model\OptIn optInPersisted */
            $this->optInPersisted = $this->optInRepository->findOneByTokenUserIncludingDeleted($this->frontendUserToken);
        }

        return $this->optInPersisted;
    }



    /**
     * @return \TYPO3\CMS\Extbase\Mvc\Request|null
     */
    public function getRequest(): ?Request
    {
        return $this->request;
    }


    /**
     * @var \TYPO3\CMS\Extbase\Mvc\Request $request
     * @return self
     */
    public function setRequest(Request $request): self
    {
        $this->request = $request;
        return $this;
    }


    /**
     * Adds a backendUser for the approval
     *
     * @param \RKW\RkwRegistration\Domain\Model\BackendUser for the approval $backendUser
     * @return void
     * @api
     */
    public function addApproval(BackendUser $backendUser): void
    {
        $this->approval->attach($backendUser);
    }


    /**
     * Removes a backendUser for the approval
     *
     * @param \RKW\RkwRegistration\Domain\Model\BackendUser $backendUser
     * @return void
     * @api
     */
    public function removeApproval(BackendUser $backendUser): void
    {
        $this->approval->detach($backendUser);
    }


    /**
     * Returns the backend users for the approval
     *
     * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwRegistration\Domain\Model\BackendUser>
     * @api
     */
    public function getApproval(): ObjectStorage
    {
        return $this->approval;
    }


    /**
     * Sets the backend users for the approval
     *
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\RKW\RkwRegistration\Domain\Model\BackendUser> $backendUsers
     * @return void
     * @api
     */
    public function setApproval(ObjectStorage $backendUsers)
    {
        $this->approval = $backendUsers;
    }


    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }


    /**
     * @var mixed $data
     * @return self
     */
    public function setData($data): self
    {
        $this->data = $data;
        return $this;
    }


    /**
     * @return string
     */
    public function getCategory(): string
    {
        return $this->category;
    }


    /**
     * @var string $category
     * @return self
     */
    public function setCategory(string $category): self
    {
        $this->category = $category;
        return $this;
    }


    /**
     * Returns repository that belongs to the given frontendUserType
     *
     * @var string $category
     * @return \RKW\RkwRegistration\Domain\Repository\FrontendUserRepository
     */
    public function getContextAwareFrontendUserRepository(): FrontendUserRepository
    {
        if ($this->frontendUser instanceof GuestUser) {
            return $this->guestUserRepository;
        }

        return $this->frontendUserRepository;
    }


    /**
     * Returns TYPO3 settings
     *
     * @param string $type
     * @return array
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    protected function getSettings($type = \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS): array
    {
        if (!$this->settings) {
            $this->settings = GeneralUtility::getTyposcriptConfiguration('Rkwregistration', $type);
        }

        if ($this->settings) {
            return  $this->settings;
        }
        return [];
    }


    /**
     * Returns logger instance
     *
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    protected function getLogger(): Logger
    {
        if (!$this->logger instanceof Logger) {
            $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
        }

        return $this->logger;
    }

}
