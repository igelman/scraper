

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
			bindRefreshSelectedButton();
			bindRefreshRecordButtons();
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
	
	var refreshSelectedButton = "<button type='button' data-loading-text='Loading...'  class='btn btn-default' id='refresh-selected'><span class='glyphicon glyphicon-repeat'></span></button>";
	var thead = "<thead><th>" + refreshSelectedButton + "</th><th>Date Retrieved</th><th>Set</th><th>Url</th></thead>";
	var dataTable = "<table class='table table-hover table-condensed'>" + thead + "<tbody>";
	var dataRows = "";
	$.each(data, function(rowNumber, item) {
		var refreshRecordButton = "<button type='button' data-loading-text='Loading...'  class='btn btn-default refresh-record' id='button-refresh-" + rowNumber + "'><span class='glyphicon glyphicon-repeat'></span></button>";
		var refreshCheckBox = "<input type='checkbox' class='select-refresh' id='select-refresh-" + rowNumber +"'>";
		var row = "<tr class='item' id='row-" + rowNumber + "'>";
		row += makeTableCell(refreshCheckBox, "", "cell-checkbox-refresh");
		row += makeTableCell(item.date_retrieved, "cell-date-retrieved-" + rowNumber, "cell-date-retrieved");
		row += makeTableCell(item.set_number, "", "cell-set-number");
		row += makeTableCell(item.url, "cell-url-" + rowNumber, "cell-url");
		row += makeTableCell(refreshRecordButton, "", "cell-button-refresh");
		row += "</tr>";
		dataRows += row;
	})
	dataTable += dataRows;
	dataTable += "</tbody></table>";
	$('#data').html(dataTable);

/*
	bindRefreshSelectedButton();
	bindRefreshRecordButtons();
*/
}


function makeTableCell(cellContent, cellId, cellClass) {
	return "<td class='" + cellClass + "' id='" + cellId + "'>" + cellContent + "</td>";
}


function bindRefreshSelectedButton() {
	$('#refresh-selected').on('click', function() {
		console.log("clicked #refresh-selected");
		$(this).button('loading');

		var selectedCheckboxes = $('.select-refresh:checked'); // set of selected checkboxes
		if (selectedCheckboxes.length > 5) { // don't do more than five, else we risk getting the IP blocked
			console.log("Risky to try so many");
			$(this).button('reset');
			return;
		}

		console.log("about to loop through checkboxes");
		var selectedRows = new Array();
		var selectedUrls = new Array();
		$.each(selectedCheckboxes, function() {
			console.log($(this).attr('id'));
			var selectedCheckboxId = $(this).attr('id');
			var selectedRowNumber = selectedCheckboxId.replace("select-refresh-", "");
			var selectedUrl = $( 'tr#row-' + selectedRowNumber + ' > td.cell-url' ).html();
			$( 'tr#row-' + selectedRowNumber).addClass('active');
			console.log("clickedUrl: " + selectedUrl);
			selectedRows.push(selectedRowNumber);
			selectedUrls.push(selectedUrl);
		});
		console.log(selectedUrls);

		$.post(
			"ajax/ajax.php",
			{	'action'		:	"downloadAndProcess",
				'urls'			:	selectedUrls,
				'element-id'	:	$(this).attr('id'),
				'rows'			:	selectedRows,
				
			},
			function(result, status){
				var result = $.parseJSON(result);
				console.log(result);			
				// reset button
				$('#' + result['element-id']).button('reset');
/*
				// reset rows
				$.each(result.post.rows, function(rowNumber) {
					$( 'tr#row-' + rowNumber).removeClass('active').addClass('success');
				});
*/
				// update date
				$.each(result.package, function(data) {
					updateRowId = $( "tr:contains("+ data.url + ")" ).attr('id');
					console.log("...looping through package... data:");
					console.log(data);
					console.log("updateRowId: " + updateRowId);
					$('#' + updateRowId).removeClass('active').addClass('success');
					$('#' + updateRowId + ' > td.cell-date-retrieved' ).html(data.time);
					$('#' + updateRowId + ' > input.select-refresh' ).prop('unchecked');

				});
				// check url, size
			}
		);

		return;
	});
}

function bindRefreshRecordButtons() {
	console.log("in bindRefreshRecordButtons");
	$('.refresh-record').on('click', function () {
		var clickedButtonId = $(this).attr('id');
		var clickedRowNumber = clickedButtonId.replace("button-refresh-", "");
		var clickedUrl = $( "tr#row-" + clickedRowNumber + " > td.cell-url" ).html();

		$(this).button('loading');
		$('tr#row-' + clickedRowNumber).addClass('active');

		$.post(
			"ajax/ajax.php",
			{	'action'	:	"downloadAndProcess",
				'urls'		:	[clickedUrl],
				'element-id'	:	clickedButtonId,
			},
			function(result, status){
				var result = $.parseJSON(result);
				console.log(result);			
				var resetButtonId = result.post['element-id'];
				var updateRowNumber = resetButtonId.replace("button-refresh-", "");
				
				$('tr#row-' + updateRowNumber).removeClass('active').addClass('success');
				$('#cell-date-retrieved-' + updateRowNumber).html(result.package[0]['time']);
				$('#' + resetButtonId).button('reset');
			}
		);

	});
}