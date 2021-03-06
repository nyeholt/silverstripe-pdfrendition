<?php

namespace Symbiote\PdfRendition\Control;

use Symbiote\PdfRendition\Model\ComposedPdf;
use SilverStripe\Admin\ModelAdmin;
use SilverStripe\View\Requirements;
use SilverStripe\Forms\GridField\GridFieldDetailForm_ItemRequest;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\GridField\GridFieldDetailForm;

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
		Requirements::javascript('symbiote/silverstripe-pdfrendition: client/javascript/pdfrendition.js');
	}

	public function getEditForm($id = null, $fields = null) {
        $form = parent::getEditForm($id, $fields);
        
		if ($this->modelClass == ComposedPdf::class) {
            $fs = $form->Fields();
			$grid = $form->Fields()->dataFieldByName(str_replace('\\', '-', $this->modelClass));
			$editForm = $grid ? $grid->getConfig()->getComponentByType(GridFieldDetailForm::class) : null;
			if ($editForm) {
				$editForm->setItemRequestClass(ComposedPdfGridFieldDetailForm_ItemRequest::class);
			}
		}

		return $form;
	}

}
