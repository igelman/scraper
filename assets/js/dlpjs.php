

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
	data = $.parseJSON(data);
	var thead = "<thead><th>Date Retrieved</th><th>Set</th><th>Url</th></thead>";
	var dataTable = "<table class='table table-hover table-condensed'>" + thead + "<tbody>";
	var dataRows = "";
	$.each(data, function(row, item) {
		var button = '<button type="button" data-loading-text="Loading..."  class="btn btn-default download-and-process-button" id="button-refresh-' + row + '"><span class="glyphicon glyphicon-repeat"></span></button>';
		var row = "<tr class='item' id='row-" + row + "'>";
		row += "<td class='cell-date-retrieved'>" + item.date_retrieved + "</td>";
		row += "<td class='cell-set-number'>" + item.set_number + "</td>";
		row += "<td class='cell-url'>" + item.url + "</td>";
		row += "<td class='cell-button-refresh'>" + button + "</td>";
		row += "</tr>";
		dataRows += row;
	})
	dataTable += dataRows;
	dataTable += "</tbody></table>";
	$('#data').html(dataTable);

	
	YUI().use('datatable-sort', function (Y) {
		// DataTable is available and ready for use. Add implementation
		// code here.
		// Columns must match data object property names

		//console.log( data);
		//data = $.parseJSON(data);
		
		var columns = [
			{
				label:	'Select',
				
			},
			{
				key:	'date_retrieved',
				label:	'Date',
			},
			{
				key:	'set_number',
				label:	'Set',
			},
			{
				key:	'url',
				label:	'URL',
				formatter: '<a href="{value}" target="_blank">{value}</a>',
				allowHTML:	true,
			},
			{
				label:		'Update',
				formatter:	function(o) {
								button = '<button type="button" data-loading-text="Loading..."  class="btn btn-default download-and-process-button" id="button-refresh-' + o.rowIndex + '"><span class="glyphicon glyphicon-repeat"></span></button>';
								return button;
							},
				allowHTML:	true,	
			},
			{
				label:		'Info',
				formatter:	function(o) {
								content = "o.rowIndex: " + o.rowIndex;
								return content;
							}
			}
		];
	
		var table = new Y.DataTable({
		    columns: columns,
		    data: data,
			sortable: true,
		    caption: "Caption",
		    summary: "Summary"
		});
		
		$('body').addClass('yui3-skin-sam');
		//table.render("#data");


		bindDownloadAndProcessButtons();
/*
		console.log("table.getCell([0,1]): ...");
		console.log(table.getCell([0,1]));
		console.log("table.getRecord(1): ...");
		console.log(table.getRecord(1));
		console.log("table.getRow(1): ...");
		console.log(table.getRow(1));
*/

	});
}

function bindDownloadAndProcessButtons() {
	$('.download-and-process-button').on('click', function () {
		$(this).button('loading');
		var buttonId = $(this).attr('id');
		var rowNumber = buttonId.replace("button-refresh-", "");
		var url = $( "tr#row-" + rowNumber + " > td.cell-url" ).html();
		
/*
		$.post(
			"ajax/ajax.php",
			{	'action'	:	"downloadAndProcess",
				'urls'		:	urls,
				'elementId'	:	buttonId,
			},
			function(data, status){
				console.log(status);
				console.log(data);
				console.log(buttonId);
				$(buttonId).button('reset');
			}
		);
*/

	});
}