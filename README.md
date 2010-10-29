# PDF Rendition Module

## Maintainer Contact

* Marcus Nyeholt marcus(at)silverstripe.com.au

## Requirements

* SilverStripe 2.4.2
* Java runtime environment (1.5 or greater)

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
* Add `Object::add_extension('Page_Controller', 'PdfControllerExtension');` to
  your _config.php
* Add $PdfLink in your template to insert a link to the PDF version of the page
* To customise the PDF layout, create a 'pdfrendition.css' file in your theme 
  directory and add styles specifically for your pdf. See the github wiki
  for some examples of how to do some common PDF based things. 

## Usage Overview

<Highlevel usage, refer to wiki documentation for details>

## Known issues

<Popular issues, how to solve them, and links to tickets in the bugtracker>