# PDF Rendition Module

## Maintainer Contact

* Marcus Nyeholt marcus(at)silverstripe.com.au
* Nathan Glasl nathan(at)silverstripe.com.au

## Requirements

* SilverStripe 2.4.X
* Tidy (preferably the built in PHP tidy module, otherwise the commandline
binary)
* Java >= 1.5 installed on the SilverStripe server

## Documentation

This module allows users to easily create complex PDF renditions of content
by utilising HTML and CSS3 to define page layouts for printing. It provides
a simple extension that adds a simple action for automatically generating
PDF renditions of a page, and an API for developers to generate more
specific PDF renditions.

Please see http://github.com/nyeholt/silverstripe-pdfrendition for more
details about specific styling tips

## Installation Instructions

* Extract to your silverstripe folder in /pdfrendition

## Usage Overview

* Add `Object::add_extension('Page_Controller', 'PdfControllerExtension');` to
  your _config.php
* Add $PdfLink in your template to insert a link to the PDF version of the page
* To customise the PDF layout, create a 'pdfrendition.css' file in your theme
  directory and add styles specifically for your pdf. See the github wiki
  for some examples of how to do some common PDF based things.


## Known issues

**Rendition Failures**

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
