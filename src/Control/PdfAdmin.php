<?php

namespace Symbiote\PdfRendition\Control;

use SilverStripe\Admin\ModelAdmin;
use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\View\Requirements;
use Symbiote\PdfRendition\Model\ComposedPdf;

/**
 *  Admin controller for creating and managing composed PDFs.
 *
 *  @authors Marcus Nyeholt <marcus@silverstripe.com.au> and Nathan Glasl <nathan@silverstripe.com.au>
 *  @license BSD http://silverstripe.org/BSD-license
 */
class PdfAdmin extends ModelAdmin
{
    private static $allowed_actions = [
        'previewpdf'
    ];

    private static $url_segment = 'pdfs';

    private static $menu_title = 'PDFs';

    private static $managed_models = [
        ComposedPdf::class
    ];

    public function init()
    {
        parent::init();
        Requirements::javascript('symbiote/silverstripe-pdfrendition: client/javascript/pdfrendition.js');
    }

    public function getEditForm($id = null, $fields = null)
    {
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
