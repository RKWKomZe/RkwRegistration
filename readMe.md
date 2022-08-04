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
    'RKW\\RkwRegistration\\Tools\\Registration',
    \RKW\RkwRegistration\Tools\Registration::SIGNAL_AFTER_CREATING_OPTIN_EXISTING_USER . 'RkwEvents',
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