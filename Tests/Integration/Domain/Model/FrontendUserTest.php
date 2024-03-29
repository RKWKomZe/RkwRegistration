<?php
namespace RKW\RkwRegistration\Tests\Integration\Domain\Model;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use RKW\RkwRegistration\Domain\Model\FrontendUser;
use RKW\RkwRegistration\Domain\Model\Title;
use RKW\RkwRegistration\Domain\Repository\FrontendUserRepository;
use RKW\RkwRegistration\Domain\Repository\TitleRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

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
 * FrontendUserTest
 *
 * @author Christian Dilger <c.dilger@addorange.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @todo rework!
 */
class FrontendUserTest extends FunctionalTestCase
{

    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/FrontendUserTest/Fixtures';


    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/ajax_api',
        'typo3conf/ext/core_extended',
        'typo3conf/ext/rkw_registration',
    ];


    /**
     * @var string[]
     */
    protected $coreExtensionsToLoad = [];


    /**
     * @var \RKW\RkwRegistration\Domain\Model\FrontendUser|null
     */
    private ?FrontendUser $frontendUser = null;


    /**
     * @var \RKW\RkwRegistration\Domain\Model\Title|null
     */
    private ?Title $title = null;


    /**
     * @var \RKW\RkwRegistration\Domain\Repository\FrontendUserRepository|null
     */
    private ?FrontendUserRepository $frontendUserRepository;


    /**
     * @var \RKW\RkwRegistration\Domain\Repository\TitleRepository|null
     */
    private ?TitleRepository $titleRepository;


    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager|null
     */
    private ?PersistenceManager $persistenceManager = null;


    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager|null
     */
    private ?ObjectManager $objectManager = null;


    /**
     * Setup
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Global.xml');

        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:rkw_registration/Configuration/TypoScript/setup.txt',
            ]
        );

        $this->persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->frontendUserRepository = $this->objectManager->get(FrontendUserRepository::class);
        $this->titleRepository = $this->objectManager->get(TitleRepository::class);

        $this->frontendUser = $this->objectManager->get(FrontendUser::class);
        $this->title = $this->objectManager->get(Title::class);

    }

    //=============================================

    /**
     * @test
     */
    public function setTxRkwregistrationWithParameterRegistrationTitleEqualsNullDoesNotSetATitle()
    {

        $fixture = '';

        $this->title = null;

        $this->frontendUser->setFirstName("Erika");
        $this->frontendUser->setLastName("Musterfrau");
        $this->frontendUser->setTxRkwregistrationTitle($this->title);

        self::assertEquals($fixture, $this->frontendUser->getTxRkwregistrationTitle()->getName());

    }

    //=============================================

    /**
     * TearDown
     */
    protected function teardown(): void
    {
        parent::tearDown();
    }

}
