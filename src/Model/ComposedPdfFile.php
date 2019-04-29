<?php

namespace Symbiote\PdfRendition\Model;

use Symbiote\PdfRendition\Model\ComposedPdf;
use SilverStripe\Assets\File;
use SilverStripe\Forms\LiteralField;

/**
 *	A file generated as the result of a composed pdf being created.
 *
 *	@author Marcus Nyeholt <marcus@silverstripe.com.au>
 *	@license BSD http://silverstripe.org/BSD-license
 */

class ComposedPdfFile extends File
{
    private static $table_name = 'ComposedPdfFile';

    private static $summary_fields = array(
        'Title', 'Created',
    );

    private static $default_sort = 'Created DESC';

    private static $has_one = array(
        'Source'            => ComposedPdf::class,
    );

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        if ($this->exists()) {
            $fields->push(LiteralField::create('pdflink', '<a href="' . $this->Link() . '">Download</a>'));
        }
        
        return $fields;
    }
}
