

function init() {
	console.log("called init");
	listAllUrls();
}

function bindSpanSetNumber() {
	$('.span-set-number').on('click', function() {
		var clickedId = $(this).attr('id');
		var clickedRowNumber = clickedId.replace("span-set-number-", "");
		var url = $( 'tr#row-' + clickedRowNumber + ' > td.cell-url > a' ).attr('href');
		var set = $('#' + clickedId).html();
		
		// replace with input
		$(this).replaceWith("<input type='text' id='set-input-" + clickedRowNumber + "' value='" + set + "'>");
		// call addToSet
		$('#set-input-' + clickedRowNumber).change(function() {
			var newSet = $(this).val();
			addToSet('set-input-' + clickedRowNumber, url, newSet);
		});
	});
}

function addToSet(elementId, url, set) {
	var ajaxUrl = "ajax/ajax.php?action=addToSet&url=" + encodeURIComponent(url) + "&set=" + parseInt(set) + "&elementId=" + elementId;
	$.get(
		ajaxUrl,
		function(data, textStatus, jqXHR) {
			var updateElementId = data.get.elementId;
			var newSet = data.get.set;
			var rowCount = data.rowCount;
			var updateRowNumber = updateElementId.replace("set-input-", "");
			if (rowCount==1) {
				$('#' + updateElementId).replaceWith("<span id='span-set-number-" + updateRowNumber + "' class='span-set-number'>" + newSet + "</span>");
				bindSpanSetNumber(); // this is a little heavy-handed, since we just need to rebind this one element
				$('tr#row-' + updateRowNumber).removeClass('active').addClass('success');
			}
		},
		"json" 
	);
}

function listAllUrls() {
	var request = $.ajax({
		type:	"POST",
		url:	"ajax/ajax.php",
		data:	{
			action:	"listAllUrls",
		},
		dataType:	"json",
	});
	request.done(function(data){
		makeSourceTable(data);
		addGetCommandButton();
		addQueueSetControls();
		bindRefreshSelectedButton();	// button refreshes all selected records
		bindRefreshRecordButtons();		// button refreshes current record
		bindRefreshCheckbox();			// checkbox to select record for refresh
		bindGetCommandButton();
		bindQueueSetButton();
		bindViewCouponsButtons();
		bindSpanSetNumber(); //bindAddToSetButtons();
	});
	request.fail(function( jqXHR, textStatus ) {
		console.log(jqXHR);
		console.log( "Request failed: " + textStatus );
	});
}

