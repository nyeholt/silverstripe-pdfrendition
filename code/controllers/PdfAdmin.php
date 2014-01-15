<?php

/**
 *	Admin controller for creating and managing composed PDFs.
 *
 *	@authors Marcus Nyeholt <marcus@silverstripe.com.au> and Nathan Glasl <nathan@silverstripe.com.au>
 *	@license BSD http://silverstripe.org/BSD-license
 */

class PdfAdmin extends ModelAdmin {
	public static $url_segment = 'pdfs';
	public static $menu_title = 'PDFs';
	public static $managed_models = array(
		'ComposedPdf',
	);
	
	public function init() {
		parent::init();
		Requirements::javascript('pdfrendition/javascript/pdfrendition.js');
	}
	
	/**
	 *	Preview the pdf file.
	 *
	 *	@return String
	 */

	public function previewpdf() {
		$id = $this->request->getVar('ID');
		if ($id) {
			$pdf = ComposedPdf::get()->byID($id);
			if ($pdf->canView()) {
				return $pdf->renderPdf();
			}
			else {
				throw new Exception("You don't have permission to do this.");
			}
		}
	}

	/**
	 *	Compose the pdf file.
	 *
	 *	@return String
	 */

	public function compose() {
		$id = $this->request->getVar('ID');
		if ($id) {
			$pdf = ComposedPdf::get()->byID($id);
			if ($pdf->canView()) {
				$pdf->createPdf();
				Session::set('PdfComposed', true);
				$this->redirectBack();
			}
			else {
				throw new Exception("You don't have permission to do this.");
			}
		}
	}
}
