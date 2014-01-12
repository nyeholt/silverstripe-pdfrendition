<?php

/**
 *	Description of ComposedPdf
 *
 *	@authors Marcus Nyeholt <marcus@silverstripe.com.au> and Nathan Glasl <nathan@silverstripe.com.au>
 *	@license BSD http://silverstripe.org/BSD-license
 */

class ComposedPdf extends DataObject {

	public static $db = array(
		'Title'					=> 'Varchar(125)',
		'Description'			=> 'HTMLText',
		'TableOfContents'		=> 'Boolean',
		'Template'				=> 'Varchar',
	);
	public static $defaults = array(
		
	);
	
	public static $has_one = array(
		'Page'					=> 'Page',
	);

	public static $has_many = array(
		'Pdfs'					=> 'ComposedPdfFile',
	);
	
	public function onBeforeWrite() {
		parent::onBeforeWrite();
		if ($this->ID && !$this->Title) {
			
			throw new Exception("Invalid title");
		}
	}
	
	public function getCMSFields() {
		$fields = parent::getCMSFields();
		
		if ($this->ID) {

			// If a pdf composition has completed, alert the user of the success.

			Requirements::css('pdfrendition/css/cms-custom.css');

			if(Session::get('PdfComposed')) {
				$fields->addFieldToTab('Root.Main', new LiteralField('ComposeMessage', '<div class="pdfresult message good">This pdf has successfully been composed.</div>'), 'Title');
				Session::set('PdfComposed', false);
			}

			// Add buttons to preview/compose the current pdf.

			$fields->addFieldToTab('Root.Main', new LiteralField('PreviewLink', '<div class="field"><a href="admin/pdfs/' . $this->ClassName . '/previewpdf?ID=' . $this->ID.'" target="_blank" class="pdfaction action action ss-ui-button ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only">Preview</a>'), 'Title');
			$fields->addFieldToTab('Root.Main', new LiteralField('ComposeLink', '<div><a href="admin/pdfs/' . $this->ClassName . '/compose?ID=' . $this->ID.'" class="pdfaction action action ss-ui-action-constructive ss-ui-button ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only ui-state-hover">Compose</a></div></div>'), 'Title');
		}
		
		$fields->addFieldToTab('Root.Main', new CheckboxField('TableOfContents', _t('ComposedPdf.TOC', 'Table of contents?')), 'Description');
		$fields->addFieldToTab('Root.Main', new DropdownField('Template', _t('ComposedPdf.TEMPLATE', 'Template'), $this->templateSource()), 'Description');
		$fields->addFieldToTab('Root.Main', new TreeDropdownField('PageID', _t('ComposedPdf.ROOT_PAGE', 'Root Page'), 'Page'), 'Description');
		
		$pdfs = new TableListField(
			'Pdfs',
			'ComposedPdfFile',
			array(
					'Title'                                 => 'Title',
					'Created'                               => 'Generated',
					'ID'                                    => 'Links'
			),
			'"SourceID" = '.((int) $this->ID),
			'"Created" DESC'
		);

		$pdfs->setShowPagination(true);

		$links = '<a class=\'pdfDownloadLink\' target=\'blank\' href=\'".$Link()."\'>Download</a> ';

		$pdfs->setFieldFormatting(array(
				'ID' => $links,
		));
		
		$fields->addFieldToTab('Root.Pdfs', $pdfs);

		return $fields;
	}
	
	public function getCMSActions() {
		$actions = parent::getCMSActions();
		$actions->push(new FormAction('compose', _t('ComposedPdf.COMPOSE', 'Compose')));
		return $actions;
	}
	
	public function createPdf() {
		$storeIn = $this->getStorageFolder();
		$name = FileNameFilter::create()->filter($this->Title);
		$name = preg_replace('#[^A-Za-z0-9.+_\-]#','',$name);
		$name .= '.pdf';
		
		if (!$name) {
			throw new Exception("Must have a name!"); 
		}
		
		if (!$this->Template) {
			throw new Exception("Please specify a template before rendering.");
		}

		$file = new ComposedPdfFile;
		$file->ParentID = $storeIn->ID;
		$file->SourceID = $this->ID;
		$file->Title = $this->Title;
		$file->setName($name);
		$file->write();

		$content = $this->renderPdf();
		$filename = singleton('PdfRenditionService')->render($content);
		
		if (file_exists($filename)) {
			copy($filename, $file->getFullPath());
		}
	}

	public function renderPdf() {
		Requirements::clear();

		if (!$this->Template) {
			throw new Exception("Please specify a template before rendering.");
		}

		$content = $this->renderWith($this->Template);
		Requirements::restore();
		
		return $content;
	}
	
	protected function getStorageFolder() {
		$id = $this->ID;
		$folderName = 'composed-pdfs/'.$id;
		return Folder::find_or_make($folderName);
	}
		

	public static $template_paths = array();
	
	public function templatePaths() {
		if (!count(self::$template_paths)) {
			if (file_exists(Director::baseFolder() . DIRECTORY_SEPARATOR . THEMES_DIR . "/" . SSViewer::current_theme() . "/templates/pdfs")) {
				self::$template_paths[] = THEMES_DIR . "/" . SSViewer::current_theme() . "/templates/pdfs";
			}

			if (file_exists(Director::baseFolder() . DIRECTORY_SEPARATOR . project() . '/templates/pdfs')) {
				self::$template_paths[] = project() . '/templates/pdfs';
			}
			
			if (file_exists(Director::baseFolder() . DIRECTORY_SEPARATOR . 'pdfrendition/templates/pdfs')) {
				self::$template_paths[] = 'pdfrendition/templates/pdfs';
			}
		}

		return self::$template_paths;
	}

	/**
	 * Copied from NewsletterAdmin!
	 *
	 * @return array
	 */
	public function templateSource() {
		$paths = self::$this->templatePaths();
		$templates = array("" => _t('ComposedPdf.NONE', 'None'));

		if (isset($paths) && count($paths)) {
			$absPath = Director::baseFolder();
			if ($absPath{strlen($absPath) - 1} != "/")
				$absPath .= "/";

			foreach ($paths as $path) {
				$path = $absPath . $path;
				if (is_dir($path)) {
					$templateDir = opendir($path);

					// read all files in the directory
					while (( $templateFile = readdir($templateDir) ) !== false) {
						// *.ss files are templates
						if (preg_match('/(.*)\.ss$/', $templateFile, $match)) {
							$templates[$match[1]] = $match[1];
						}
					}
				}
			}
		}
		return $templates;
	}
}