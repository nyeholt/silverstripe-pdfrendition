<?php

/**
 * Admin controller for creating and managing composed PDFs
 *
 * @author Marcus Nyeholt <marcus@silverstripe.com.au>
 * @license BSD http://silverstripe.org/BSD-license
 */
class PdfAdmin extends ModelAdmin {
	public static $url_rule = '/$Action';
	public static $url_segment = 'pdfs';
	public static $menu_title = 'PDFs';
	public static $managed_models = array(
		'ComposedPdf',
	);
	
	public function init() {
		parent::init();
		Requirements::javascript('pdfrendition/javascript/pdfrendition.js');
	}

	public function compose($data, Form $form, $request) {
		$record = $this->getCurrentRecord();
		if ($record) {
			// lets generate the pdf with the give template
			$record->createPdf();
		}

		// Behaviour switched on ajax.
		if(Director::is_ajax()) {
			return $this->edit($request);
		} else {
			Director::redirectBack();
		}
	}

	public function preview() {
		$record = $this->getCurrentRecord();
		if ($record && $record->Template) {
			// lets generate the pdf with the give template
			echo $record->renderWith($record->Template);
		} else {
			echo "No template found";
		}
	}
	
	/**
	 * Preview the pdf file
	 *
	 * @return String 
	 */
	public function previewpdf() {
		$id = $this->request->getVar('ID');
		if ($id) {
			$pdf = DataObject::get_by_id('ComposedPdf', $id);
			if ($pdf->canView()) {
				return $pdf->renderPdf();
			}
		}
	}
}