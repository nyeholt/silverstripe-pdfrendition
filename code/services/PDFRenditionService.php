<?php

/**
 *	A class that handles the rendition of pages into PDFs.
 *
 *	@authors Marcus Nyeholt <marcus@silverstripe.com.au> and Nathan Glasl <nathan@silverstripe.com.au>
 *	@license http://silverstripe.org/bsd-license/
 */

class PDFRenditionService {

	public static $tidy_bin = "/usr/bin/tidy";
	public static $java_bin = "/usr/bin/java";

	public function __construct() {
		
	}

	/**
	 * Renders passed in content to a PDF.
	 *
	 * If $outputTo == '', then the temporary filename is returned, with the expectation
	 * that the caller will correctly handle the streaming of the content.
	 *
	 * @param String $content
	 * 			Raw content to render into a pdf
	 * @param String $outputTo
	 * 				'file' or 'browser'
	 * @param String $outname
	 * 				A filename if the pdf is sent direct to the browser
	 * @return String
	 * 				The filename of the output file
	 */
	public function render($content, $outputTo = null, $outname='') {
		$tempFolder = getTempFolder();
		if (!is_dir($tempFolder)) {
			throw new Exception("Could not find TMP directory");
		}

		$pdfFolder = $tempFolder . '/pdfrenditions';

		if (!file_exists($pdfFolder)) {
			@mkdir($pdfFolder);
		}

		if (!is_dir($pdfFolder)) {
			throw new Exception("PDF temp directory could not be found");
		}

		$in = tempnam($pdfFolder, "html_");
		chmod($in, 0664);

		$content = $this->fixLinks($content);
		$content = str_replace('&nbsp;', '&#160;', $content);
		$content = http::absoluteURLs($content);

		file_put_contents($in, $content);

		$mid = tempnam($pdfFolder, "xhtml_");
		chmod($mid, 0664);

		$out = tempnam($pdfFolder, "pdf_") . '.pdf';

		if (class_exists('Tidy')) {
			$this->tidyHtml($in, $mid);
		} else {
			$this->tidyHtmlExternal($in, $mid);
		}


		// then run it through our pdfing thing
		$jarPath = dirname(dirname(dirname(__FILE__))) . '/thirdparty/xhtmlrenderer';
		$classpath = $jarPath . '/core-renderer.jar' . PATH_SEPARATOR . $jarPath . '/iText-2.0.8.jar';

		$cmd = self::$java_bin;
		if (!is_executable($cmd)) {
			$cmd = "java";
		}

		$escapefn = 'escapeshellarg';

		$cmd = "$cmd -classpath " . $escapefn($classpath) . " org.xhtmlrenderer.simple.PDFRenderer " . $escapefn($mid) . ' ' . $escapefn($out);
		$retVal = exec($cmd, $output, $return);

		if (!file_exists($out)) {
			throw new Exception("Could not generate pdf using command $cmd: " . var_export($output, true));
		}

		unlink($in);
		unlink($mid);

		if (!($outputTo == 'browser')) {
			return $out;
		}

		if (file_exists($out)) {
			$size = filesize($out);
			$type = "application/pdf";
			$name = urlencode(htmlentities($outname));
			if (!headers_sent()) {
				// set cache-control headers explicitly for https traffic, otherwise no-cache will be used,
				// which will break file attachments in IE
				// Thanks to Niklas Forsdahl <niklas@creamarketing.com>
				if (isset($_SERVER['HTTPS'])) {
					header('Cache-Control: private');
					header('Pragma: ');
				}
				header('Content-disposition: attachment; filename=' . $name);
				header('Content-type: application/pdf'); //octet-stream');
				header('Content-Length: ' . $size);
				readfile($out);
			} else {
				echo "Invalid file";
			}
		}

		unlink($out);
	}

	protected function tidyHtml($input, $output) {
		$tidy_config = array(
			'clean' => true,
			'quote-nbsp' => false,
			'drop-proprietary-attributes' => true,
			'output-xhtml' => true,
			'word-2000' => true,
			'wrap' => '0'
		);

		$tidy = new tidy;
		$out = $tidy->repairFile($input, $tidy_config, 'utf8');
		file_put_contents($output, $out);
	}

	protected function tidyHtmlExternal($input, $output) {
		$tidy = self::$tidy_bin;
		if (!is_executable($tidy)) {
			$tidy = "tidy";
		}

		$escapefn = 'escapeshellarg';

		$cmd = "$tidy -utf8 -asxhtml -output " . $escapefn($output) . ' ' . $escapefn($input);

		// first we need to tidy the content
		exec($cmd, $out, $return);

		if (filesize($output) <= 0) {
			throw new Exception("Invalid Tidy output from command $cmd: " . print_r($out, true) . "\n" . print_r($return, true));
		}
	}

	/**
	 * Fixes URLs in images, link and a tags to refer to correct things relevant to the base tag. 
	 *
	 * @param String $contentFile
	 * 				The name of the file to fix links within
	 */
	protected function fixLinks($content) {
		$value = SS_HTML4Value::create($content);

		$base = $value->getElementsByTagName('base');
		if ($base && $base->item(0)) {
			$base = $base->item(0)->getAttribute('href');
			$check = array('a' => 'href', 'link' => 'href', 'img' => 'src');
			foreach ($check as $tag => $attr) {
				if ($items = $value->getElementsByTagName($tag)) {
					foreach ($items as $item) {
						$href = $item->getAttribute($attr);
						if ($href && $href{0} != '/' && strpos($href, '://') === false) {
							$item->setAttribute($attr, $base . $href);
						}
					}
				}
			}
		}

		return $value->getContent();
	}

	/**
	 * Renders the contents of a silverstripe URL into a PDF
	 *
	 * @param String $url
	 * 			A relative URL that silverstripe can execute
	 * @param String $outputTo
	 */
	public function renderUrl($url, $outputTo = null, $outname='') {
		if (strpos($url, '/') === 0) {
			// fix it
			$url = Director::makeRelative($url);
		}
		// convert the URL to content
		// do a 'test' request, making sure to keep the current session active
		$response = Director::test($url, null, new Session($_SESSION));
		if ($response->getStatusCode() == 200) {
			return $this->render($response->getBody(), $outputTo, $outname);
		} else {
			throw new Exception("Failed rendering URL $url: " . $response->getStatusCode() . " - " . $response->getStatusDescription());
		}
	}

	/**
	 *
	 * @param SiteTree $page
	 * 				The page that should be rendered
	 * @param String $action
	 * 				An action for the page to render
	 * @param String $outputTo
	 * 				'file' or 'browser'
	 * @return String
	 * 				The filename of the output file
	 */
	public function renderPage($page, $action='', $outputTo = null, $outname='') {
		$link = Director::makeRelative($page->Link($action));
		return $this->renderUrl($link, $outputTo, $outname);
	}

}