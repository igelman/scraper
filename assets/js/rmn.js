function init() {
	var pagination = getPaginationFromUrlQueryString();
	addControls(pagination);
	bindLoadButton();
	bindNextPageButton();
	getSourceData(pagination);
	return;
}

function getPaginationFromUrlQueryString() {
// Returns object {"pageNumber":int, "merchantsCount":int}
//  (or an empty object if the url doesn't have the parameters)
	var params = $.deparam.querystring( true );
	var pagination = {};
	if (params.pageNumber) {
		pagination['pageNumber'] = params.pageNumber;
	}
	if (params.merchantsCount) {
		pagination['merchantsCount'] = params.merchantsCount;
	}
	return(pagination);
}

function addControls(pagination) {
// Add form controls to set page number and merchants per page
	var controls = '<form class="form-horizontal" role="form">'
		+'	<div class="row">'
		+'		<div class="col-sm-5">'
		+'			<div class="form-group">'
		+'				<label class="control-label" for="page-number">Page Number</label>'
		+'				<input class="form-control" id="page-number" type="number" placeholder="Page number" min="1">'
		+'			</div>'
		+'		</div>'
		+'		<div class="col-sm-5">'
		+'			<div class="form-group">'
		+'				<label class="control-label" for="merchants-count">Number of Merchants</label>'
		+'				<input class="form-control" id="merchants-count" type="number" placeholder="Merchants per page" min="1">'
		+'			</div>'
		+'		</div>'
		+'	</div> <!-- .row -->'
		+'  <button type="button" class="btn btn-default" id="load-button">Load</button>'
		+'  <button type="button" class="btn btn-default" id="next-page-button">Next Page</button>'
		+'</form>';
	$('#controls').html(controls);

	$('#page-number').val( pagination.pageNumber ? pagination.pageNumber: "" );
	$('#merchants-count').val( pagination.merchantsCount ? pagination.merchantsCount: "" );
	
	$('#controls').parent('.row').addClass('well');
	return;
}

function bindLoadButton() {
// Load the page requested in the form.
	$('#load-button').on('click', function () {
		var pagination = {};
		pagination.pageNumber = $('#page-number').val();
		pagination.merchantsCount = $('#merchants-count').val();
		getSourceData(pagination);
	});
	return;
}

function bindNextPageButton() {
// Get the next page of data.
	$('#next-page-button').on('click', function () {
		$('#page-number').val(parseInt($('#page-number').val()) + 1);
		var pagination = {};
		pagination.pageNumber = $('#page-number').val();
		pagination.merchantsCount = $('#merchants-count').val();		
		getSourceData(pagination);
	});
	return;
}

function updateUrl(pagination) {
// Update the url with the new page number & merchants / page parameters.
//  (This allows the user to rely on the browser back button).
	var url = window.location.protocol + "//" + window.location.host + window.location.pathname;
	if ( pagination.pageNumber && pagination.merchantsCount ) {
		url += "?pageNumber=" + pagination.pageNumber + "&merchantsCount=" + pagination.merchantsCount
	}
	history.pushState(null, null, url);
}

function getSourceData(pagination) {
	updateUrl(pagination);
	$('.btn').button('loading');
	$('#data').html("");
	var query = "";
	if (pagination.pageNumber && pagination.merchantsCount) {
		query = "?offset=" + (parseInt(pagination.pageNumber) - 1) + "&maxRecords=" + parseInt(pagination.merchantsCount);
	}
	ajaxUrl = "ajax/client-select-parsed-content-class.php" + query;
	$.get( ajaxUrl, function( data ) {
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
		
		$(this).button('loading');
		$('tr#row-' + clickedRowNumber).addClass('active');
		$.post(
			"ajax/ajax.php",
			{	'action'		: "postCouponToTjd",
				'element-id'	: clickedButtonId,
				'postTitle'		: $('#cell-title-' + clickedRowNumber).html(),
				'postContent'	: $('#cell-details-' + clickedRowNumber).html(),
				'couponCode'	: $('#cell-coupon-' + clickedRowNumber).html(),
				'couponExpires'	: $('#cell-expires-' + clickedRowNumber).html(),
				'couponUrl'		: "",
				'postOfferId'	: $('#cell-offer_id-' + clickedRowNumber).html(),
				'productTypes'	: [],
				'merchant'		: $('#cell-merchant_domain-' + clickedRowNumber).html(),
			},
			function(result, status){
				result = $.parseJSON(result);
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