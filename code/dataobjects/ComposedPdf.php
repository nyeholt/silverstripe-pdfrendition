<?php

/**
 * Description of ComposedPdf
 *
 * @author Marcus Nyeholt <marcus@silverstripe.com.au>
 * @license BSD http://silverstripe.org/BSD-license
 */
class ComposedPdf extends DataObject {

	public static $db = array(
		'Title'					=> 'Varchar(125)',
		'TableOfContents'		=> 'Boolean',
		'Template'				=> 'Varchar',
	);
	public static $defaults = array(
	);
	
	public static $has_one = array(
		'Page'					=> 'Page',
	);
	
	public static $many_many = array(
		'Pages'					=> 'Page',
	);
	
	public function getCMSFields() {
		$fields = parent::getCMSFields();
		
		$fields->addFieldToTab('Root.Main', new DropdownField('Template', _t('ComposedPdf.TEMPLATE', 'Template'), $this->templateSource()));
//		$fields->addFieldToTab('Root.Main', new TreeMultiselectField('Pages', _t('ComposedPdf.PAGES', 'Pages'), 'Page'));
		$fields->addFieldToTab('Root.Main', new TreeDropdownField('PageID', _('ComposedPdf.ROOT_PAGE', 'Root Page'), 'Page'));
		
		return $fields;
	}
	
	public function getCMSActions() {
		$actions = parent::getCMSActions();
		$actions->push(new FormAction('compose', 'Compose'));
		return $actions;
	}
	
	/**
	 * 
	 */
	public function getComposedContent() {
		if ($this->PageID) {
			$root = $this->Page();
			
			if ($this->Template) {
				$content = $this->renderWith($this->Template);
			} else {
				$content = $content;
			}
		}
	}
	
	protected function includeContentFrom($item) {
		
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
