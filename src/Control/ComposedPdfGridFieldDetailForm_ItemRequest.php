<?php

namespace Symbiote\PdfRendition\Control;

use Exception;

use SilverStripe\Control\Controller;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\GridField\GridFieldDetailForm_ItemRequest;
use Symbiote\PdfRendition\Model\ComposedPdf;
use SilverStripe\Control\Session;



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
	 * 	@return string
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
	 * 	@return string
	 */
	public function compose() {
		$id = $this->record->ID;
		if ($id) {
			$pdf = ComposedPdf::get()->byID($id);
			if ($pdf->canView()) {
                $pdf->createPdf();
                Controller::curr()->getRequest()->getSession()->set('PdfComposed', 1);
//				$this->redirectBack();
			} else {
				throw new Exception("You don't have permission to do this.");
			}
		}

		return $this->edit(Controller::curr()->getRequest());
	}

}
