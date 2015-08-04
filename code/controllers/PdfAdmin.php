<?php

/**
 * 	Admin controller for creating and managing composed PDFs.
 *
 * 	@authors Marcus Nyeholt <marcus@silverstripe.com.au> and Nathan Glasl <nathan@silverstripe.com.au>
 * 	@license BSD http://silverstripe.org/BSD-license
 */
class PdfAdmin extends ModelAdmin {

	private static $allowed_actions = array('previewpdf');
	private static $url_segment = 'pdfs';
	private static $menu_title = 'PDFs';
	private static $managed_models = array(
		'ComposedPdf',
	);

	public function init() {
		parent::init();
		Requirements::javascript('pdfrendition/javascript/pdfrendition.js');
	}

	public function getEditForm($id = null, $fields = null) {
		$form = parent::getEditForm($id, $fields);

		$gridClass = $this->modelClass . 'GridFieldDetailForm_ItemRequest';

		if (class_exists($gridClass)) {
			$grid = $form->Fields()->dataFieldByName($this->modelClass);
			$editForm = $grid->getConfig()->getComponentByType('GridFieldDetailForm');
			if ($editForm) {
				$editForm->setItemRequestClass($gridClass);
			}
		}

		return $form;
	}

}

class ComposedPdfGridFieldDetailForm_ItemRequest extends GridFieldDetailForm_ItemRequest {

	private static $allowed_actions = array(
		'ItemEditForm',
		'previewpdf',
	);

	function ItemEditForm() {
		$form = parent::ItemEditForm();

		if ($this->record->ID) {
			$form->Actions()->push($action = FormAction::create('previewpdf', 'Preview')->setAttribute('data-link', $this->Link() . '/previewpdf'));
			$form->Actions()->push($action = FormAction::create('compose', 'Compose'));
		}

		return $form;
	}

	/**
	 * 	Preview the pdf file.
	 *
	 * 	@return String
	 */
	public function previewpdf() {
		$id = $this->record->ID;
		if ($id) {
			$pdf = ComposedPdf::get()->byID($id);
			if ($pdf->canView()) {
				return $pdf->renderPdf();
			} else {
				throw new Exception("You don't have permission to do this.");
			}
		}
	}

	/**
	 * 	Compose the pdf file.
	 *
	 * 	@return String
	 */
	public function compose() {
		$id = $this->record->ID;
		if ($id) {
			$pdf = ComposedPdf::get()->byID($id);
			if ($pdf->canView()) {
				$pdf->createPdf();
				Session::set('PdfComposed', true);
//				$this->redirectBack();
			} else {
				throw new Exception("You don't have permission to do this.");
			}
		}

		return $this->edit(Controller::curr()->getRequest());
	}

}
