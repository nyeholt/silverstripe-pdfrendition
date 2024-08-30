<?php

namespace Symbiote\PdfRendition\Model;

use SilverStripe\Assets\File;
use SilverStripe\Forms\LiteralField;

/**
 *  A file generated as the result of a composed pdf being created.
 *
 *  @author Marcus Nyeholt <marcus@silverstripe.com.au>
 *  @license BSD http://silverstripe.org/BSD-license
 */

class ComposedPdfFile extends File
{
    private static $table_name = 'ComposedPdfFile';

    private static $has_one = [
        'Source' => ComposedPdf::class
    ];

    private static $summary_fields = [
        'Title',
        'Created'
    ];

    private static $default_sort = 'Created DESC';

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        if ($this->exists()) {
            $fields->push(LiteralField::create('pdflink', '<a href="' . $this->Link() . '">Download</a>'));
        }

        return $fields;
    }
}
