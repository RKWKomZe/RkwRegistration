# Usage in your own extension
## Opt-In
### 1. Generate Opt-In in your controller
For a registration with opt-in simple use the example-code below in your controller.
Please ensure to always load FrontendUserRegistration via ObjectManager
```
/** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
$frontendUser = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(FrontendUser::class);
$frontendUser->setEmail($email);

/** @var \RKW\RkwRegistration\Registration\FrontendUserRegistration $registration */
$objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ObjectManager::class);
$registration = $objectManager->get(FrontendUserRegistration::class);
$registration->setFrontendUser($frontendUser)
    ->setData($alert)
    ->setCategory('rkwAlerts')
    ->setRequest($request)
    ->startRegistration();
```

If you want to be able to update the data of the frontendUser after the successful opt-in
you can use the method **setFrontendUserUpdate**. This will update the frontendUser-object as soon as the user
accepts the opt-in. This way you can be sure that changes to the frontendUser-object only
happen if authorized.

### 2. Define MailService for Opt-In-Mail
No you need a MailService class with a defined action for Opt-Ins
```
/**
* Handles opt-in event
*
* @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
* @param \RKW\RkwRegistration\Domain\Model\OptIn $optIn
* @return void
* @throws \RKW\RkwMailer\Exception
* @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
* @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
* @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
* @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
*/
public function optIn (
    \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser,
    \RKW\RkwRegistration\Domain\Model\OptIn $optIn
): void  {

    // get settings
    $settings = $this->getSettings(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
    $settingsDefault = $this->getSettings();

    if ($settings['view']['templateRootPaths']) {

        /** @var \RKW\RkwMailer\Service\MailService $mailService */
        $mailService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(MailService::class);

        // send new user an email with token
        $mailService->setTo($frontendUser, array(
            'marker' => array(
                'frontendUser' => $frontendUser,
                'optIn'        => $optIn,
                'pageUid'      => intval($GLOBALS['TSFE']->id),
                'loginPid'     => intval($settingsDefault['loginPid']),
            ),
        ));

        $mailService->getQueueMail()->setSubject(
            FrontendLocalizationUtility::translate(
                'rkwMailService.optInAlertUser.subject',
                'rkw_alerts',
                null,
                $frontendUser->getTxRkwregistrationLanguageKey()
            )
        );

        $mailService->getQueueMail()->addTemplatePaths($settings['view']['templateRootPaths']);
        $mailService->getQueueMail()->addPartialPaths($settings['view']['partialRootPaths']);

        $mailService->getQueueMail()->setPlaintextTemplate('Email/OptInAlertUser');
        $mailService->getQueueMail()->setHtmlTemplate('Email/OptInAlertUser');
        $mailService->send();
    }
}
```
### 3. Set Signal-Slot
Now we need a signal-slot that refers to the defined method for sending mails (ext_localconf.php)
```
/**
 * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher
 */
$signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class);
$signalSlotDispatcher->connect(
    RKW\RkwRegistration\Registration\AbstractRegistration::class,
    RKW\RkwRegistration\Registration\AbstractRegistration::SIGNAL_AFTER_CREATING_OPTIN  . 'RkwAlerts',
    RKW\RkwAlerts\Service\RkwMailService::class,
    'optInAlertUser'
);
```

