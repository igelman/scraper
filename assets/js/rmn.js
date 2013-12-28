function init() {
	addControls();
	bindLoadButton();
	bindNextButton();
	getSourceData();
	return;
}

function addControls() {
	var controls = '<form class="form-inline" role="form">'
+'  <div class="form-group">'
+'    <label class="sr-only" for="page-number">Page Number</label>'
+'    <input type="number" class="form-control" id="page-number" placeholder="Page number">'
+'  </div>'
+'  <div class="form-group">'
+'    <label class="sr-only" for="merchants-count">Number of Merchants</label>'
+'    <input type="number" class="form-control" id="merchants-count" placeholder="Merchants per page">'
+'  </div>'
+'  <button type="button" class="btn btn-default" id="load-button">Load</button>'
+'  <button type="button" class="btn btn-default" id="next-page-button">Next Page</button>'
+'</form>';
	$('#controls').html(controls);
	$('#controls').parent('.row').addClass('well');
	return;
}

function bindLoadButton() {
	$('#load-button').on('click', function () {
		getSourceData($('#page-number').val(), $('#merchants-count').val());
	});
	return;
}

function bindNextButton() {
	$('#next-page-button').on('click', function () {
		$('#page-number').val(parseInt($('#page-number').val()) + 1);
		getSourceData($('#page-number').val(), $('#merchants-count').val());
	});
	return;
	
}

function getSourceData(pageNumber, merchantsCount) {
	$('.btn').button('loading');
	$('#data').html("");
	var query = "";
	if (pageNumber && merchantsCount) {
		query = "?offset=" + (parseInt(pageNumber) - 1) + "&maxRecords=" + parseInt(merchantsCount);
		console.log(query);
	}
	ajaxUrl = "ajax/client-select-parsed-content-class.php" + query;
	$.get( ajaxUrl, function( data ) {
		console.log(data);
		makeSourceTable(data);
		bindDraftToWpButtons();
		$('.btn').button('reset');
	});
}

function makeSourceTable(data) {

	var columns = [
		"title",
		"details",
		"coupon",
		"use-data",
		"offer_id",
		"data_type",
		"merchant_domain",
		"coupon_score",
		"coupon_rank",
		"expires",
		"last_click",
		"comment_count",
		"vote_count",
		"success",
		"verified",
		"source_url",
		"date_retrieved",
	];
	var thead = "<thead><tr>";
	thead += "<th>Draft to tjd</th>";
	var i;
	$.each(columns, function(i, columnName){
		thead += "<th>" + columnName + "</th>";
	})
	thead += "</tr></thead>";

	data = $.parseJSON(data);
	var dataTable = "<table class='table table-hover table-condensed' id='data-table'>" + thead + "<tbody>";

	var dataRows = "";

	$.each(data, function(rowNumber, item) {
		var draftToWpButton = "<button type='button' data-loading-text='Loading...'  class='btn btn-default draft-to-wp' id='button-draft-" + rowNumber + "'><span class='glyphicon glyphicon-share'></span></button>";
		var row = "<tr class='item' id='row-" + rowNumber + "'>";
		row += makeTableCell(draftToWpButton, "", "");
		var i;
		$.each(columns, function(i, columnName){
			row += makeTableCell(item[columnName], "cell-" + columnName + "-" + rowNumber, "cell-" + columnName);
		})
		row += "</tr>";
		dataRows += row;
	})

	dataTable += dataRows;
	dataTable += "</tbody></table>";
	$('#data').html(dataTable);
	$('#data-table').dataTable({
        "bPaginate": false,
        "bLengthChange": false,
        "bAutoWidth": false
    });


}

function makeTableCell(cellContent, cellId, cellClass) {
	return "<td class='" + cellClass + "' id='" + cellId + "'>" + cellContent + "</td>";
}

function bindDraftToWpButtons() {
	$('.draft-to-wp').on('click', function () {
		console.log("Clicked draft");

		var clickedButtonId = $(this).attr('id');
		var clickedRowNumber = clickedButtonId.replace("button-draft-", "");
		
		console.log("clickedButtonId: " + clickedButtonId);
		console.log("clickedRowNumber: " + clickedRowNumber);
		
		$(this).button('loading');
		$('tr#row-' + clickedRowNumber).addClass('active');
		$.post(
			"ajax/ajax.php",
			{	'action'		: "postCouponToTjd",
				'element-id'	: clickedButtonId,
				'postTitle'		: $('#cell-title-' + clickedRowNumber).val(),
				'postContent'	: $('#cell-details-' + clickedRowNumber).val(),
				'couponCode'	: $('#cell-coupon-' + clickedRowNumber).val(),
				'couponExpires'	: $('#cell-expires-' + clickedRowNumber).val(),
				'couponUrl'		: "",
				'postOfferId'	: $('#cell-offer_id-' + clickedRowNumber).val(),
				'productTypes'	: [],
				'merchant'		: $('#cell-merchant_domain-' + clickedRowNumber).val(),
			},
			function(result, status){
				var result = $.parseJSON(result);
				console.log(result);
				$('#' + result.post['element-id']).button('reset');
				var resetButtonId = result.post['element-id'];
				var updateRowNumber = resetButtonId.replace("button-refresh-", "");
				$('tr#row-' + updateRowNumber).removeClass('active').addClass('success');
			}
		);

	});
}

function OLDmakeSourceTable() {
console.log("in makeSourceTable()");
	// Create a new YUI instance and populate it with the required modules.
	YUI().use('datatable-sort', function (Y) {
		// DataTable is available and ready for use. Add implementation
		// code here.
		// Columns must match data object property names

		$.get( "ajax/client-select-parsed-content-class.php", function( data ) {
			//console.log( data);
			data = $.parseJSON(data);
			
			var columns = new Array();
			$.each(data[0], function(key){
				columns.push(
					{'key': key, 'allowHTML': true, resizeable: true}
				);
			});

			
			var table = new Y.DataTable({
			    columns: columns,
			    data: data,
				sortable: true,
			    caption: "Caption",
			    summary: "Summary"
			});
			
			$('body').addClass('yui3-skin-sam');
			table.render("#data");
		});
		
	});
}