

function init() {
	console.log("called init");
	urls = [
		"http://www.retailmenot.com/view/giorgioarmanibeauty-usa.com",
		"http://www.retailmenot.com/view/blissworld.com",
		]
	listAllUrls();
	//downloadAndProcess(urls);
	return;
}

function listAllUrls() {
	console.log("called listAllUrls");
	$.post(
		"ajax/ajax.php",
		{	'action': "listAllUrls",
		},
		function(data, status){
			console.log("ajax success function");
/*
			console.log(status);
			console.log(data);
*/
			makeSourceTable(data);
		}
	);
}

function downloadAndProcess(urls) {
	console.log("called downloadAndProcess");
	$.post(
		"ajax/ajax.php",
		{	'action': "downloadAndProcess",
			'urls': urls
		},
		function(data, status){
			console.log("calling makeSourceTable");
			makeSourceTable(data);
		}
	);
	
}

function makeSourceTable(data) {
	console.log("in makeSourceTable");
	// Create a new YUI instance and populate it with the required modules.
	YUI().use('datatable-sort', function (Y) {
		// DataTable is available and ready for use. Add implementation
		// code here.
		// Columns must match data object property names

		//console.log( data);
		data = $.parseJSON(data);
		
		var columns = [
			{	key:	'date_retrieved',
				label:	'Date',
			},
			{	key:	'set_number',
				label:	'Set',
			},
			{
				key:	'url',
				label:	'URL',
				formatter: '<a href="{value}" target="_blank">{value}</a>',
				allowHTML:	true,
			},
			{
				key:	'url',
				label:	'Click',
				formatter:	'<button class="download-and-process-button" id="button-{value}"></button>',
				allowHTML: true,	
			},
		];
		
		var table = new Y.DataTable({
		    columns: columns,
		    data: data,
			sortable: true,
		    caption: "Caption",
		    summary: "Summary"
		});
		
		$('body').addClass('yui3-skin-sam');
		table.render("#data");
		bindDownloadAndProcessButtons();

	});
}

function bindDownloadAndProcessButtons() {
	$('.download-and-process-button').on('click', function () {
		//$(this).button('loading');
		console.log("button clicked");
		console.log($(this));
		//console.log($(this).attr('id'));
		buttonId = $(this).attr('id');
		
		urls = [buttonId.replace('button-', '')];
		console.log("url: " + urls);
		$.post(
			"ajax/ajax.php",
			{	'action': "downloadAndProcess",
				'urls': urls
			},
			function(data, status){
				console.log(status);
				console.log(data);
			}
		);

	});
}