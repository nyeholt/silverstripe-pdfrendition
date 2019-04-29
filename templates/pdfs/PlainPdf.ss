<html>
	<head>
		<% base_tag %>
		<% require css(symbiote/silverstripe-pdfrendition: client/css/pdfrendition.css) %>
	</head>
	<body>
		<div id="PrintCoverPage">
			<div class="printLogo">
			</div>
			<h1>$Title</h1>
			<p class="reportDescription">$Description</p>
			<p>Generated $LastEdited.Nice</p>
		</div>
		<div class="landscape newPage">
			<h1>$Page.Title</h1>
			$Page.Content
		</div>
	</body>
</html>