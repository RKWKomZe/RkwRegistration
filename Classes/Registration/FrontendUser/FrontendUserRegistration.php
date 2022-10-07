<?php

namespace RKW\RkwRegistration\Registration\FrontendUser;
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

use RKW\RkwRegistration\DataProtection\PrivacyHandler;
use RKW\RkwRegistration\Exception;
use RKW\RkwRegistration\Utility\FrontendUserSessionUtility;
use RKW\RkwRegistration\Utility\PasswordUtility;
use TYPO3\CMS\Core\Log\LogLevel;

/**
 * FrontendUserRegistration
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class FrontendUserRegistration extends AbstractRegistration
{


    /**
     * Registers new FE-User - or sends another opt-in to existing user
     *
     * @return bool
     * @throws \RKW\RkwRegistration\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception\NotImplementedException
     * @api
     */
    public function startRegistration(): bool
    {

        // check for frontendUser-object
        if (!$this->getFrontendUser()) {
            throw new Exception('No frontendUser-object set.', 1434997734);
        }

        // check if a user is logged in. In this case no registration is needed!
        if (FrontendUserSessionUtility::getLoggedInUserId()) {
            throw new Exception('Registration is not necessary for logged in users.', 1659691717);
        }

        // Case 1: check if user already exists - no matter if enabled or disabled
        // then we generate an opt-in for additional data given
        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUserPersisted */
        if ($frontendUserPersisted = $this->getFrontendUserPersisted()) {

            // add opt in - but only if additional data is set!
            if ($this->getData()) {

                $this->createOptIn();

                $this->getLogger()->log(
                    LogLevel::INFO,
                    sprintf(
                        'Opt-in for existing user "%s" successfully generated (id=%s, category=%s).',
                        strtolower($frontendUserPersisted->getUsername()),
                        $frontendUserPersisted->getUid(),
                        $this->getCategory()
                    )
                );

                return true;
            }

            return false;
        }

        // Case 2: if user does not exist yet, we create it and set a temporary password
        $frontendUser = $this->getFrontendUser();
        $this->frontendUser->setTempPlaintextPassword(PasswordUtility::generatePassword());
        $this->frontendUser->setPassword(PasswordUtility::saltPassword($this->frontendUser->getTempPlaintextPassword()));

        $this->getContextAwareFrontendUserRepository()->add($frontendUser);
        $this->persistenceManager->persistAll();

        $this->createOptIn();

        $this->getLogger()->log(
            LogLevel::INFO,
            sprintf(
                'Successfully registered user "%s" (id=%s, category=%s). Awaiting opt-in.',
                strtolower($frontendUser->getUsername()),
                $frontendUser->getUid(),
                $this->getCategory()
            )
        );

        return true;
    }


    /**
     * Checks given tokens from e-mail
     *
     * @param string $token Token for consent or denial to check for
     * @return int returns several codes
     *          200= successfully accepted
     *          210 = successfully accepted, already processed
     *          220 = successfully accepted, approval on counterpart pending
     *          300 = successfully denied
     *          310 = successfully denied, already processed
     *          404 = Not found
     * @throws \RKW\RkwRegistration\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception\NotImplementedException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     * @api
     */
    public function validateOptIn(string $token): int
    {
        // check for frontendUserToken
        if (! $this->getFrontendUserToken()) {
            throw new Exception('No frontendUserToken set.', 1434997735);
        }

        if (
            ($optInPersisted = $this->getOptInPersisted())
            && ($frontendUserPersisted = $this->getFrontendUserPersisted())
        ) {

            // check if we are in god-mode
            $adminMode = ($token == $optInPersisted->getAdminTokenYes()) || ($token == $optInPersisted->getAdminTokenNo());

            if (
                ($token == $optInPersisted->getTokenYes())
                || ($token == $optInPersisted->getAdminTokenYes())
            ){

                // already processed!
                if ($optInPersisted->getDeleted()) {

                    $this->getLogger()->log(
                        LogLevel::WARNING, sprintf(
                            'OpIn with uid=%s is not valid any more.',
                            $optInPersisted->getUid(),
                        )
                    );

                    return 210;
                }

                $setter = 'setApproved';
                $signalSlot = self::SIGNAL_AFTER_APPROVAL_OPTIN;
                if ($adminMode) {
                    $setter = 'setAdminApproved';
                    $signalSlot = self::SIGNAL_AFTER_APPROVAL_OPTIN_ADMIN;
                }

                $optInPersisted->$setter(true);

                // we do NOT set a category-parameter here. We use the append-method instead.
                // This way we either send a mail from this extension or from another - never both!
                $this->dispatchSignalSlot($signalSlot . ucfirst($this->getCategory()));

                // add privacy entry
                if (! $adminMode) {

                    // add privacy for frontendUser
                    if ($request = $this->getRequest()) {
                        PrivacyHandler::addPrivacyDataForOptInFinal(
                            $request,
                            $frontendUserPersisted,
                            $optInPersisted,
                            ($optInPersisted->getCategory() ? 'accepted opt-in for ' . $optInPersisted->getCategory() : 'accepted opt-in')
                        );
                    }
                }

                // do the update
                $this->optInRepository->update($optInPersisted);
                $this->persistenceManager->persistAll();

                // waiting for approval on the counterpart?
                if (
                    (! $optInPersisted->getApproved())
                    || (! $optInPersisted->getAdminApproved())
                ){

                    $this->getLogger()->log(
                        LogLevel::INFO, sprintf(
                            'OptIn with uid=%s is waiting for approval on the counterpart.',
                            $optInPersisted->getUid(),
                        )
                    );

                    return 220;
                }

                // update frontendUser according to stored data
                // now that we have a valid optIn it is safe to persist the form-data in the frontendUser-object
                foreach ($optInPersisted->getFrontendUserUpdate() as $property => $value) {

                    $setter = 'set' . ucfirst($property);
                    if (method_exists($frontendUserPersisted, $setter)) {
                        $frontendUserPersisted->$setter($value);

                        $this->getLogger()->log(
                            LogLevel::INFO, sprintf(
                                'Updating field %s in frontendUser.',
                                $property
                            )
                        );
                    }
                }

                // synchronize frontendUser-objects!
                $this->frontendUser = $frontendUserPersisted;
                $this->frontendUserRepository->update($frontendUserPersisted);

                // complete registration-process
                $this->completeRegistration();

                // mark opt-in as deleted
                $this->optInRepository->remove($optInPersisted);
                $this->persistenceManager->persistAll();

                $this->getLogger()->log(
                    LogLevel::INFO,
                    sprintf(
                        'Opt-in with uid=%s was successfully accepted (frontendUser uid=%s, category=%s).',
                        $optInPersisted->getUid(),
                        $frontendUserPersisted->getUid(),
                        $optInPersisted->getCategory()
                    )
                );

                return 200;

            } else if (
                ($token == $optInPersisted->getTokenNo())
                || ($token == $optInPersisted->getAdminTokenNo())
            ){

                // already processed!
                if ($optInPersisted->getDeleted()) {

                    $this->getLogger()->log(
                        LogLevel::WARNING, sprintf(
                            'Opt-in with uid=%s is not valid any more.',
                            $optInPersisted->getUid(),
                        )
                    );

                    return 310;
                }

                // send e-mail to user
                $signalSlot = self::SIGNAL_AFTER_DENIAL_OPTIN;
                if ($adminMode) {
                    $signalSlot = self::SIGNAL_AFTER_DENIAL_OPTIN_ADMIN;
                }

                // we do NOT set a category-parameter here. We use the append-method instead.
                // This way we either send a mail from this extension or from another - never both!
                $this->dispatchSignalSlot($signalSlot . ucfirst($this->getCategory()));

                // cancel registration
                $this->cancelRegistration();

                // mark opt-in as deleted
                $this->optInRepository->remove($this->getOptInPersisted());
                $this->persistenceManager->persistAll();

                $this->getLogger()->log(
                    LogLevel::INFO,
                    sprintf(
                        'Opt-in with uid=%s was successfully canceled (frontendUser uid=%s, category=%s).',
                        $optInPersisted->getUid(),
                        $frontendUserPersisted->getUid(),
                        $optInPersisted->getCategory()
                    )
                );

                return 300;

            }
        }

        $this->getLogger()->log(
            LogLevel::WARNING,
            sprintf(
                'Opt-in or frontendUser for token "%s" can not be not found.',
                $this->getFrontendUserToken()
            )
        );

        return 404;
    }
}
