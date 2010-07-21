<?php
/*

Copyright (c) 2009, SilverStripe Australia PTY LTD - www.silverstripe.com.au
All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

    * Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
    * Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the
      documentation and/or other materials provided with the distribution.
    * Neither the name of SilverStripe nor the names of its contributors may be used to endorse or promote products derived from this software
      without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE
GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT,
STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY
OF SUCH DAMAGE.
*/

/**
 * A class that handles the rendition of pages into PDFs
 *
 * @author Marcus Nyeholt <marcus@silverstripe.com.au>
 */
class PDFRenditionService
{

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
	 *			Raw content to render into a pdf
	 * @param String $outputTo
	 *			a filename to output to
	 */
	public function render($content, $outputTo = null) {
		$tempFolder = getTempFolder();
		if (!is_dir($tempFolder)) {
			throw new Exception("Could not find TMP directory");
		}

		$pdfFolder = $tempFolder.'/pdfrenditions';

		if (!file_exists($pdfFolder)) {
			@mkdir($pdfFolder);
		}

		if (!is_dir($pdfFolder)) {
			throw new Exception("PDF temp directory could not be found");
		}

		$in = tempnam($pdfFolder, "html_");
		file_put_contents($in, $content);
		
		$mid = tempnam($pdfFolder, "xhtml_");

		$out = tempnam($pdfFolder, "pdf_") . '.pdf';

		$tidy = self::$tidy_bin;
		if (!is_executable($tidy)) {
			$tidy = "tidy";
		}

		$cmd = "$tidy -utf8 -output $mid $in";
		// first we need to tidy the content
		@exec($cmd, $output, $return);

		// then run it through our pdfing thing
		$jarPath = dirname(dirname(dirname(__FILE__))).'/thirdparty/xhtmlrenderer';
		$classpath = $jarPath.'/core-renderer.jar'.PATH_SEPARATOR.$jarPath.'/iText-2.0.8.jar';

		$cmd = self::$java_bin;
		if (!is_executable($cmd)) {
			$cmd = "java";
		}

		$cmd = "$cmd -classpath $classpath org.xhtmlrenderer.simple.PDFRenderer $mid $out";
		$retVal = exec($cmd, $output, $return);

		if (!file_exists($out)) {
			throw new Exception("Could not generate pdf for $outputTo");
		}

		unlink($in);
		unlink($mid);

		if (!$outputTo) {
			return $out;
		}

		unlink($out);
	}

	/**
	 * Renders the contents of a silverstripe URL into a PDF
	 *
	 * @param String $url
	 *			A relative URL that silverstripe can execute
	 * @param String $outputTo
	 */
	public function renderUrl($url, $outputTo = null) {
		if (strpos($url, '/') === 0) {
			// fix it
			$url = Director::makeRelative($url);
		}
		// convert the URL to content

		// do a 'test' request, making sure to keep the current session active
		$response = Director::test($url, null, new Session($_SESSION));
		if ($response->getStatusCode() == 200) {
			return $this->render($response->getBody());
		} else {
			throw new Exception("Failed rendering URL $url: ".$response->getStatusCode()." - ".$response->getStatusDescription());
		}
	}

	public function renderPage($page, $action='', $outputTo = null) {
		$link = Director::makeRelative($page->Link());
		return $this->renderUrl($link);
	}
}
?>