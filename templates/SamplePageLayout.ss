
<div class="printOnly" id="PrintCoverPage">
	<% include PdfFooter %>
	<div class="printLogo">
		<img src="themes/mytheme/css/images/my-logo.jpg" />
	</div>
	<h1>$Title</h1>
	<p>Generated $Now.Nice</p>
</div>

<div id="PageContent" class="portrait">
	<% include PdfHeaderFooter %>
	$Content
</div>

<div class="printHidden">
	<div id="PageForm">
	$Form
	</div>
</div>