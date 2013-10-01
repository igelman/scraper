

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
	$.each(data, function(rowNumber, item) {
		var button = '<button type="button" data-loading-text="Loading..."  class="btn btn-default download-and-process-button" id="button-refresh-' + rowNumber + '"><span class="glyphicon glyphicon-repeat"></span></button>';
		var row = "<tr class='item' id='row-" + rowNumber + "'>";
		row += "<td class='cell-date-retrieved' id='cell-date-retrieved-" + rowNumber + "'>" + item.date_retrieved + "</td>";
		row += "<td class='cell-set-number'>" + item.set_number + "</td>";
		row += "<td class='cell-url'>" + item.url + "</td>";
		row += "<td class='cell-button-refresh'>" + button + "</td>";
		row += "</tr>";
		dataRows += row;
	})
	dataTable += dataRows;
	dataTable += "</tbody></table>";
	$('#data').html(dataTable);

	bindDownloadAndProcessButtons();
}

function bindDownloadAndProcessButtons() {
	$('.download-and-process-button').on('click', function () {
		$(this).button('loading');
		var buttonId = $(this).attr('id');
		var rowNumber = buttonId.replace("button-refresh-", "");
		var url = $( "tr#row-" + rowNumber + " > td.cell-url" ).html();
		console.log("buttonId: " + buttonId);
		console.log("rowNumber: " + rowNumber);
		console.log("url: " + url);
		
		$.post(
			"ajax/ajax.php",
			{	'action'	:	"downloadAndProcess",
				'urls'		:	[url],
				'element-id'	:	buttonId,
			},
			function(data, status){
				var data = $.parseJSON(data);
				console.log("Status: ...");
				console.log(status);
				
				console.log("Data: ...");
				console.log(data);

				console.log("data['element-id']: ...");
				console.log(data['element-id']);
				
				console.log("data.package: ...");
				console.log(data.package);
				
				console.log("data.package[0]['time']: ...");
				console.log(data.package[0]['time']);
/*
				console.log(data.package0][url]);
				console.log(data.package.0.size);
				console.log(data.package.0.time);
*/
				
				
				var buttonId = data['element-id'];
				var rowNumber = buttonId.replace("button-refresh-", "");
				console.log("buttonId: " + buttonId);
				console.log('#cell-date-retrieved-' + rowNumber);
				
				$('tr#row-' + rowNumber).addClass('success');
				$('#cell-date-retrieved-' + rowNumber).html(data.package[0]['time']);
				$(buttonId).button('reset');
			}
		);

	});
}