<?php

namespace Symbiote\PdfRendition\Model;

use Exception;
use SilverStripe\Assets\FileNameFilter;
use SilverStripe\Assets\Folder;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\TreeDropdownField;
use SilverStripe\ORM\DataObject;
use SilverStripe\View\Requirements;
use Symbiote\PdfRendition\Service\PDFRenditionService;

/**
 *  @authors Marcus Nyeholt <marcus@symbiote.com.au> and Nathan Glasl <nathan@symbiote.com.au>
 *  @license BSD http://silverstripe.org/BSD-license
 */

class ComposedPdf extends DataObject
{
    private static $table_name = 'ComposedPdf';

    private static $db = [
        'Title' => 'Varchar(125)',
        'TableOfContents' => 'Boolean',
        'Description' => 'HTMLText',
        'Template' => 'Varchar'
    ];

    private static $defaults = [];

    private static $has_one = [
        'Page' => 'Page'
    ];

    private static $has_many = [
        'Pdfs' => ComposedPdfFile::class
    ];

    private static $template_paths = [];

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        if ($this->ID && !$this->Title) {
            throw new \Exception("Invalid title");
        }
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        if ($this->ID) {
            // If a pdf composition has completed, alert the user of the success.

            Requirements::css('symbiote/silverstripe-pdfrendition: client/css/cms-custom.css');

            if (Controller::has_curr() && Controller::curr()->getRequest()->getSession()->get('PdfComposed')) {
                $fields->addFieldToTab(
                    'Root.Main',
                    new LiteralField(
                        'ComposeMessage',
                        '<div class="pdfresult message good">This pdf has successfully been composed.</div>'
                    ),
                    'Title'
                );
                Controller::curr()->getRequest()->getSession()->clear('PdfComposed');
            }

            // Add buttons to preview/compose the current pdf.

//          $fields->addFieldToTab('Root.Main', new LiteralField('PreviewLink', '<div class="field"><a href="admin/pdfs/' . $this->ClassName . '/previewpdf?ID=' . $this->ID.'" target="_blank" class="pdfaction action action ss-ui-button ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only">Preview</a>'), 'Title');
//          $fields->addFieldToTab('Root.Main', new LiteralField('ComposeLink', '<div><a href="admin/pdfs/' . $this->ClassName . '/compose?ID=' . $this->ID.'" class="pdfaction action action ss-ui-action-constructive ss-ui-button ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only ui-state-hover">Compose</a></div></div>'), 'Title');

            $pdfs = $fields->fieldByName('Pdfs');
        } else {
            $fields->removeByName('Pdfs');
        }

        $fields->addFieldToTab(
            'Root.Main',
            new TreeDropdownField(
                'PageID',
                _t('ComposedPdf.ROOT_PAGE', 'Root Page'),
                'Page'
            ),
            'Description'
        );
        $fields->addFieldToTab(
            'Root.Main',
            new DropdownField(
                'Template',
                _t('ComposedPdf.TEMPLATE', 'Template'),
                $this->templateSource()
            ),
            'Description'
        );

        return $fields;
    }

    public function getCMSActions()
    {
        $actions = parent::getCMSActions();
        $actions->push(new FormAction('compose', _t('ComposedPdf.COMPOSE', 'Compose')));
        return $actions;
    }

    public function createPdf()
    {
        $storeIn = $this->getStorageFolder();
        $name = FileNameFilter::create()->filter($this->Title);
        $name .= '.pdf';

        if (!$name) {
            throw new Exception("Must have a name!");
        }

        if (!$this->Template) {
            throw new Exception("Please specify a template before rendering.");
        }

        $file = ComposedPdfFile::create();
        $file->ParentID = $storeIn->ID;
        $file->SourceID = $this->ID;
        $file->Title = $this->Title;
        $file->Name = $name;
        $file->write();

        $content = $this->renderPdf();
        $filename = singleton(PDFRenditionService::class)->render($content);

        if (file_exists($filename)) {
            $file->setFromLocalFile($filename);
            $file->Name = $name;
            $file->write();
            unlink($filename);
        }
    }

    public function renderPdf()
    {
        Requirements::clear();

        if (!$this->Template) {
            throw new Exception("Please specify a template before rendering.");
        }

        $paths = $this->templatePaths();

        $templates = [];
        foreach ($paths as $p) {
            $templates[] = $p . '/' . $this->Template . '.ss';
        }

        $content = $this->renderWith($templates);
        Requirements::restore();

        return $content;
    }

    protected function getStorageFolder()
    {
        $id = $this->ID;
        $folderName = 'composed-pdfs/' . $id;
        return Folder::find_or_make($folderName);
    }

    public function templatePaths()
    {
        if (!count(self::$template_paths)) {
            if (file_exists(Director::baseFolder() . DIRECTORY_SEPARATOR . project() . '/templates/pdfs')) {
                self::$template_paths[] = project() . '/templates/pdfs';
            }

            if (file_exists(Director::baseFolder() . DIRECTORY_SEPARATOR . 'vendor/symbiote/silverstripe-pdfrendition/templates/pdfs')) {
                self::$template_paths[] = 'vendor/symbiote/silverstripe-pdfrendition/templates/pdfs';
            }
        }

        return self::$template_paths;
    }

    /**
     * Copied from NewsletterAdmin!
     *
     * @return array
     */
    public function templateSource()
    {
        $paths = self::templatePaths();
        $templates = ["" => _t('ComposedPdf.NONE', 'None')];

        if (isset($paths) && count($paths)) {
            $absPath = Director::baseFolder();
            if ($absPath[strlen($absPath) - 1] != "/") {
                $absPath .= "/";
            }

            foreach ($paths as $path) {
                $path = $absPath . $path;
                if (is_dir($path)) {
                    $templateDir = opendir($path);

                    // read all files in the directory
                    while (($templateFile = readdir($templateDir)) !== false) {
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
