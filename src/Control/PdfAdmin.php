<?php

namespace Symbiote\PdfRendition\Control;

use Symbiote\PdfRendition\Model\ComposedPdf;
use SilverStripe\Admin\ModelAdmin;
use SilverStripe\View\Requirements;
use SilverStripe\Forms\GridField\GridFieldDetailForm_ItemRequest;
use SilverStripe\Forms\FormAction;




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
		ComposedPdf::class,
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