### 4. Set Template for Opt-In-Mail
The opt-in-email may look like this:
```
<html xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers"
	xmlns:rkwMailer="http://typo3.org/ns/RKW/RkwMailer/ViewHelpers"
	data-namespace-typo3-fluid="true">

	<f:layout name="Email/{mailType}" />

	<!-- PLAINTEXT -->
	<f:section name="Plaintext"><rkwMailer:email.plaintextLineBreaks>
	    <rkwMailer:email.translate key="templates_email_optInAlertUser.textOptInLinkLabel" languageKey="{frontendUser.txRkwregistrationLanguageKey}" extensionName="rkwAlerts"/>:\n
	    <rkwMailer:email.uri.action action="optIn" controller="Alert" extensionName="rkwAlerts" pluginName="rkwAlerts" absolute="true" pageUid="{pageUid}" additionalParams="{tx_rkwalerts_rkwalerts: {token: optIn.tokenYes, tokenUser: optIn.tokenUser}}" section="rkw-alerts" />\n\n

        <rkwMailer:email.translate key="templates_email_optInAlertUser.textOptOutLinkLabel" languageKey="{frontendUser.txRkwregistrationLanguageKey}" extensionName="rkwAlerts"/>:\n
        <rkwMailer:email.uri.action action="optIn" controller="Alert" extensionName="rkwAlerts" pluginName="rkwAlerts" absolute="true" pageUid="{pageUid}" additionalParams="{tx_rkwalerts_rkwalerts: {token: optIn.tokenNo, tokenUser: optIn.tokenUser}}" section="rkw-alerts" />
    </rkwMailer:email.plaintextLineBreaks></f:section>

	<!-- HTML -->
	<f:section name="Html">
		<a href="<rkwMailer:email.uri.action action='optIn' controller='Alert' extensionName='rkwAlerts' pluginName='rkwAlerts' absolute='true' pageUid='{pageUid}' additionalParams='{tx_rkwalerts_rkwalerts: {token: optIn.tokenYes, tokenUser: optIn.tokenUser}}' section='rkw-alerts' />"><rkwMailer:email.translate key="templates_email_optInAlertUser.textOptInLinkLabel" languageKey="{frontendUser.txRkwregistrationLanguageKey}" extensionName="rkwAlerts"/></a>
		<a href="<rkwMailer:email.uri.action action='optIn' controller='Alert' extensionName='rkwAlerts' pluginName='rkwAlerts' absolute='true' pageUid='{pageUid}' additionalParams='{tx_rkwalerts_rkwalerts: {token: optIn.tokenNo, tokenUser: optIn.tokenUser}}' section='rkw-alerts' />"><rkwMailer:email.translate key="templates_email_optInAlertUser.textOptOutLinkLabel" languageKey="{frontendUser.txRkwregistrationLanguageKey}" extensionName="rkwAlerts"/></a>
	</f:section>

</html>
```
### 5. Check Opt-In
To check the opt-in you can use the following example-code in your contoller:
```
public function optInAction(string $tokenUser, string $token): void
{
    /** @var \RKW\RkwRegistration\Registration\FrontendUserRegistration $registration */
    $registration = $this->objectManager->get(FrontendUserRegistration::class);
    $result = $registration->setFrontendUserToken($tokenUser)
        ->setCategory('rkwAlerts')
        ->setRequest($this->request)
        ->validateOptIn($token);

    if ($result >= 200 && $result < 300) {

        // sucessfull

    } elseif ($result >= 300 && $result < 400) {

        // canceled

    } else {
        // error / not found
    }
}
```

### 6. Signal-Slot for extension specific action after opt-in
We need a second signal-slot in order to do whatever we need to do after the opt-in
```
    $signalSlotDispatcher->connect(
        RKW\RkwRegistration\Registration\AbstractRegistration::class,
        \RKW\RkwRegistration\Registration\AbstractRegistration::SIGNAL_AFTER_REGISTRATION_COMPLETED . 'RkwAlerts',
        'RKW\\RkwAlerts\\Alerts\\AlertManager',
        'saveAlertByRegistration'
    );
```

