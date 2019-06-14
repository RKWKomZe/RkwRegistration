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
     * @param $formula
     * @param $berater_honorarsatz
     * @param $rkw_honorarsatz
     * @param $tagwerke
     */
    public function calculate($formula, $berater_honorarsatz, $rkw_honorarsatz, $tagwerke)
    {

        //  feststehende Parameter (via Backend einzugeben
        $foerderprogramme = [
            'beratung' => [
                'name' => 'Beratungsrichtlinie',
                'subvention' => [
                    'berater' => 'WENN($tagwerke * $berater_honorarsatz > 2550;2550;$tagwerke * $berater_honorarsatz)',
                    'rkw' => '$tagwerke * $rkw_honorarsatz'
                ],
                'foerdermittel' => '$subvention["gesamt"] * 0.5',
                'eigenmittel' => [
                    'netto' => '$honorar["gesamt"] - $foerdermittel',
                    'brutto' => '$eigenmittel["netto"] + $honorar["gesamt"]["tax"]',
                ],
                'meta' => [
                    'name' => 'Thüringer Beratungsrichtlinie',
                    'zustaendig' => '',
                    'unternehmensalter' => 0,
                    'moeglicheTagwerke' => '',
                    'zuschuss' => '',
                    'beratungsinhalte' => ''

                ]
            ],
            'gruender' => [
                'name' => 'Gründerrichtlinie',
                'formula' => 'WENN($tagwerke * $berater_honorarsatz > 3550;3550;$tagwerke * $berater_honorarsatz)',
                'foerdermittel' => '$subvention["gesamt"] * 0.7',
            ],
            'bafa_jung' => [
                'name' => 'BAFA_junge_Unternehmen',
                'formula' => 'WENN($berater_honorarsatz > 800;$tagwerke * 800;$tagwerke * $berater_honorarsatz)',
                'foerdermittel' => '$subvention["gesamt"] * 0.8',
            ],
            'bafa_bestand' => [
                'name' => 'BAFA_Bestandsunternehmen',
                'formula' => 'WENN($berater_honorarsatz > 714,28;714,2857142857 * $tagwerke;$berater_honorarsatz * $tagwerke)',
                'foerdermittel' => '$subvention["gesamt"] * 0.8',
            ]
        ];

        switch ($formula) {
            case 'bafa_bestand':
                $result = [
                    'honorar' => [
                        'berater' => $tagwerke * $berater_honorarsatz,
                        'rkw' => $tagwerke * $rkw_honorarsatz,
                        'netto' => ($tagwerke * $berater_honorarsatz) + ($tagwerke * $rkw_honorarsatz),
                        'tax' => (($tagwerke * $berater_honorarsatz) + ($tagwerke * $rkw_honorarsatz)) * 0.19,
                        'brutto' => (($tagwerke * $berater_honorarsatz) + ($tagwerke * $rkw_honorarsatz)) * 1.19,
                    ],
                    'subvention' => [
                        'berater' => ($tagwerke * $berater_honorarsatz > 2550) ? 2550 : $tagwerke * $berater_honorarsatz,
                        'rkw' => $tagwerke * $rkw_honorarsatz,
                    ]
                ];

                //  @todo: Fördermittel berechnen - bessere Darstellung für die Fixtures suchen!
                break;
            case 'bafa_jung':
                $result = [
                    'honorar' => [
                        'berater' => $tagwerke * $berater_honorarsatz,
                        'rkw' => $tagwerke * $rkw_honorarsatz,
                        'netto' => ($tagwerke * $berater_honorarsatz) + ($tagwerke * $rkw_honorarsatz),
                        'tax' => (($tagwerke * $berater_honorarsatz) + ($tagwerke * $rkw_honorarsatz)) * 0.19,
                        'brutto' => (($tagwerke * $berater_honorarsatz) + ($tagwerke * $rkw_honorarsatz)) * 1.19,
                    ],
                    'subvention' => [
                        'berater' => ($tagwerke * $berater_honorarsatz > 3550) ? 3550 : $tagwerke * $berater_honorarsatz,
                        'rkw' => $tagwerke * $rkw_honorarsatz,
                    ]
                ];
                break;
            case 'beratung':
                $result = [
                    'honorar' => [
                        'berater' => $tagwerke * $berater_honorarsatz,
                        'rkw' => $tagwerke * $rkw_honorarsatz,
                        'netto' => ($tagwerke * $berater_honorarsatz) + ($tagwerke * $rkw_honorarsatz),
                        'tax' => (($tagwerke * $berater_honorarsatz) + ($tagwerke * $rkw_honorarsatz)) * 0.19,
                        'brutto' => (($tagwerke * $berater_honorarsatz) + ($tagwerke * $rkw_honorarsatz)) * 1.19,
                    ],
                    'subvention' => [
                        'berater' => ($berater_honorarsatz > 800) ? $tagwerke * 800 : $tagwerke * $berater_honorarsatz,
                        'rkw' => $tagwerke * $rkw_honorarsatz,
                    ]
                ];
                break;
            case 'gruender':
                $result = [
                    'honorar' => [
                        'berater' => $tagwerke * $berater_honorarsatz,
                        'rkw' => $tagwerke * $rkw_honorarsatz,
                        'netto' => ($tagwerke * $berater_honorarsatz) + ($tagwerke * $rkw_honorarsatz),
                        'tax' => (($tagwerke * $berater_honorarsatz) + ($tagwerke * $rkw_honorarsatz)) * 0.19,
                        'brutto' => (($tagwerke * $berater_honorarsatz) + ($tagwerke * $rkw_honorarsatz)) * 1.19,
                    ],
                    'subvention' => [
                        'berater' => ($berater_honorarsatz > 714.28) ? 714.2857142857 * $tagwerke : $berater_honorarsatz * $tagwerke,
                        'rkw' => $tagwerke * $rkw_honorarsatz,
                    ]
                ];
                break;
        }

        return $result;

    }

    /**
     * @test
     */
    public function calculator()
    {
        //  Programme wählen
        $cellmapping = [
            'b7' => 'tagwerke',
            'b9' => 'berater_honorarsatz',
        ];

        $tagwerke = 10;

        $fixtures = [
            'beratung' => [
                'honorar' => [
                    'berater' => 10000,
                    'berater_satz' => 1000,
                    'rkw' => 1000,
                    'rkw_satz' => 100,
                ],
                'subvention' => [
                    'berater' => 8000,
                    'rkw' => 1000
                ],
            ],
            'gruender' => [
                'honorar' => [
                    'berater' => 10000,
                    'berater_satz' => 1000,
                    'rkw' => 1000,
                    'rkw_satz' => 100,
                ],
                'subvention' => [
                    'berater' => 7142.86,
                    'rkw' => 1000
                ],
            ],
            'bafa_jung' => [
                'honorar' => [
                    'berater' => 10000,
                    'berater_satz' => 1000,
                    'rkw' => 450,
                    'rkw_satz' => 45,
                ],
                'subvention' => [
                    'berater' => 3550,
                    'rkw' => 450
                ],
            ],
            'bafa_bestand' => [
                'honorar' => [
                    'berater' => 10000,
                    'berater_satz' => 1000,
                    'rkw' => 450,
                    'rkw_satz' => 45,
                ],
                'subvention' => [
                    'berater' => 2550,
                    'rkw' => 450
                ],
            ]
        ];

        foreach ($fixtures as $key => $fixture) {

            $calculation = $this->calculate($key, $fixture['honorar']['berater_satz'], $fixture['honorar']['rkw_satz'], $tagwerke);
            $this->assertEquals($fixture['subvention']['berater'], round($calculation['subvention']['berater'], 2));
            $this->assertEquals($fixture['subvention']['rkw'], round($calculation['subvention']['rkw'],2));

        }

    }

    /**
     * @test
     */
    public function taxIsCalculated()
    {
        $calculation = $this->calculate('beratung', 1000, 100, 10);

        $this->assertEquals(11000.00, round($calculation['honorar']['netto'], 2));
        $this->assertEquals(2090.00, round($calculation['honorar']['tax'], 2));
        $this->assertEquals(13090.00, round($calculation['honorar']['brutto'], 2));

    }

    /**
     * @test
     */
    public function titlePropertyIsCheckedIsPersistedToTheDatabase()
    {
        $fixture['isChecked'] = true;

        $this->title->setName("Dr. med.");
        $this->title->setIsChecked($fixture['isChecked']);

        $this->titleRepository->add($this->title);

        $this->persistenceManager->persistAll();

        $databaseResult = $this->getDatabaseConnection()->selectSingleRow('*', 'tx_rkwregistration_domain_model_title','name = "Dr. med."');

        $this->assertFalse(empty($databaseResult));
        $this->assertNotNull($databaseResult['is_checked']);
        $this->assertTrue(isset($databaseResult['is_checked']));
        $this->assertEquals($fixture['isChecked'], (bool) $databaseResult['is_checked']);
    }

    /**
     * @test
     */
    public function onlyTitlesWithPropertyIsCheckedEqualsTrueShouldBePassedToView()
    {

        $titleDoNotPassToView = $this->objectManager->get(Title::class);
        $titleDoNotPassToView->setName("Do not pass to view");
        $titleDoNotPassToView->setIsChecked(false);

        $titleDoPassToView = $this->objectManager->get(Title::class);
        $titleDoPassToView->setName("Do pass to view");
        $titleDoPassToView->setIsChecked(true);

        $this->titleRepository->add($titleDoNotPassToView);
        $this->titleRepository->add($titleDoPassToView);

        $this->persistenceManager->persistAll();

        $titles = $this->titleRepository->findAllOfType(true, false, false);

        $this->assertCount(1, $titles);
        $this->assertEquals($titleDoPassToView, $titles->getFirst());

    }

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
        $this->assertNotNull($databaseResult['is_included_in_salutation']);
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