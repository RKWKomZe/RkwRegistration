<?php

namespace RKW\RkwRegistration\ViewHelpers\Widget;

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
 * Class TitleAutoCompleteViewHelper
 *
 * @author Christian Dilger <c.dilger@addorange.de>
 * @copyright Rkw Kompetenzzentrum
 * @package RKW_RkwRegistration
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */

class TitleAutoCompleteViewHelper extends \TYPO3\CMS\Fluid\Core\Widget\AbstractWidgetViewHelper
{

    /**
     * @param string $options
     * @param string $container
     * @param string $list
     * @return string
     */
    public function render($options = '', $container = '', $list = '') {

        return '
        <div class="jcf-select-drop" id="' . $container . '" style="display: none;">
            <div class="jcf-select-drop-content">
                <span class="jcf-list">
                    <span class="jcf-list-content">
                        <ul id="' . $list . '" class="links-list">
                        </ul>
                    </span>
                </span>
            </div>
        </div>
        <style>
            #autocomplete-results .jcf-option:hover {
                background: #e6e6e6;
            }
        </style>
        <script type="text/javascript">
    
            var Autocomplete = {
                settings: {
                    input: document.getElementById(\'title\'),
                    options: JSON.parse(\''. $options . '\'),
                    autocomplete_container: document.getElementById("' . $container . '"),
                    autocomplete_results: document.getElementById("' . $list . '"),
                    currentFocus: -1
                },
                init: function() {
                    this.bindEvents();
                },
                bindEvents: function() {
                    var self = this;
    
                    this.settings.input.onkeyup = function(e) {
                        if (e.keyCode == 40) {
                            self.settings.currentFocus++;
                            self.setFocus();
                        } else if (e.keyCode == 38) {
                            self.settings.currentFocus--;
                            self.setFocus();
                        } else if (e.keyCode == 13) {
                            e.preventDefault();
                            if (self.settings.currentFocus > -1) {
                                //  simulate the click
                                //  if (x) x[currentFocus].click();
                            }
                        } else {
                            self.findResults();
                        }
                    };
    
                    this.settings.autocomplete_results.addEventListener(\'click\', function(e) {
                        self.settings.input.value = self.settings.options[e.target.dataset.autocompleteIndex];
                        self.findResults();
                    });
                },
                setFocus: function () {
                    //  set the current focus on corresponding list item
                },
                buildResults: function(val) {
                    var resultSet = [],
                        pattern = new RegExp(val, \'i\');
    
                    for (i = 0; i < this.settings.options.length; i++) {
                        if (pattern.test(this.settings.options[i]) && this.settings.options[i] !== val) {
                            resultSet.push(i);
                        }
                    }
    
                    return resultSet;
                },
                findResults: function() {
                    var input_val = this.settings.input.value,
                        resultSet = [];
    
                    if (input_val.length > 0) {
                        this.settings.autocomplete_results.innerHTML = \'\';
                        resultSet = this.buildResults(input_val);
                        for (i = 0; i < resultSet.length; i++) {
                            this.settings.autocomplete_results.innerHTML += \'<li><span class="jcf-option" data-autocomplete-index="\' + resultSet[i] + \'">\' + this.settings.options[resultSet[i]] + \'</span></li>\';
                        }
                        this.settings.autocomplete_container.style.display = (resultSet.length > 0) ? \'block\' : \'none\';
                    } else {
                        this.settings.autocomplete_results.innerHTML = \'\';
                        this.settings.autocomplete_container.style.display = \'none\';
                    }
                }
            };
    
            Autocomplete.init();
    
        </script>
        ';
    }
}