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
	 * Return a link to generate the current content item as a PDF
	 * 
	 * @return string
	 */
	public function PdfLink() {
		return $this->owner->Link('topdf');
	}

	/**
	 * Generates a PDF file for the current page
	 */
	public function topdf() {
		Requirements::themedCSS('pdfrendition');
		singleton('PDFRenditionService')->renderPage($this->owner->data(), '', 'browser');
		return;
	}
}