function addQueueSetControls() {
	var queueSetButton = "<button type='button' class='btn btn-default' id='queue-set'><span class='glyphicon glyphicon-heart'></span></button>";
	var queueSetInput = "<label for='queueSet'>Queue set</label><input type='number' class='form-control' id='queue-set-input' placeholder='queue set'>";
	var form = "<form class='form-inline' role='form'><div class='form-group'>" + queueSetInput + "</div>" + queueSetButton + "</form>";
	$('#queueSetControls').html(form);
	$('#queueSetInput').css({"width":"200px",});
	$('#queueSetControls').parent('.row').addClass('well');
	
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
	var refreshSelectedButton = "<button type='button' data-loading-text='Loading...'  class='btn btn-default' id='refresh-selected'><span class='glyphicon glyphicon-cloud-download'></span></button>";
	
	columns = [
		refreshSelectedButton,
		"Date Retrieved",
		"Set",
		"Url",
		"&nbsp;",
		"&nbsp;"
	];
	
	var thead = "<thead><tr>";
	thead += makeTheads(columns);
	thead += "</tr></thead>";
	
	var tfoot = "<tfoot><tr>";
	tfoot += makeTheads(columns);
	tfoot += "</tr></tfoot>";
	
	var dataTable = "<table class='table table-hover table-condensed' id='data-table'>" + thead + "<tbody>";
	var dataRows = "";
	$.each(data, function(rowNumber, item) {
		var viewCouponsButton = "<button type='button' data-loading-text='Loading...'  class='btn btn-default view-coupons' id='button-view-coupons-" + rowNumber + "'><span class='glyphicon glyphicon glyphicon-th-list'></span></button>";
		var refreshRecordButton = "<button type='button' data-loading-text='Loading...'  class='btn btn-default refresh-record' id='button-refresh-" + rowNumber + "'><span class='glyphicon glyphicon-cloud-download'></span></button>";
		var refreshCheckBox = "<label class='block'><input type='checkbox' class='select-refresh' id='select-refresh-" + rowNumber +"'></label>";
		var linkToUrl = "<a href='" + item.url + "' target='_blank'>" + item.url + " <span class='glyphicon glyphicon-new-window'></span></a>";
		var addToSetForm = "<span id='span-set-number-" + rowNumber + "' class='span-set-number'>" + item.set_number + "</span>";
		var row = "<tr class='item' id='row-" + rowNumber + "'>";

		row += makeTableCell(refreshCheckBox, "", "cell-checkbox-refresh");
		row += makeTableCell(item.date_retrieved, "cell-date-retrieved-" + rowNumber, "cell-date-retrieved");
		row += makeTableCell(addToSetForm, "cell-set-number-" + rowNumber, "cell-set-number");
		row += makeTableCell(linkToUrl, "cell-url-" + rowNumber, "cell-url");
		row += makeTableCell(refreshRecordButton, "", "cell-button-refresh");
		row += makeTableCell(viewCouponsButton, "", "cell-button-view-coupons");

		row += "</tr>";
		dataRows += row;
	})
	dataTable += dataRows;
	dataTable += tfoot;
	dataTable += "</tbody></table>";

// Insert table in div #data	
	$('#data').html(dataTable);
	
// Add jQuery dataTable functionality, including column filtering plug-in

	aoColumnsArray = [];
	$.each(columns, function( index, column ) {
		if (column == "Set") {
			aoColumnsArray.push({type:"text"});
		} else {
			aoColumnsArray.push(null);	
		}
	});
	console.log("aoColumnsArray...");
	console.log(aoColumnsArray);

	
	$('#data-table').dataTable({
        "bPaginate": false,
        "bLengthChange": false,
        "bAutoWidth": false
    }).columnFilter({ // plugin from jquery-datatables-column-filter.googlecode.com/svn/trunk/index.html
    	"sPlaceHolder": "head:after", 
    	"aoColumns": aoColumnsArray,
    });

	$('.block').css({
		"display"	:	"block",
		"text-align":	"center",
	});
	$( "<hr>" ).insertBefore( '#data-table' );
	$( "<hr>" ).insertAfter( '#data-table' );
	
}

/*

	columns = [
		refreshSelectedButton,
		"Date Retrieved",
		"Set",
		"Url",
		"&nbsp;",
		"&nbsp;"
	];

*/

function makeTheads(columns) {
	var row = "";
	$.each(columns, function( index, column ) {
		row += "<th>" + column + "</th>";
	});
	return row;
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


// This is goofy; skip the binding. Just make this a link when the row is created.
function bindViewCouponsButtons() {
	$('.view-coupons').each(function( index ) {
		var buttonId = $(this).attr('id');
		var rowNumber = buttonId.replace("button-view-coupons-", "");
		var rmnUrl = $( 'tr#row-' + rowNumber + ' > td.cell-url > a' ).attr('href');
		var urlParam = encodeURIComponent(rmnUrl);
		var href = "index.php?url=" + urlParam;
		
		$(this).replaceWith("<a href='" + href + "'>link</a>");
	});


	$('.view-coupons').on('click', function () {
		var clickedButtonId = $(this).attr('id');
		var clickedRowNumber = clickedButtonId.replace("view-coupons", "");
		var rmnUrl = $( 'tr#row-' + clickedRowNumber + ' > td.cell-url > a' ).attr('href');
		var urlParam = encodeURIComponent(rmnUrl);
		var href = "index.php?url=" + urlParam;		
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

function bindQueueSetButton() {
	$('#queue-set').on('click', function () {
		var queueSet = $('#queue-set-input').val();
		$.post(
			"ajax/ajax.php",
			{
				'action'	: "addSetToQueue",
				'set'		: queueSet,
			},
			function(result, status) {
				console.log("queueSet result:");
				console.log(result);
				$('#queueMessage').html(result);
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