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

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * InstanceOfViewHelper
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class InstanceOfViewHelper extends AbstractViewHelper
{

    /**
     * Initialize arguments.
     *
     * @throws \TYPO3Fluid\Fluid\Core\ViewHelper\Exception
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('entity', AbstractEntity::class, 'The entity to check.', true);
        $this->registerArgument('type', 'string', 'The type to check against.', true);
    }

    /**
     * @param \TYPO3\CMS\Extbase\DomainObject\AbstractEntity $entity
     * @return boolean
     */
    public function render(): bool
    {
        /** @var TYPO3\CMS\Extbase\DomainObject\AbstractEntity $entity */
        $textVersion = $this->arguments['entity'];

        /** @var string $type */
        $type = $this->arguments['type'];

        if ($entity instanceof $type) {
            return true;
        }

        return false;
    }

}
