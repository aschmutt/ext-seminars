<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005 Oliver Klee (typo3-coding@oliverklee.de)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Class 'tx_seminars_templatehelper' for the 'seminars' extension.
 * 
 * This utitity class provides some commonly-used functions for handling templates
 * (in addition to all functionality provided by the base classes).
 * 
 * This is an abstract class; don't instantiate it.
 *
 * @author	Oliver Klee <typo-coding@oliverklee.de>
 */

require_once(t3lib_extMgm::extPath('seminars').'class.tx_seminars_dbplugin.php');

class tx_seminars_templatehelper extends tx_seminars_dbplugin {
	/** the HTML template subparts */
	var $templateCache = array();

	/**
	 * Dummy constructor: Does nothing.
	 * 
	 * Call $this->init() instead.
	 */
	function tx_seminars_templatehelper() {
	}

	/**
	 * Retrieve the subparts from the plugin template and write them to $this->templateCache.
	 * 
	 * @param	array		array with strings for the subpart markers to retrieve,
	 * 						e.g. 'SIGN_IN_VIEW'
	 * 
	 * @access private
	 */
	function getTemplateCode($subpartNames) {
		/** the whole template file as a string */
		$templateRawCode = $this->cObj->fileResource($this->conf['templateFile']);

		foreach ($subpartNames as $currentKey) {
			$this->templateCache[$currentKey] = $this->cObj->getSubpart($templateRawCode, '###'.$currentKey.'###');
		}
	} 

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_templatehelper.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_templatehelper.php']);
}
