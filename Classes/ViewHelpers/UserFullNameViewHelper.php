<?php
namespace RKW\RkwRegistration\ViewHelpers;

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

use RKW\RkwRegistration\Domain\Model\FrontendUser;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;


/**
 * Class UserFullNameViewHelper
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright RKW Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class UserFullNameViewHelper extends AbstractViewHelper
{

    /**
     * Return the full name of the user
     *
     * @param FrontendUser $frontendUser
     * @param bool $includeFirstName
     * @param bool $includeGender
     * @return string $string
     */
    public function render(FrontendUser $frontendUser, $includeFirstName = false, $includeGender = true)
    {

        return static::renderStatic(
            [
                'frontendUser'     => $frontendUser,
                'includeFirstName' => $includeFirstName,
                'includeGender'    => $includeGender,
            ],
            $this->buildRenderChildrenClosure(),
            $this->renderingContext
        );
    }


    /**
     * Static rendering
     *
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return string
     */
    static public function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext)
    {

        /** @var FrontendUser $frontendUser */
        $frontendUser = $arguments['frontendUser'];
        $includeFirstName = $arguments['includeFirstName'];
        $includeGender = $arguments['includeGender'];

        $fullName = [];
        if ($frontendUser->getLastName()) {

            if (
                ($includeGender == true)
                && ($frontendUser->getGenderText())
            ) {
                $fullName[] = $frontendUser->getGenderText();
            }

            if ($frontendUser->getTitleText()) {
                $fullName[] = $frontendUser->getTitleText();
            }

            if (
                ($includeFirstName == true)
                && ($frontendUser->getFirstName())
            ) {
                $fullName[] = ucFirst($frontendUser->getFirstName());
            }

            $fullName[] = ucFirst($frontendUser->getLastName());
        }

        return trim(implode(' ', $fullName));
    }
}



