<?php

namespace RKW\RkwRegistration\Validation\Consent;
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

use RKW\RkwRegistration\ViewHelpers\ConsentViewHelper;

/**
 * Class MarketingValidator
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @todo write a fucking test
 */
class MarketingValidator extends AbstractValidator
{

    /**
     * Validation of marketing checkbox
     *
     * - The given entity is not important / not used
     * - The function "forProperty" will not work proper
     * - This validator will only return the message (no field highlighting)
     *
     * @param array $value
     * @return bool
     */
    public function isValid($value): bool
    {
        $isValid = true;
        $formData = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP(ConsentViewHelper::NAMESPACE);
        if (isset($formData['marketing'])
            && (!$formData['marketing'])
        ) {
            // just a placeholder without real validation
        }

        return $isValid;
    }
}
