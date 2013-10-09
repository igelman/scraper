

function init() {
	console.log("called init");
	listAllUrls();
}

function listAllUrls() {
	console.log("called listAllUrls");
	$.post(
		"ajax/ajax.php",
		{	'action': "listAllUrls",
		},
		function(data, status){
			makeSourceTable(data);
			//$('#data-table').dataTable();
/*
			bindRefreshSelectedButton();	// button refreshes all selected records
			bindRefreshRecordButtons();		// button refreshes current record
			bindRefreshCheckbox();			// checkbox to select record for refresh
*/
		}
	);
}


function makeSourceTable(data) {
	data = $.parseJSON(data);
	
	var refreshSelectedButton = "<button type='button' data-loading-text='Loading...'  class='btn btn-default' id='refresh-selected'><span class='glyphicon glyphicon-repeat'></span></button>";
	var thead = "<thead><tr><th>" + refreshSelectedButton + "</th><th>Date Retrieved</th><th>Set</th><th>Url</th><th> </th></tr></thead>";
	var dataTable = "<table class='table table-hover table-condensed' id='data-table'>" + thead + "<tbody>";
	var dataRows = "";
	$.each(data, function(rowNumber, item) {
		var refreshRecordButton = "<button type='button' data-loading-text='Loading...'  class='btn btn-default refresh-record' id='button-refresh-" + rowNumber + "'><span class='glyphicon glyphicon-repeat'></span></button>";
		var refreshCheckBox = "<input type='checkbox' class='select-refresh' id='select-refresh-" + rowNumber +"'>";
		var linkToUrl = "<a href='" + item.url + "' target='_blank'>" + item.url + " <span class='glyphicon glyphicon-new-window'></span></a>";
		var row = "<tr class='item' id='row-" + rowNumber + "'>";
		row += makeTableCell(refreshCheckBox, "", "cell-checkbox-refresh");
		row += makeTableCell(item.date_retrieved, "cell-date-retrieved-" + rowNumber, "cell-date-retrieved");
		row += makeTableCell(item.set_number, "", "cell-set-number");
		row += makeTableCell(linkToUrl, "cell-url-" + rowNumber, "cell-url");
		row += makeTableCell(refreshRecordButton, "", "cell-button-refresh");
		row += "</tr>";
		dataRows += row;
	})
	dataTable += dataRows;
	dataTable += "</tbody></table>";
	$('#data').html(dataTable);
	
}


function makeTableCell(cellContent, cellId, cellClass) {
	return "<td class='" + cellClass + "' id='" + cellId + "'>" + cellContent + "</td>";
}

function bindRefreshCheckbox() {
	$('.select-refresh').on('click', function () {
		if ( $(this).prop('checked') ) {
			var selectedCheckboxes = $('.select-refresh:checked'); // set of selected checkboxes
			if (selectedCheckboxes.length > 5) { // don't do more than five, else we risk getting the IP blocked
				alert("Risky to try so many");
				$(this).prop('checked', false);
			}
		}
	});
	return;
}


function bindRefreshSelectedButton() {
	$('#refresh-selected').on('click', function() {
		$(this).button('loading');

		var selectedCheckboxes = $('.select-refresh:checked'); // set of selected checkboxes
		if (selectedCheckboxes.length > 5) { // don't do more than five, else we risk getting the IP blocked
			console.log("Risky to try so many");
			$(this).button('reset');
			return;
		}

		var selectedRows = new Array();
		var selectedUrls = new Array();
		$.each(selectedCheckboxes, function() {
			var selectedCheckboxId = $(this).attr('id');
			var selectedRowNumber = selectedCheckboxId.replace("select-refresh-", "");
			var selectedUrl = $( 'tr#row-' + selectedRowNumber + ' > td.cell-url > a' ).attr('href');
			$( 'tr#row-' + selectedRowNumber).addClass('active');
			//selectedRows.push(selectedRowNumber);
			selectedUrls.push(selectedUrl);
		});
		console.log(selectedUrls);

		$.post(
			"ajax/ajax.php",
			{	'action'		:	"downloadAndProcess",
				'urls'			:	selectedUrls,
				'element-id'	:	$(this).attr('id'),
				//'rows'			:	selectedRows,
			},
			function(result, status){
				var result = $.parseJSON(result);
				console.log(result);
				$('#' + result['element-id']).button('reset');

				$.each(result.package, function(i, data) {
					
					appendMessageToFixedHeader(generateResultMessage(data));
					var url = data.CURLINFO_EFFECTIVE_URL;
					var updateRowId = $( "tr:contains(" + url + ")" ).attr('id'); // What if this doesn't exist? Do a check to make sure it matches one of the urls we sent, and that the size of the download is largish.					
					var updateRowNumber = updateRowId.replace("row-", "");
					updateAffectedRow(updateRowNumber, data);

				});
			}
		);
		return;
	});
}

function bindRefreshRecordButtons() {
	$('.refresh-record').on('click', function () {
		var clickedButtonId = $(this).attr('id');
		var clickedRowNumber = clickedButtonId.replace("button-refresh-", "");
		var clickedUrl = $( 'tr#row-' + clickedRowNumber + ' > td.cell-url > a' ).attr('href');

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
				$('#' + result.post['element-id']).button('reset');

				appendMessageToFixedHeader(generateResultMessage(result.package[0]));
				var resetButtonId = result.post['element-id'];
				var updateRowNumber = resetButtonId.replace("button-refresh-", "");
				updateAffectedRow(updateRowNumber, result.package[0]);				
				
			}
		);
	});
}

function updateAffectedRow(rowNumber, package) {
	$('tr#row-' + rowNumber).removeClass('active').addClass('success');
	$('#cell-date-retrieved-' + rowNumber).html(package.time);
	$('#row-' + rowNumber + ' > input.select-refresh' ).prop('checked', false);
console.log("clearing checkmark in " + '#row-' + rowNumber + ' > input.select-refresh');
}

function generateResultMessage(package) {
console.log("generateResultMessage package:");
console.log(package);
	var display = "";
	var time = package.time;
	var effectiveUrl = package.CURLINFO_EFFECTIVE_URL;
	var size = package.size;
	var message = package.message;
	
	display += "<div>";
	display += "<p>" + message + "</p>";
	display += "<ul>";
	display += "<li>Effective URL: " + effectiveUrl + "</li>";
	display += "<li>Download size: " + size + "</li>";
	display += "</ul>";
	display += "</div>";

	return display;
}

function appendMessageToFixedHeader(htmlContent) {

	var divId = "#fixed-message-window";
	if ($(divId).length != 0) {
		var navbarHeight = 70;
		var divHeight = 70;
		var bodyPadding = navbarHeight + divHeight;
		
		var divCss = {
			"position"		:	"fixed",
			"overflow"		:	"auto",
			"top"			:	navbarHeight + "px",
			"height"		:	divHeight + "px",
			"width"			:	"100%",
			"z-index"		:	10,
			"background-color"	:	"white",
			"opacity"		:	1,
		};
		
		var bodyCss = {
			"padding-top"	:	bodyPadding + "px",
		};
		
		$(divId).insertAfter('nav#navbar');
		$(divId).css( divCss );
		//$(divId).html( divContent );
		$('body').css( bodyCss );
	}
	
	$(divId).append("<div class='col-md-8'>" + htmlContent + "</div>");
	
}