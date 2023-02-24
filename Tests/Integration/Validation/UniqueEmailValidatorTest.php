<?php
namespace RKW\RkwRegistration\Tests\Integration\Validation;

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

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use Madj2k\CoreExtended\Utility\FrontendSimulatorUtility;
use Madj2k\CoreExtended\Utility\GeneralUtility;
use RKW\RkwRegistration\Domain\Model\FrontendUser;
use RKW\RkwRegistration\Domain\Repository\FrontendUserGroupRepository;
use RKW\RkwRegistration\Domain\Repository\FrontendUserRepository;
use RKW\RkwRegistration\Utility\FrontendUserSessionUtility;
use RKW\RkwRegistration\Validation\UniqueEmailValidator;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Class UniqueEmailValidatorTest
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class UniqueEmailValidatorTest extends FunctionalTestCase
{

    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/UniqueEmailValidatorTest/Fixtures';


    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/ajax_api',
        'typo3conf/ext/core_extended',
        'typo3conf/ext/rkw_registration'
    ];


    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager|null
     */
    private ?ObjectManager $objectManager = null;


    /**
     * @var \RKW\RkwRegistration\Domain\Repository\FrontendUserRepository|null
     */
    private ?FrontendUserRepository $frontendUserRepository = null;


    /**
     * @var \RKW\RkwRegistration\Domain\Repository\FrontendUserGroupRepository|null
     */
    private ?FrontendUserGroupRepository $frontendUserGroupRepository = null;


    /**
     * Setup
     * @throws \Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Global.xml');
        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:core_extended/Configuration/TypoScript/setup.typoscript',
                'EXT:rkw_registration/Configuration/TypoScript/setup.typoscript',
                self::FIXTURE_PATH . '/Frontend/Configuration/Rootpage.typoscript',
            ]
        );

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        /** @var  \RKW\RkwRegistration\Domain\Repository\FrontendUserRepository frontendUserRepository */
        $this->frontendUserRepository = $this->objectManager->get(FrontendUserRepository::class);

        /** @var  \RKW\RkwRegistration\Domain\Repository\FrontendUserGroupRepository frontendUserGroupRepository */
        $this->frontendUserGroupRepository = $this->objectManager->get(FrontendUserGroupRepository::class);

        FrontendSimulatorUtility::simulateFrontendEnvironment(1);
    }

    #==============================================================================

    /**
     * @test
     */
    public function isValidWithInvalidEmailReturnsFalse ()
    {

        /**
         * Scenario:
         *
         * Given is a persistent frontendUser with a valid email-address
         * Given a new frontendUser with a different, but invalid email-address
         * When the validator is called
         * Then false is returned
         */

        /** @var \RKW\RkwRegistration\Validation\UniqueEmailValidator $uniqueEmailValidator */
        $uniqueEmailValidator = $this->objectManager->get(UniqueEmailValidator::class);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->objectManager->get(FrontendUser::class);
        $frontendUser->setEmail('lauterbach');

        // workaround start: for creating $this->result of the validator
        $uniqueEmailValidator->validate($frontendUser);
        // workaround end

        $result = $uniqueEmailValidator->isValid($frontendUser);

        self::assertFalse($result);

    }


    /**
     * @test
     */
    public function isValidWithExistingUserReturnsTrue ()
    {
        /**
         * Scenario:
         *
         * Given is a persistent frontendUser with a valid email-address
         * Given is that frontendUser is logged in
         * When the validator is called
         * Then true is returned
         */

        /** @var \RKW\RkwRegistration\Validation\UniqueEmailValidator $uniqueEmailValidator */
        $uniqueEmailValidator = $this->objectManager->get(UniqueEmailValidator::class);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->frontendUserRepository->findByUid(1);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUserGroup $frontendUserGroup */
        $frontendUserGroup = $this->frontendUserGroupRepository->findByUid(1);

        FrontendUserSessionUtility::simulateLogin($frontendUser, $frontendUserGroup);

        // workaround start: for creating $this->result of the validator
        $uniqueEmailValidator->validate($frontendUser);
        // workaround end

        $result = $uniqueEmailValidator->isValid($frontendUser);

        self::assertTrue($result);
    }


    /**
     * @test
     */
    public function isValidWithAlreadyExistingEmailReturnsFalse ()
    {

        /**
         * Scenario:
         *
         * Given is a persistent frontendUser with a valid email-address
         * Given a new frontendUser with the same valid email-address
         * When the validator is called
         * Then false is returned
         */

        /** @var \RKW\RkwRegistration\Validation\UniqueEmailValidator $uniqueEmailValidator */
        $uniqueEmailValidator = $this->objectManager->get(UniqueEmailValidator::class);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->objectManager->get(FrontendUser::class);
        $frontendUser->setEmail('lauterbach@spd.de');

        // workaround start: for creating $this->result of the validator
        $uniqueEmailValidator->validate($frontendUser);
        // workaround end

        $result = $uniqueEmailValidator->isValid($frontendUser);

        self::assertFalse($result);
    }


    /**
     * @test
     */
    public function isValidReturnsTrue ()
    {

        /**
         * Scenario:
         *
         * Given is a persistent frontendUser with a valid email-address
         * Given a new frontendUser with a different valid email-address
         * When the validator is called
         * Then true is returned
         */

        /** @var \RKW\RkwRegistration\Validation\UniqueEmailValidator $uniqueEmailValidator */
        $uniqueEmailValidator = $this->objectManager->get(UniqueEmailValidator::class);

        /** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
        $frontendUser = $this->objectManager->get(FrontendUser::class);
        $frontendUser->setEmail('merkel@cdu.de');

        // workaround start: for creating $this->result of the validator
        $uniqueEmailValidator->validate($frontendUser);
        // workaround end

        $result = $uniqueEmailValidator->isValid($frontendUser);

        self::assertTrue($result);
    }

    #==============================================================================

    /**
     * TearDown
     */
    protected function teardown(): void
    {
        FrontendSimulatorUtility::resetFrontendEnvironment();
        parent::tearDown();
    }

}
