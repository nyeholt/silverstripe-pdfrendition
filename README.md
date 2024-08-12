# PDF Rendition Module

## Maintainer Contact

* Marcus Nyeholt marcus@symbiote.com.au
* Nathan Glasl nathan@symbiote.com.au

## Requirements

* SilverStripe 4.12+ || 5+
* Tidy (preferably the built in PHP tidy module, otherwise the commandline
binary)
* Java 1.8 (the latest version this has been tested against)
  * **Important:** earlier 1.7 versions cause the PDF to not load _Cloudflare_ specific assets such as images and CSS
  * **Important:** regardless of the version you end up using, make sure the PDF output is correct on the production server prior to go-live (please see the known issues section below)

## Documentation

This module allows users to easily create complex PDF renditions of content
by utilising HTML and CSS3 to define page layouts for printing. It provides
a simple extension that adds a simple action for automatically generating
PDF renditions of a page, and an API for developers to generate more
specific PDF renditions.

## Installation Instructions

`composer require symbiote/silverstripe-pdfrendition`

## Usage Overview

* Add `Symbiote\PdfRendition\Extension\PdfControllerExtension` as an extension to PageController 
* Add $PdfLink in your template to insert a link to the PDF version of the page
* To customise the PDF layout, create a 'pdfrendition.css' file in your theme directory, link to it with your preferred method (@import, requirements, etc.) and add styles specifically for your pdf using the @print media query. See the github wiki for some examples of how to do some common PDF based things.

## Known Issues / Troubleshooting

* Using HTTPS without a valid certificate can cause the PDF to not render correctly.
* _Cloudflare_ can cause the PDF to not render correctly.
  * This may be due to _Java_ attempting to reference the assets (images and CSS), and being listed as a "bad browser".
    * To resolve this, _Cloudflare_ `Page Rules` need to be added for those specific assets (or a general `/*` blanket rule) with `Browser Integrity Check` set to `Off`.
  * This may also be due to the _Java_ version (please see above).
  * The PDF output can be tested by intercepting the render process locally and holding onto the `xhtml` file generated prior to render.
  * Using this, replace the asset URLs with those you want to test and confirm (production for example).
    * The _Java Flying Saucer_ utility will retrieve assets and external sources via links contained in the `xhtml` source. Ensure these links are fully formed, and are able to be retrieved from within the production server (i.e. outbound firewall restrictions or localised DNS/host definitions could cause issues).
  * Using this `/tmp/xhtml`, the below should give you a correctly rendered PDF when run from within the production server.
  * If not, _Cloudflare_ and/or _Java_ are likely the issue.

> java -classpath '**{project}**/pdfrendition/thirdparty/xhtmlrenderer/flying-saucer-core-9.0.7.jar:**{project}**/pdfrendition/thirdparty/xhtmlrenderer/flying-saucer-pdf-9.0.7.jar:**{project}**/pdfrendition/thirdparty/xhtmlrenderer/itext-4.2.1.jar' org.xhtmlrenderer.simple.PDFRenderer '/tmp/xhtml' '/tmp/output.pdf'

* Make sure you don't define @font-face inside @media print.

Occasionally a page won't correctly render, throwing some kind of junk
back to the browser as the PDF rendition process fails. Typically,
this is caused by malformed XML being sent to the renderer; for this reason
everything is first passed through HTML Tidy, however in some rare cases
this can still not correctly convert the raw content.

In these cases, errors will be sent through to your error log files; it
will indicate the temporary files that were created, so you should first
check these for XML errors. If that does not work, you can also
attempt to manually perform the conversion using commandline tidy
and the commandline for the PDF rendition to see if there are more
verbose errors available for debugging the problem. 
