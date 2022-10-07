# Usage in your own extension

## 1. Generate Opt-In
For a registration with opt-in simple use the example-code below in your controller.
Please ensure to always load FrontendUserRegistration via ObjectManager
```
/** @var \RKW\RkwRegistration\Domain\Model\FrontendUser $frontendUser */
$frontendUser = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(FrontendUser::class);
$frontendUser->setEmail($email);

/** @var \RKW\RkwRegistration\Registration\FrontendUser\FrontendUserRegistration $registration */
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

## 2. Define MailService for Opt-In-Mail
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
## 3. Set Signal-Slot
Now we need a signal-slot that refers to the defined method for sending mails.


## 4. Set Template for Opt-In-Mail
The opt-in-email may look like this:
```
<html xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers"
	xmlns:rkwMailer="http://typo3.org/ns/RKW/RkwMailer/ViewHelpers"
	data-namespace-typo3-fluid="true">

	<f:layout name="Email/{mailType}" />

	<!-- PLAINTEXT -->
	<f:section name="Plaintext"><rkwMailer:plaintextLineBreaks>
	    <rkwMailer:frontend.translate key="templates_email_optInAlertUser.textOptInLinkLabel" languageKey="{frontendUser.txRkwregistrationLanguageKey}" extensionName="rkwAlerts"/>:\n
	    <rkwMailer:frontend.link action="optIn" controller="Alert" extensionName="rkwAlerts" pluginName="rkwAlerts" absolute="true" pageUid="{pageUid}" additionalParams="{tx_rkwalerts_rkwalerts: {token: optIn.tokenYes, tokenUser: optIn.tokenUser}}" section="rkw-alerts" />\n\n

        <rkwMailer:frontend.translate key="templates_email_optInAlertUser.textOptOutLinkLabel" languageKey="{frontendUser.txRkwregistrationLanguageKey}" extensionName="rkwAlerts"/>:\n
        <rkwMailer:frontend.link action="optIn" controller="Alert" extensionName="rkwAlerts" pluginName="rkwAlerts" absolute="true" pageUid="{pageUid}" additionalParams="{tx_rkwalerts_rkwalerts: {token: optIn.tokenNo, tokenUser: optIn.tokenUser}}" section="rkw-alerts" />
    </rkwMailer:plaintextLineBreaks></f:section>

	<!-- HTML -->
	<f:section name="Html">
		<a href="<rkwMailer:frontend.link action='optIn' controller='Alert' extensionName='rkwAlerts' pluginName='rkwAlerts' absolute='true' pageUid='{pageUid}' additionalParams='{tx_rkwalerts_rkwalerts: {token: optIn.tokenYes, tokenUser: optIn.tokenUser}}' section='rkw-alerts' />"><rkwMailer:frontend.translate key="templates_email_optInAlertUser.textOptInLinkLabel" languageKey="{frontendUser.txRkwregistrationLanguageKey}" extensionName="rkwAlerts"/></a>
		<a href="<rkwMailer:frontend.link action='optIn' controller='Alert' extensionName='rkwAlerts' pluginName='rkwAlerts' absolute='true' pageUid='{pageUid}' additionalParams='{tx_rkwalerts_rkwalerts: {token: optIn.tokenNo, tokenUser: optIn.tokenUser}}' section='rkw-alerts' />"><rkwMailer:frontend.translate key="templates_email_optInAlertUser.textOptOutLinkLabel" languageKey="{frontendUser.txRkwregistrationLanguageKey}" extensionName="rkwAlerts"/></a>
	</f:section>

</html>
```
## 5.

## 5. Check Opt-In
To check the opt-in you can use the following example-code in your contoller:
```
public function optInAction(string $tokenUser, string $token): void
{
    /** @var \RKW\RkwRegistration\Registration\FrontendUser\FrontendUserRegistration $registration */
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

# Refactoring changes of RkwRegistration 2021

## update script
Execute the update script in InstallTool to change automatically plugin names in database

## beware of old templates
Some links seem not to be correct? The dynamic base uri does not work? You don't see expected flash messages?
Then you should check, if an old fluid template is responsible for this behavior and compare it's content to the rewritten templates!

## change existing signal slots in third party extensions. Example below
**from**
```
$signalSlotDispatcher->connect(
    \RKW\RkwRegistration\Registration\FrontendUser\AbstractRegistration::class,
    \RKW\RkwRegistration\Register\OptInRegister::SIGNAL_AFTER_CREATING_OPTIN_EXISTING_USER . 'RkwEvents',
    'RKW\\RkwEvents\\Service\\RkwMailService',
    'optInRequest'
);
```
**to**
```
$signalSlotDispatcher->connect(
    'RKW\\RkwRegistration\\Service\\OptInService',
    \RKW\RkwRegistration\Service\OptInService::SIGNAL_AFTER_CREATING_OPTIN_EXISTING_USER . 'RkwEvents',
    'RKW\\RkwEvents\\Service\\RkwMailService',
    'optInRequest'
);
```

## using of dynamic "My RKW" domains
### create in backend new domain entries of the "Mein RKW" page
note the new fields in tab "Mein RKW" to set the related main page URI and forwarding options on login and logout
### adjust the RealUrl configuration. Just copy the standard domain settings to it's dynamic sibling. Local example:
```
$TYPO3_CONF_VARS['EXTCONF']['realurl']['mein-rkw-kompetenzzentrum.rkw.local'] = $TYPO3_CONF_VARS['EXTCONF']['realurl']['mein.rkw.local'];
```
### set cookie domain in install tool. Local example:
```
'cookieDomain' => '.rkw.local',
'cookieSameSite' => 'lax',
```
#### for more complex using take a look to the description of [SYS][cookieDomain]:
The result of the match is used as the domain for the cookie. eg.
```
/\.(example1|example2)\.com$/
```
or
```
/\.(example1\.com)|(example2\.net)$/
```
