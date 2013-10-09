

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
			addGetCommandButton();
			bindRefreshSelectedButton();	// button refreshes all selected records
			bindRefreshRecordButtons();		// button refreshes current record
			bindRefreshCheckbox();			// checkbox to select record for refresh
			bindGetCommandButton();
		}
	);
}

function addGetCommandButton() {
	var getCommandButton = "<button type='button' class='btn btn-default' id='get-command-for-selected'><span class='glyphicon glyphicon-heart'></span></button>";
	var sleepInput = "<label for='sleep'>Sleep between downloads (seconds)</label><input type='number' class='form-control' id='sleep-input' placeholder='sleep time'>";
	var form = "<form class='form-inline' role='form'><div class='form-group'>" + sleepInput + "</div>" + getCommandButton + "</form>";
	var lead = "<p class='lead'>To get more than a few URLs...</p><p>...select them from the list, enter sleep time, and get the command to enter in Terminal.app.</p>";
	$('#controls').html(lead + form);
	$('#sleep-input').css({"width":"200px",})
	$( '#controls' ).parent( '.row' ).addClass('well');
}

function makeSourceTable(data) {
	data = $.parseJSON(data);
	
	var refreshSelectedButton = "<button type='button' data-loading-text='Loading...'  class='btn btn-default' id='refresh-selected'><span class='glyphicon glyphicon-cloud-download'></span></button>";
	var thead = "<thead><tr><th>" + refreshSelectedButton + "</th><th>Date Retrieved</th><th>Set</th><th>Url</th><th> </th></tr></thead>";
	var dataTable = "<table class='table table-hover table-condensed' id='data-table'>" + thead + "<tbody>";
	var dataRows = "";
	$.each(data, function(rowNumber, item) {
		var refreshRecordButton = "<button type='button' data-loading-text='Loading...'  class='btn btn-default refresh-record' id='button-refresh-" + rowNumber + "'><span class='glyphicon glyphicon-cloud-download'></span></button>";
		var refreshCheckBox = "<label class='block'><input type='checkbox' class='select-refresh' id='select-refresh-" + rowNumber +"'></label>";
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
	$('.block').css({
		"display"	:	"block",
		"text-align":	"center",
	});
	$( "<hr>" ).insertBefore( '#data-table' );
	$( "<hr>" ).insertAfter( '#data-table' );
	
}


function makeTableCell(cellContent, cellId, cellClass) {
	return "<td class='" + cellClass + "' id='" + cellId + "'>" + cellContent + "</td>";
}

function bindRefreshCheckbox() {
	$('.select-refresh').on('click', function () {
		var selectedCheckboxes = $('.select-refresh:checked'); // set of selected checkboxes
		if ( $(this).prop('checked') && (selectedCheckboxes.length > 5)) {
			$('#refresh-selected').hide();
		}
		else if (selectedCheckboxes.length <= 5) {
			$('#refresh-selected').show();
		}
	});
}


function bindRefreshSelectedButton() {
	$('#refresh-selected').on('click', function() {
		$(this).button('loading');

		var selectedUrls = getSelectedUrls();
		if (selectedUrls.length > 5) { // don't do more than five, else we risk getting the IP blocked
			console.log("Risky to try so many");
			$(this).button('reset');
			return;
		}

		console.log("selectedUrls: " + selectedUrls);
		console.log("JSON selectedUrls: " + JSON.stringify(selectedUrls) );

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
				$('#' + result.post['element-id']).button('reset');

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

function bindGetCommandButton() {
	$('#get-command-for-selected').on('click', function () {
		var selectedUrls = getSelectedUrls();
		var sleep = $('#sleep-input').val();
		var command = "<pre>php cli-client.php " + sleep + " " + JSON.stringify(selectedUrls) + "</pre>";
		console.log(command);
		$('#finder').html(command);
	})
	
}

function getSelectedUrls() {
	var selectedCheckboxes = $('.select-refresh:checked'); // set of selected checkboxes
	var selectedUrls = new Array();
		$.each(selectedCheckboxes, function() {
			var selectedCheckboxId = $(this).attr('id');
			var selectedRowNumber = selectedCheckboxId.replace("select-refresh-", "");
			var selectedUrl = $( 'tr#row-' + selectedRowNumber + ' > td.cell-url > a' ).attr('href');
			$( 'tr#row-' + selectedRowNumber).addClass('active');
			//selectedRows.push(selectedRowNumber);
			selectedUrls.push(selectedUrl);
		});
		return selectedUrls;
}

function updateAffectedRow(rowNumber, package) {
	$('tr#row-' + rowNumber).removeClass('active').addClass('success');
	$('#cell-date-retrieved-' + rowNumber).html(package.time);
	$('#row-' + rowNumber + ' > input.select-refresh' ).prop('checked', false);
}

function generateResultMessage(package) {
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