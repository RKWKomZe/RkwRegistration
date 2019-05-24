<?php
namespace RKW\RkwRegistration\Tests\Functional\Domain;

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
 * TitleTest
 *
 * @author Christian Dilger <c.dilger@addorange.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class TitleTest extends FunctionalTestCase
{

    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/rkw_basics',
        'typo3conf/ext/rkw_registration',
    ];

    /**
     * @var string[]
     */
    protected $coreExtensionsToLoad = [];

    /**
     * @var \RKW\RkwRegistration\Domain\Model\FrontendUser
     */
    private $frontendUser = null;

    /**
     * @var \RKW\RkwRegistration\Domain\Model\Title
     */
    private $title = null;

    /**
     * @var \RKW\RkwRegistration\Domain\Repository\FrontendUserRepository
     */
    private $frontendUserRepository;

    /**
     * @var \RKW\RkwRegistration\Domain\Repository\TitleRepository
     */
    private $titleRepository;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     */
    private $persistenceManager = null;

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     */
    private $objectManager = null;

    /**
     * Setup
     */
    protected function setUp()
    {
        parent::setUp();

        $this->importDataSet(__DIR__ . '/../Fixtures/Database/Pages.xml');

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
    public function titlePropertyIsIncludedInSalutationIsPersistedToTheDatabase()
    {
        $fixture['isIncludedInSalutation'] = true;

        $this->title->setName("Dr. med.");
        $this->title->setIsIncludedInSalutation($fixture['isIncludedInSalutation']);

        $this->titleRepository->add($this->title);

        $this->persistenceManager->persistAll();

        $databaseResult = $this->getDatabaseConnection()->selectSingleRow('*', 'tx_rkwregistration_domain_model_title','name = "Dr. med."');

        $this->assertFalse(empty($databaseResult));
        $this->assertTrue(isset($databaseResult['is_included_in_salutation']));
        $this->assertEquals($fixture['isIncludedInSalutation'], (bool) $databaseResult['is_included_in_salutation']);
    }

    /**
     * @test
     */
    public function titlePropertyNameFemaleIsPersistedToTheDatabase()
    {
        $fixture['nameFemale'] = "Dipl.-Kauffrau";

        $this->title->setName("Dipl.-Kaufmann");
        $this->title->setNameFemale($fixture['nameFemale']);

        $this->titleRepository->add($this->title);

        $this->persistenceManager->persistAll();

        $databaseResult = $this->getDatabaseConnection()->selectSingleRow('*', 'tx_rkwregistration_domain_model_title','name = "Dipl.-Kaufmann"');

        $this->assertFalse(empty($databaseResult));
        $this->assertTrue(isset($databaseResult['name_female']));
        $this->assertEquals($fixture['nameFemale'], $databaseResult['name_female']);
    }

    /**
     * @test
     */
    public function titlePropertyNameFemaleLongIsPersistedToTheDatabase()
    {
        $fixture['nameFemale'] = "Dipl.-Kauffrau";
        $fixture['nameFemaleLong'] = "Diplom-Kauffrau";

        $this->title->setName("Dipl.-Kaufmann");
        $this->title->setNameFemale($fixture['nameFemale']);
        $this->title->setNameFemaleLong($fixture['nameFemaleLong']);

        $this->titleRepository->add($this->title);

        $this->persistenceManager->persistAll();

        $databaseResult = $this->getDatabaseConnection()->selectSingleRow('*', 'tx_rkwregistration_domain_model_title','name = "Dipl.-Kaufmann"');

        $this->assertFalse(empty($databaseResult));
        $this->assertTrue(isset($databaseResult['name_female']));
        $this->assertTrue(isset($databaseResult['name_female_long']));
        $this->assertEquals($fixture['nameFemale'], $databaseResult['name_female']);
        $this->assertEquals($fixture['nameFemaleLong'], $databaseResult['name_female_long']);
    }

    /**
     * @test
     */
    public function aTitleWithIsAfterEqualsTrueIsRenderedAfterFullName()
    {

        $fixture = "Herr Max Mustermann, Magister artium";

        $this->title->setName("Magister artium");
        $this->title->setIsTitleAfter(true);
        $this->title->setIsIncludedInSalutation(true);

        $this->frontendUser->setFirstName("Max");
        $this->frontendUser->setLastName("Mustermann");
        $this->frontendUser->setTxRkwregistrationGender(0);
        $this->frontendUser->setTxRkwregistrationTitle($this->title);
        $this->frontendUser->setTxRkwregistrationLanguageKey('de');

        static::assertEquals($fixture, $this->frontendUser->getCompleteSalutationText());

    }

    /**
     * @test
     */
    public function aTitleWithIsAfterEqualsFalseIsRenderedBeforeFullName()
    {
        $fixture = "Herr Dr. med. Max Mustermann";

        $this->title->setName("Dr. med.");
        $this->title->setIsTitleAfter(false);

        $this->frontendUser->setFirstName("Max");
        $this->frontendUser->setLastName("Mustermann");
        $this->frontendUser->setTxRkwregistrationGender(0);
        $this->frontendUser->setTxRkwregistrationTitle($this->title);
        $this->frontendUser->setTxRkwregistrationLanguageKey('de');

        static::assertEquals($fixture, $this->frontendUser->getCompleteSalutationText());

    }

    /**
     * @test
     */
    public function aNotSetTitleRendersOnlyFullName()
    {
        $fixture = "Herr Max Mustermann";

        $this->frontendUser->setFirstName("Max");
        $this->frontendUser->setLastName("Mustermann");
        $this->frontendUser->setTxRkwregistrationGender(0);
        $this->frontendUser->setTxRkwregistrationLanguageKey('de');

        static::assertEquals($fixture, $this->frontendUser->getCompleteSalutationText());

    }

    /**
     * @test
     */
    public function aTitleMustHaveAnAttributeIsIncludedInSalutation()
    {
        $this->title->setName("Dr. med.");
        $this->title->setIsIncludedInSalutation(true);

        static::assertTrue($this->title->getIsIncludedInSalutation());
    }

    /**
     * @test
     */
    public function aSalutationIsRenderedWithoutTitleIfOptionCheckIncludedInSalutationIsTrueAndTitleIsIncludedInSalutationIsFalse()
    {
        $fixture = "Herr Max Mustermann";

        $this->title->setName("Dr. med.");
        $this->title->setIsTitleAfter(false);
        $this->title->setIsIncludedInSalutation(false);

        $this->frontendUser->setFirstName("Max");
        $this->frontendUser->setLastName("Mustermann");
        $this->frontendUser->setTxRkwregistrationTitle($this->title);
        $this->frontendUser->setTxRkwregistrationGender(0);
        $this->frontendUser->setTxRkwregistrationLanguageKey('de');

        static::assertEquals($fixture, $this->frontendUser->getCompleteSalutationText($checkIncludedInSalutation = true));

    }

    /**
     * @test
     */
    public function aSalutationIsRenderedWithTitleIfOptionCheckIncludedInSalutationIsTrueAndTitleIsIncludedInSalutationIsTrue()
    {
        $fixture = "Herr Dr. med. Max Mustermann";

        $this->title->setName("Dr. med.");
        $this->title->setIsTitleAfter(false);
        $this->title->setIsIncludedInSalutation(true);

        $this->frontendUser->setFirstName("Max");
        $this->frontendUser->setLastName("Mustermann");
        $this->frontendUser->setTxRkwregistrationTitle($this->title);
        $this->frontendUser->setTxRkwregistrationGender(0);
        $this->frontendUser->setTxRkwregistrationLanguageKey('de');

        static::assertEquals($fixture, $this->frontendUser->getCompleteSalutationText($checkIncludedInSalutation = true));

    }

    /**
     * @test
     */
    public function aSalutationIsRenderedWithFemaleVariantOfTitleIfGenderIsWomanGivenAFemaleVariantIsSet()
    {
        $fixture = "Frau Dipl.-Kauffrau Erika Musterfrau";

        $this->title->setName("Dipl.-Kaufmann");
        $this->title->setNameFemale("Dipl.-Kauffrau");
        $this->title->setIsTitleAfter(false);
        $this->title->setIsIncludedInSalutation(true);

        $this->frontendUser->setFirstName("Erika");
        $this->frontendUser->setLastName("Musterfrau");
        $this->frontendUser->setTxRkwregistrationTitle($this->title);
        $this->frontendUser->setTxRkwregistrationGender(1);
        $this->frontendUser->setTxRkwregistrationLanguageKey('de');

        static::assertEquals($fixture, $this->frontendUser->getCompleteSalutationText($checkIncludedInSalutation = true));

    }

    /**
     * @test
     */
    public function aSalutationIsRenderedWithDefaultVariantOfTitleIfGenderIsWomanGivenNoFemaleVariantIsSet()
    {
        $fixture = "Frau Dipl.-Kaufmann Erika Musterfrau";

        $this->title->setName("Dipl.-Kaufmann");
        $this->title->setIsTitleAfter(false);
        $this->title->setIsIncludedInSalutation(true);

        $this->frontendUser->setFirstName("Erika");
        $this->frontendUser->setLastName("Musterfrau");
        $this->frontendUser->setTxRkwregistrationTitle($this->title);
        $this->frontendUser->setTxRkwregistrationGender(1);
        $this->frontendUser->setTxRkwregistrationLanguageKey('de');

        static::assertEquals($fixture, $this->frontendUser->getCompleteSalutationText($checkIncludedInSalutation = true));

    }

    /**
     * TearDown
     */
    protected function tearDown()
    {
        parent::tearDown();
    }

}