### 7. Method for for the specific action
Then we need to define the corresponding method:
```
    /**
     * Save alert by registration
     * Used by SignalSlot
     *
     * @param \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser
     * @param \RKW\RkwRegistration\Domain\Model\OptIn $optIn
     * @return void
     * @api Used by SignalSlot
     */
    public function saveAlertByRegistration(
        \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser,
        \RKW\RkwRegistration\Domain\Model\OptIn $optIn
    ) {

        if (
            ($alert = $optIn->getData())
            && ($alert instanceof \RKW\RkwAlerts\Domain\Model\Alert)
        ) {

            try {
                // create alert here
            } catch (\RKW\RkwAlerts\Exception $exception) {
                // do nothing here
            }
        }
    }
```
## Another use-cases
* You can also:
** Send a confirmation-email after the opt-in was successful (Using SIGNAL_AFTER_ALERT_CREATED-Signal-Slot)
** Delete all extension-specific data if the frontendUser is deleted (Using SIGNAL_AFTER_REGISTRATION_ENDED-Signal-Slot)
** ... do many other fancy stuff ;-)

# Consent (Privacy, Terms, Marketing)
The extension has a ViewHelper and validators to obtain consent to privacy, terms of use and advanced marketing.
In order to obtain the consents, only the corresponding ViewHelper must be used in the own extension. As soon as an opt-in is carried out, the corresponding consents are automatically documented and stored in the database. The consents granted are recorded accordingly with the associated data (IP address, browser, etc.). In addition, the consent to the terms of use and to marketing is stored in the FrontendUser, as these consents are usually page-wide and independent of the respective context of the consent.
## 1. In Fluid
The following code can be used to obtain the appropriate consent. It is important that the ViewHelper is used within a form and that the FormErrors are also returned via Fluid.
```
<html xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers"
	xmlns:rkwRegistration="http://typo3.org/ns/RKW/RkwRegistration/ViewHelpers"
	xmlns:ajaxApi="http://typo3.org/ns/Madj2k/AjaxApi/ViewHelpers"
	data-namespace-typo3-fluid="true">

	<f:form action="create" name="alert" object="{alert}">

        [...]

        <rkwRegistration:consent type="terms" />
        <rkwRegistration:consent type="privacy" key="default" />
        <rkwRegistration:consent type="marketing" />

        [...]

	</f:form>
</html>
```
## 2. In the controller
Only the corresponding validators are included here. They always refer to the form object.
```
    /**
     * action create
     *
     * @param \RKW\RkwAlerts\Domain\Model\Alert $alert
     * @param string $email
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @TYPO3\CMS\Extbase\Annotation\Validate("\RKW\RkwRegistration\Validation\Consent\TermsValidator", param="alert")
     * @TYPO3\CMS\Extbase\Annotation\Validate("\RKW\RkwRegistration\Validation\Consent\PrivacyValidator", param="alert")
     * @TYPO3\CMS\Extbase\Annotation\Validate("\RKW\RkwRegistration\Validation\Consent\MarketingValidator", param="alert")
     */
    public function createAction(
        \RKW\RkwAlerts\Domain\Model\Alert $alert,
        string $email = ''
    ): void {

        [...]
```
An opt-in procedure is usually not carried out for logged-in frontend users. If you still want to record the time of consent for a registration, you can achieve this with the following code:
```
    \RKW\RkwRegistration\DataProtection\ConsentHandler::add(
        $request,
        $frontendUser,
        $alert,
        'new alert'
    );
```

# Upgrade to v9.5

## Update Database
```
RENAME TABLE `tx_rkwregistration_domain_model_privacy` TO `tx_rkwregistration_domain_model_consent`;
ALTER TABLE `tx_rkwregistration_domain_model_consent` CHANGE `registration_user_sha1` `frontend_user_token` VARCHAR(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '';
ALTER TABLE `tx_rkwregistration_domain_model_consent` ADD `consent_privacy` INT DEFAULT 0 NOT NULL;
ALTER TABLE `tx_rkwregistration_domain_model_consent` ADD `consent_terms` INT DEFAULT 0 NOT NULL;
ALTER TABLE `tx_rkwregistration_domain_model_consent` ADD `consent_marketing` INT DEFAULT 0 NOT NULL;
UPDATE `tx_rkwregistration_domain_model_consent` SET `consent_privacy` = 1;
```
