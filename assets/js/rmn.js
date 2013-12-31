/**
* TO-DOS
*  Link DLP items to page
*  Debug the xmlrpc parameters. Some items don't make it to post ... maybe because of markup
*/


function init() {
	var urlParams = $.deparam.querystring( true );
	
	if (!urlParams.url) {
		urlParams = setDefaultUrlParams(urlParams);
		addPageControls(urlParams);
		//bindLoadButton();
		bindPrevPageButton();
		bindNextPageButton();
	} else if (urlParams.url) {
		addMerchantControls(urlParams);
	}
	bindLoadButton();
	getSourceData(urlParams);
	return;
}

function setDefaultUrlParams(urlParams) {
	urlParams.pageNumber = urlParams.pageNumber ? urlParams.pageNumber : 1;
	urlParams.merchantsCount = urlParams.merchantsCount ? urlParams.merchantsCount : 1;
	return(urlParams);
	
}

function getRmnUrlFromUrlQueryString() {
	var params = $.deparam.querystring( true );
	var rmnUrl = "";
	if (params.url) {
		rmnUrl = params.url;
	}
	return rmnUrl;
}

function addMerchantControls(urlParams) {
	var controls = '<form class="form-horizontal" role="form">'
		+'	<div class="row">'
		+'		<div class="col-sm-5">'
		+'			<div class="form-group">'
		+'				<label class="control-label" for="merchant-url">RMN Merchant Url</label>'
		+'				<input class="form-control" id="merchant-url" type="url" placeholder="RMN Merchant Url">'
		+'			</div>'
		+'		</div>'
		+'	</div> <!-- .row -->'
		+'  <button type="button" class="btn btn-default" id="load-button">Load</button>'
//		+'  <button type="button" class="btn btn-default" id="prev-page-button">Prev Page</button>'
//		+'  <button type="button" class="btn btn-default" id="next-page-button">Next Page</button>'
		+'</form>';
	$('#controls').html(controls);
	
	addTypeAhead();
	setInputVals(urlParams);
	return;
}

function addTypeAhead() {
	$.get(
		'ajax/ajax.php?action=fetchAllUrls',
		function(data, textStatus, jqXHR) {
			var result = [];
			for(var i in data) {
				result.push(data[i].url);
			}
			$('#merchant-url').typeahead([
				{
					name: 'urls',
					local: result,
				}
			]);			
		},
		"json" 
	);
}

function addPageControls(urlParams) {
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
		+'  <button type="button" class="btn btn-default" id="prev-page-button">Prev Page</button>'
		+'  <button type="button" class="btn btn-default" id="next-page-button">Next Page</button>'
		+'</form>';
	$('#controls').html(controls);
	setInputVals(urlParams/* urlParams.pageNumber, urlParams.merchantsCount */);
	
	$('#controls').parent('.row').addClass('well');
	return;
}

function setInputVals(urlParams/* pageNumber, merchantsCount */) {
	$('#page-number').val( urlParams.pageNumber ? urlParams.pageNumber : "" );
	$('#merchants-count').val( urlParams.merchantsCount ? urlParams.merchantsCount : "" );
	$('#merchant-url').val( urlParams.url ? urlParams.url : "");

}

function bindLoadButton() {
// Load the page requested in the form.
	$('#load-button').on('click', function () {
		var urlParams = {};
		urlParams.pageNumber = $('#page-number').val();
		urlParams.merchantsCount = $('#merchants-count').val();
		urlParams.url = $('#merchant-url').val();
console.log("bindLoadButton urlParams: ...");
console.log(urlParams);
		getSourceData(urlParams);
	});
	return;
}

function bindNextPageButton() {
// Get the next page of data.
	$('#next-page-button').on('click', function () {
		var currentPage = parseInt($('#page-number').val());
		var nextPage = currentPage + 1;
		$('#page-number').val(nextPage);
		var urlParams = {};
		urlParams.pageNumber = $('#page-number').val();
		urlParams.merchantsCount = $('#merchants-count').val();		
		getSourceData(urlParams);
	});
	return;
}

function bindPrevPageButton() {
// Get the prev page of data.
	$('#prev-page-button').on('click', function () {
		var currentPage = parseInt($('#page-number').val());
		var prevPage = currentPage - 1;
		if ( prevPage >= 1 ) {
			$('#page-number').val(prevPage);
			var urlParams = {};
			urlParams.pageNumber = prevPage; //$('#page-number').val();
			urlParams.merchantsCount = $('#merchants-count').val();
			getSourceData(urlParams);			
		}
	});
	return;
}


function updateUrl(urlParams) {
// Update the url with the new page number & merchants / page parameters.
//  (This allows the user to rely on the browser back button).
	var url = window.location.protocol + "//" + window.location.host + window.location.pathname;
	if ( urlParams.pageNumber && urlParams.merchantsCount ) {
		url += "?pageNumber=" + urlParams.pageNumber + "&merchantsCount=" + urlParams.merchantsCount
	} else if (urlParams.url) {
		url += "?url=" + urlParams.url;
	}
	history.pushState(null, null, url);
}

function getSourceData(urlParams) {
	updateUrl(urlParams);
	$('.btn').button('loading');
	$('#data').html("");
	var query = "";
	if (urlParams.pageNumber && urlParams.merchantsCount) {
		query = "?offset=" + parseInt(urlParams.merchantsCount)*(parseInt(urlParams.pageNumber) - 1) + "&maxRecords=" + parseInt(urlParams.merchantsCount);
	} else if (urlParams.url) {
		query = "?url=" + urlParams.url;
	}
	ajaxUrl = "ajax/ajax.php" + query;
	$.post(
		ajaxUrl,
		{action:	"listCoupons"},
		function( data ) {
			makeSourceTable(data);
			bindDraftToWpButtons();
			$('.btn').button('reset');
		},
		"json" 
	);
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
				//console.log(result);
				var postId = getPostInfoFromXmlResponse(result.response);
				var resetButtonId = result.post['element-id'];
				var updateRowNumber = resetButtonId.replace("button-refresh-", "");
				
				$('#' + resetButtonId).html("post " + postId);
				replaceButtonWithLinkToWp(resetButtonId, postId); // $('#' + resetButtonId).button('reset');
				$('tr#row-' + updateRowNumber).removeClass('active').addClass('success');
			}
		);
	});
}

function replaceButtonWithLinkToWp(buttonId, postId) {
	// After posting to WordPress, use button to open post in wp-admin
	$('#' + buttonId).replaceWith(
		"<a target='_blank' href='http://localhost/development/wordpress/wp-admin/post.php?post=" + $.trim(postId) + "&action=edit'>" + $.trim(postId) + "</a>"
	);
	
}


function getPostInfoFromXmlResponse(xmlResponse) {
	xmlDoc = $.parseXML( xmlResponse ),
	$xml = $( xmlDoc );
	return $xml.find( "value" ).text();
}