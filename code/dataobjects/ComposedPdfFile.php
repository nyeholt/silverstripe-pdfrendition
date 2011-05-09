<?php

/**
 * A file generated as the result of a composed pdf being created
 *
 * @author Marcus Nyeholt <marcus@silverstripe.com.au>
 * @license BSD http://silverstripe.org/BSD-license
 */
class ComposedPdfFile extends File {
	public static $has_one = array(
		'Source'			=> 'ComposedPdf',
	);
}
