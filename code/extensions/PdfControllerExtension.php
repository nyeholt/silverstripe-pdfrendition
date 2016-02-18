<?php

/**
 *	Action for converting a page to a pdf.
 *
 *	@author marcus@silverstripe.com.au
 *	@license http://silverstripe.org/bsd-license/
 */

class PdfControllerExtension extends Extension
{
    public static $allowed_actions = array(
        'topdf',
    );

    /**
     * Return a link to generate the current content item as a PDF
     *
     * @return string
     */
    public function PdfLink()
    {
        return $this->owner->Link('topdf').'/'.$this->owner->data()->URLSegment.'.pdf';
    }

    /**
     * Generates a PDF file for the current page
     */
    public function topdf()
    {
        singleton('PDFRenditionService')->renderPage($this->owner->data(), '', 'browser');
        return;
    }
}
