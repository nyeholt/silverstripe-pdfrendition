<?php
/* 
 *  @license http://silverstripe.org/bsd-license/
 */

/**
 * Action for converting a page to a pdf
 *
 * @author marcus@silverstripe.com.au
 */
class PdfControllerExtension extends Extension {
    static $allowed_actions = array(
		'topdf',
	);


	/**
	 * Generates a PDF file for the current page
	 */
	public function topdf() {
		singleton('PDFRenditionService')->renderPage($this->owner->data(), '', 'browser');
		return;
	}
}