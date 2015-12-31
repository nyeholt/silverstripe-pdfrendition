<?php

/**
 *	A file generated as the result of a composed pdf being created.
 *
 *	@author Marcus Nyeholt <marcus@silverstripe.com.au>
 *	@license BSD http://silverstripe.org/BSD-license
 */

class ComposedPdfFile extends File
{

    private static $summary_fields = array(
        'Title', 'Created',
    );

    private static $default_sort = 'Created DESC';

    private static $has_one = array(
        'Source'            => 'ComposedPdf',
    );
}
