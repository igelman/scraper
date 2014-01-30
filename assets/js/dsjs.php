function init() {
	addControls(); // add radios, (hidden) file inputs, divs for buttons
	sourceSelect(); // attach action to radio select
	kcfinderInput("dealnews");
	kcfinderInput("rmn");
}

function addControls() {
	// Add radio and text (file) input controls
	var form = "";
	form += "<form name='source-select-form'>";
	
	// Radio names: 'source-radios'
	radios_name = "source-radios";
	radios_id_base = "source-radio-";
	
	// option 1: use dealnews.com
	//	value: 'remote_dealnews'
	id_remote_dealnews = radios_id_base + "dealnews_remote";
	value_remote_dealnews = "dealnews_remote";
	selected_remote_dealnews = "";
	label_remote_dealnews = "remote (dealnews.com)";
	dealnews_remote_radio = makeRadio(radios_name, id_remote_dealnews, value_remote_dealnews, selected_remote_dealnews, label_remote_dealnews);

	// option 2: use saved dealnews source
	//	When selected, displays the text input
	//	value: dealnews_file
	id_file_dealnews = radios_id_base + "dealnews_file";
	value_file_dealnews = "dealnews_file";
	selected_file_dealnews = "";
	label_file_dealnews = "file (dealnews source)";
	dealnews_file_radio = makeRadio(radios_name, id_file_dealnews, value_file_dealnews, selected_file_dealnews, label_file_dealnews);
	dealnews_file_input = "<div id='file-input-div-rmn'>" + makeFileInput("dealnews") + "</div>";

	// option 3: use saved rmn source
	//	When selected, displays the text input
	//	value: rmn_file
	rmn_file_radio = "";
	id_file_rmn = radios_id_base + "rmn_file";
	value_file_rmn = "rmn_file";
	selected_file_rmn = "";
	label_file_rmn = "file (retailmenot source)";
	rmn_file_radio = makeRadio(radios_name, id_file_rmn, value_file_rmn, selected_file_rmn, label_file_rmn);
	rmn_file_input = "<div id='file-input-div-rmn'>" + makeFileInput("rmn") + "</div>";

	form += dealnews_remote_radio;
	form += dealnews_file_radio + dealnews_file_input;
	form += rmn_file_radio + rmn_file_input;

	form += "</form>";

	var parse_button_div = "<div id='parse-button-div'></div>";	
	$('#controls').html(form + parse_button_div);
	$('[id^="kcfinder-input-"]').css('display', 'none');
	
	
	var kcfinder_div = "<div id='kcfinder-div'></div>";
	$('#finder').html(kcfinder_div);
	$('#kcfinder-div').css({	    'display': 'none',	    'position': 'absolute',	    'width': '670px',	    'height': '400px',	    'background': '#e0dfde',	    'border': '2px solid #3687e2',	    'border-radius': '6px',	    '-moz-border-radius': '6px',	    '-webkit-border-radius': '6px',	    'padding': '1px',	})

}

function makeRadio( name, id, value, selected, label ) {

	var checked = "";
	if (selected) {
		checked = "checked";
	}
	var radio = "<label class='radio'>";
	radio += "<input type='radio' name='" + name + "' id='" + id + "' value='" + value + "'" + checked + ">";
	radio += label;
	radio += "</label>";
	
	return radio;
} // makeRadio()

function makeFileInput(source) {
	file_input = "<input id='kcfinder-input-" + source + "' type='text' class='uneditable-input' placeholder='Click here to browse the server (" + source + " only)' style='width:600px;cursor:pointer' />"; // http://kcfinder.sunhater.com/demos/iframe
	return file_input;
} // makeFileInput()

function sourceSelect() {
// bind action to radio click.
// Reveal file input if user clicks file option 	
	$("[name='source-radios']").click(function () {
		source_option = $("[name='source-radios']:checked").val(); // file or remote
		console.log("called sourceSelect. source_option: " + source_option);
		
		$('[id^="kcfinder-input-"]').hide(250);
		$('#parse-button-div').hide(250);
		window.KCFinder = null;
		$('#kcfinder-div').css('display','none');
		$('#kcfinder-div').html();
		$('#filters').html("");
		$('#output').html("");
		$('#export').html("");
		
		if (source_option == "dealnews_file") {
			$('#kcfinder-input-dealnews').show(250);
		} else if (source_option == "rmn_file") {
			$('#kcfinder-input-rmn').show(250);
		} else if (source_option == "dealnews_remote" ) {
			addParseButton('dealnews');
		}

	})
	return;

} // sourceSelect()

function kcfinderInput(source) {
// bind action to textinput to open KCFinder browser in iframe 
// Return selected file as textinput's val
// http://kcfinder.sunhater.com/demos/iframe

	var kcfinder_browse_file = "../assets/kcfinder/browse.php";
	var upload_dir = "assets/stored-content";
	
	$('#kcfinder-input-' + source).on('click', function () {
		var input_id = $(this).attr("id");
		var kcfinder_div = $('#kcfinder-div');

		if (kcfinder_div.css('display') == "block") {
		// if the kcfinder div is already open, close it & return
			//console.log(kcfinder_div);
			//console.log(" display != none, so hiding");
			window.KCFinder = null;
			kcfinder_div.css('display','none');
			kcfinder_div.html('');
			return;
		}

		window.KCFinder = {
			callBack: function(url) {
			// after selecting a file, send the file path to the textinput & close the div
				window.KCFinder = null;
				$('#' + input_id).val(url);
				kcfinder_div.css('display','none');
				kcfinder_div.html('');
				addParseButton(source);
			}
		};

		// add the iframe in the kcfinder div and display the div
		kcfinder_div.html("<iframe name='kcfinder_iframe' src='" + kcfinder_browse_file + "?type=files&dir=" + upload_dir + "' frameborder='0' width='100%' height='100%' marginwidth='0' marginheight='0' scrolling='no' />");
		kcfinder_div.css('display','block');
	})
}

function addParseButton(source) {
	var parse_dealnews_button = "<button id='parse-button-dealnews' data-loading-text='loading...'>Parse dealnews</button>";
	var parse_retailmenot_button = "<button id='parse-button-retailmenot' data-loading-text='loading...'>Parse retailmenot</button>";
	
	$('#parse-button-div').html(""); // remove whatever parse button exists
	if (source == 'dealnews') { // add the appropriate parse button
		$('#parse-button-div').html(parse_dealnews_button);
		$('#parse-button-div').show(250);
	} else if (source == 'rmn') {
		$('#parse-button-div').html(parse_retailmenot_button);
		$('#parse-button-div').show(250);
	}
	console.log("about to call parseButton");
	parseButton(source); // attach action to button click

} // addParseButton(source)

function parseButton(source) {
// bind action to parsebutton

	$('button[id^="parse-button"]').on('click', function () {

		console.log("clicked parse-button. source: " + source);
		$('button[id^="parse-button"]').button('loading'); // change button state

		// Figure out the data source (file vs. remote) and type (rmn vs. dealnews)
		source_option = $("[name='source-radios']:checked").val(); // file or remote
		console.log("source_option: " + source_option);
		if (source_option == "rmn_file") {
			file_path = $('#kcfinder-input-rmn').val();
			file_source =  source_root + file_path ;
			deal_source = "rmn";
		} else if (source_option == "dealnews_file") {
			file_path = $('#kcfinder-input-dealnews').val();
			file_source =  source_root + file_path ;
			deal_source = "dealnews";
		} else if (source_option == "dealnews_remote") {
			file_source = "http://dealnews.com";
			deal_source = "dealnews";
		}
		console.log("file_source: " + file_source + " deal_source: " + deal_source);

/* 		url = ajax_path + "?function=DealnewsParse&dealSource=" + deal_source + "&source=" + encodeURIComponent(file_source); */
		url = "ajax/ajax-doublestuff.php?function=Parse&dealSource=" + deal_source + "&source=" + encodeURIComponent(file_source);
		$.ajax({
			url: url,
			type: "GET",
			dataType: "json",
			contentType: 'application/json',
			success: function(data){
				$('button[id^="parse-button"]').remove();
				console.log("success parseButton(" + source + ") " + " Status: " + data.status + " Data: " + data.output );
				console.log(data);
				insertContent(deal_source, data);
				bindDraftToWpButtons();
				//exportContent(deal_source, data.items);
			},
			error: function(data){
				console.log("fail parseButton(" + source + ") " + " Status: " + data.status + " Data: " + data.output );
			}
		});
	})
}

function exportContent(deal_source, items) {
// create excel listing of deal items
	console.log("called exportContent");

	rel_path = "assets/stored-content/export/export-" + $.now() + ".xls";
	path = doc_root + "/" + rel_path;

	// create column headings
	string = "<table><thead><tr>";
	$.each(items[0], function(key, value){
		string += "<th>" + key + "</th>";	
	})
	string += "</tr></thead><tbody>";

	// create row for each deal
	$.each(items, function(i, item) {
		//console.log("items loop iteration i: " + i);
		string += "<tr>";
		$.each(item, function(j, field) {
			//console.log("item loop iteration j: " + j);
			string += "<td>";
			string += field;
			string += "</td>";
		})
		string += "</tr>";
	})
	string += "</tbody></table>";

	// write table to file
	mode = "w";
	writeToFile (string, path, mode);
	// insert link to rel_path div before #outpout
	link = "<div><a href='" + rel_path + "' target='_blank'>" + rel_path + "</a></div>";
	$('#export').html(link);
	return;
}

function insertContent(deal_source, data){
// check the deal_source (rmn vs dealnews), and call the appropriate front-end functions to transform json to html. That's really just the filter controls.

	console.log("Called insertConent");
	if (deal_source == "dealnews") {
	$('#filters').html( constructHotnessMenu(data.hotness_menu_items) );
		hotnessMenu(); // bind action to hotness menu
		$('#output').html( showDeals(data.items) );
	} else if (deal_source == "rmn") {
		console.log("deal_source==rmn . About to showDeals...");
		$('#output').html( showDeals(data.items) );
//		$('#output').html( JSON.stringify(showDeals(data.items)) );
	}
	return;
} // insertContent(deal_source, data)

function constructHotnessMenu(hotness_menu_items) {
	var hotness_menu = "";
	$.each(hotness_menu_items, function(key, value) {
		hotness_menu += "<label class='checkbox'><input id='" + key + "' class='hotness-check' checked='checked' type='checkbox' value='" + key + "'>" + value + "</label>";
	});
	return hotness_menu;
} // constructHotnessMenu(hotness_menu_items)

function hotnessMenu(){
	console.log("called hotnessMenu");

	$('.hotness-check').on('click', function () {
		console.log( $(this).val() + " is clicked" );
		$('.hotness-check').each(function(){
			console.log( "Looping through each function: " + $(this).val() );
			var currentId = $(this).val();
			console.log( "currentId: " + currentId );
			console.log("checked: " + $('#' + currentId + ':checked') + " ...");
			console.log( $('#' + currentId).prop('checked') );
			if ( $('#' + currentId).prop('checked') ) {
				$('tr.' + $(this).val()).fadeIn();
				console.log("show " + $(this).val());
			} else {
				$('tr.' + $(this).val()).fadeOut();
				console.log("hide " + $(this).val());
			}
		})
	})
} // hotnessMenu()

function showDeals(items) {
// construct mark-up for each deal item
	$('#output').html("");
	var content = "<table class='table table-hover'><thead></thead><tbody>";
	$.each(items, function(i, item){
	
		var link_list = "<ul>";
		$.each(item.links, function(j, link) {
			link_list += "<li><a target='_blank' href='" + link + "'><i class='icon-share-alt'></i></a><span id='item-" + i + "-old-link-" + j + "' class='item-" + i + "-old-link'>" + link + "</span><span id='item-" + i + "-short-link-" + j + "'></span></li>";
		})
		link_list += "</ul>";

		hotness_class = "";
		hotness = "";
		if(typeof item.hotness != 'undefined') {
			hotness_class = item.hotness_class;
			hotness = item.hotness;
		}
		
		tags = "";
		if(typeof item.tags != 'undefined') {
			tags = "<ul>";
			$.each(item.tags, function(index, value) {
				tags += "<li>" +  value + "</li>";
			});
			tags += "</ul>";
		}
		
		deal_type = "";
		if(typeof item.deal_type != 'undefined') {
			deal_type = item.deal_type;
		}
		
		expires = "";
		if(typeof item.expires != 'undefined') {
			expires = item.expires;
		}
		
		last_click = "";
		if(typeof item.last_click != 'undefined') {
			last_click = item.last_click;
		}
		
		num_clicks_today = "";
		if(typeof item.num_clicks_today != 'undefined') {
			num_clicks_today = item.num_clicks_today;
		}
		
		comment_count = "";
		if(typeof item.comment_count != 'undefined') {
			comment_count = item.comment_count;
		}
		
		var editButton = "<button id='button-item-" + i + "' class='btn btn-mini' onclick='editItem(" + i + ")' type='button'>" + i + "</button>";
		var draftToWpButton = "<button type='button' data-loading-text='Loading...'  class='btn btn-default draft-to-wp' id='button-draft-" + i + "'><span class='glyphicon glyphicon-share'></span></button>";
		
		content += "<tr id='item-" + i + "' class='" + hotness_class + "'>";
		content += "<td>" + editButton + "<br>" + draftToWpButton + "</td>";
		//content += "<td><button id='button-item-" + i + "' class='btn btn-mini' onclick='editItem(" + i + ")' type='button'>" + i + "</button></td>";
		content += "<td><div>" + hotness + "</div><div><a><img id='primary-image-" + i + "' src='" + item.primary_image + "'></a></div></td>";
		content += "<td><div id='content-" + i + "'><div id='item-title-" + i + "'>" + item.title + "</div><div id='item-details-" + i + "'>" + item.details + "</div></div><div id='links-" + i + "'>" + link_list + "</div></td>";
		content += "<td><ul>";
		content += "<li>Deal Type: " + deal_type + "</li>";
		content += "<li>Merchant: " + item.merchant + "</li>";
		content += "<li>Domain: " + item.merchant_domain + "</li>";
		content += "<li>Coupon Code: " + item.coupon + "</li>";
		//content += "<li>Offer Status: " + item.offer_status + "</li>";
		content += "<li>Expires: " + item.expires + "</li>";
		content += "<li>Last Click: " + item.last_click + "</li>";
		content += "<li>Number of Clicks Today: " + item.num_clicks_today + "</li>";
		content += "<li>Staff Pick: " + item.staff_pick + "</li>";
		content += "<li>Vote Count: " + item.vote_count + "</li>";
		content += "<li>Comment Count: " + item.comment_count + "</li>";
		content += "<li>Tags: " + tags + "</li>";
		content += "</ul></td>";
		content += "</tr>";
	})
	content += "</tbody><tfoot></tfoot></table>";
//	$('#output').html(content); //.append(content);
	return content;
} // showDeals(items)


function bindDraftToWpButtons() {
	$('.draft-to-wp').on('click', function () {
		var clickedButtonId = $(this).attr('id');
		var clickedRowNumber = clickedButtonId.replace("button-draft-", "");
		console.log("clickedButtonId: " + clickedButtonId);
		console.log("clickedRowNumber: " + clickedRowNumber);
		$(this).button('loading');
		$('tr#item-' + clickedRowNumber).addClass('active');
		$.post(
			"ajax/ajax.php",
			{	'action'		: "postToTjd",
				'element-id'	: clickedButtonId,
				'postType'		: "tmt-deal-posts",
				'postTitle'		: $('#item-title-' + clickedRowNumber).html(),
				'postContent'	: $('#item-details-' + clickedRowNumber).html(),
/*				'couponCode'	: $('#cell-coupon-' + clickedRowNumber).html(),
				'couponExpires'	: $('#cell-expires-' + clickedRowNumber).html(),
				'couponUrl'		: "",
				'postOfferId'	: $('#cell-offer_id-' + clickedRowNumber).html(),
				'productTypes'	: "", //JSON.stringify(["product1", "product2"]),
				'merchant'		: $('#cell-merchant_domain-' + clickedRowNumber).html(),
*/
			},
			function(result, status){
				result = $.parseJSON(result);
				//console.log(result);
				var resetButtonId = result.post['element-id'];
				var updateRowNumber = resetButtonId.replace("button-refresh-", "");				
				
				if ( getPostInfoFromXmlResponse(result.response) ) {
					var postId = getPostInfoFromXmlResponse(result.response);
					$('#' + resetButtonId).html("post " + postId);
					replaceButtonWithLinkToWp(resetButtonId, postId); // $('#' + resetButtonId).button('reset');
					$('tr#row-' + updateRowNumber).removeClass('active').addClass('success');					
				} else {
					$('#' + resetButtonId).replaceWith("fail");
				}
			}
		);
	});//
}

function getPostInfoFromXmlResponse(xmlResponse) {
	xmlDoc = $.parseXML( xmlResponse ),
	$xml = $( xmlDoc );
	return $xml.find( "value" ).text();
}

function editItem(id) {
	console.log("Called editItem id "+ id);

	// toggle the button
	$('button#button-item-' + id).replaceWith("<button id='button-item-" + id + "' class='btn btn-mini btn-inverse' onclick='endEditItem(" + id + ")' type='button'>" + id + "</button>");

	// copy image to server

	copyImage("primary-image-" + id);
	
	// place content in a CKEditor
	contentId = "content-" + id;
	var editor = addEditor(contentId);

	// skimify links
	$(".item-" + id + "-old-link").each(function(index, value){
		// for each old-link, find the endPoint and put that in a textarea for editing before skimifying

		link = $(this).html(); // the original link
		newLinkElement = "item-" + id + "-new-link-" + index; // element Id where we will put the endpoint
		newLinkElementContent = "<textarea id=\"" + newLinkElement + "\" class=\"input-block-level\" cols=\"150\" name=\"" + newLinkElement + "\"></textarea>"; // replace the element with a textarea field
		$(this).after(newLinkElementContent);
		$(this).hide();
		getEndpoint(link, newLinkElement); // get the endpoint and put it in the textarea. And add a skimify button.
	});
	
	// add replaceLinks button
	$("#links-" + id).after("<button class='btn btn-mini' onclick='replaceLinks(\"" + id + "\")' type='button'>Replace</button>"); // it would be better to hold off on this button until the skimified links are returned, but that's tricky isn't it.
	
	// finish edit (replace links, remove editor)


	return;
} // editItem(id)

function copyImage(id) {
// Grab the remote image #id and copy it to tipjardaily.com/images/scrape
// And replace the image in the scrape tool
// Uses ajax call to ajax.php?function=CopyImage&url=[remote img source]&filename=[path/to/hosted/image]
	console.log("Called copyImage id "+ id );
	url = $("#" + id).attr('src');
	var purl = $.url(url); // use URL-Parser plugin to parse url
	// basename = purl.attr('file'); // user URL-Parser plugin to get basename (e.g., image.png)
	basename = purl.segment(-1); // attr('file') breaks if the file doesn't have an extension (e.g., ".jpg")
	//imagesDirname = "/Volumes/Macintosh HD/Library/Server/Web/Data/Sites/Default/development/scraper/doublestuff/assets/stored-content/images/";
	imagesDirname = "assets/stored-content/images/";
	imagesUrlDirname = "assets/stored-content/images/";
	
	path = imagesDirname + basename;

	console.log("purl object: " + JSON.stringify(purl));
	console.log("basename (purl.attr('file'): " + basename);
	console.log("imagespath: " + imagesDirname);
	console.log("path (imagespath + basename): " + path);

		url = "ajax/ajax-doublestuff.php?function=CopyImage&url=" + encodeURIComponent(url) + "&path=" + encodeURIComponent(path);
	$.ajax({
		url: url,
		type: "GET",
		dataType: "json",
		contentType: 'application/json',
		success: function(data){
			console.log("success copyImage(" + id + ") " + " " + data.status + " filename: " + data.path );
			console.log(data);
			$('#' + id).attr('src', imagesUrlDirname + data.basename);
			$('#' + id).after("<div>" + imagesUrlDirname + data.basename + "</div>");
			//<div id=src-primary-image-" + i + "'></div>
		},
		error: function(data){
			console.log("fail copyImage(" + id + ") " + " " + data.status );
		}
	});
	return;
} // copyImage(id)

function inputCkeditor (inputId) {
// replaces the CKEDITOR.replace method, so that I can globally specify attributes for the editor
	var editor = CKEDITOR.replace( inputId, {	
	} );
	
	return editor;
} // inputCkeditor returns editor object

function addEditor(id) {
// Replaces #id with a CKEditor input, with the content intact

	console.log("Called addEditor(" + id + ")");

	// replace the content div with a CKEditor control
	var editor = inputCkeditor (id);
	return editor;
} // addEditor(id) returns editor object

function getEndpoint(link, elementId) {
// Use ajax to get the endpoint, then write it into id elementId 
	console.log( "Called getEndpoint()" );

	// Use ajax to get the endpoint, then write it into id elementId
	console.log("Using .ajax for findEndpoint link:" + link + " element: " + elementId );
	$.ajax({
		url: "ajax/ajax-doublestuff.php?function=findEndpoint&url=" + link + "&i=" + elementId,
		type: "GET",
		dataType: "json",
		contentType: 'application/json',
		success: function(data){
			$("#" + data.i).html(data.endpoint);
			$("#" + data.i).after("<button id='skimify-button-" + data.i + "' class='btn btn-mini' data-loading-text='skimming...' onclick='skimify(\"" + data.i + "\")' type='button'>Skimify</button>")
			console.log("success findEndpoint " + " " + JSON.stringify(data)  + "<br>$(#" + data.i + ").html(" + data.endpoint + ")" );
		},
		error: function(data){
			console.log("error findEndpoint() " + " " + JSON.stringify(data) );
		}
	});
	
} // getEndpoint

function skimify(id) {
// Grab URL from element #id
// Skimify it using SkimLinkShortener API
// Display the shortened link just for kicks
// Replace the original link in the content
//
	console.log( "called skimify(" + id + ")");
	var elem = id;
	var shortElem = id.replace("new","short");
	
	console.log( "skimify on #" + elem );

// set id.button('loading')
// then reset the button in the ajax callback		
// id = item-3-new-link-0  button id is skimify-button-item-3-new-link-0
	$('#skimify-button-' + id).button('loading');
	
	longUrl = $("#" + elem).val(); // input field's original content
	console.log( "longUrl: " + longUrl );


	$.ajax({
		url: "ajax/ajax-doublestuff.php?function=SkimLinkShortener&user=25078X843312&url=" + encodeURIComponent(longUrl),
		type: "GET",
		dataType: "json",
		contentType: 'application/json',
		success: function(data){
			// instead of inserting a new element, I should just update the elements value. That way the skimify button can be reused
//			$("#" + elem).after("<span id=" + shortElem + ">" + data.shorturl + "</span>");
			$('span#' + shortElem).html( data.shorturl );
			$('#skimify-button-' + id).button('reset'); //remove();
			console.log( id + "success skimify() " + " " + data.shorturl );
		},
		error: function(data){
			console.log("skimify fail status: " + data.status + "message: " + data.error );
		}
	});
}

function replaceLinks(id){
	// replace the original link in content with the skimified link
	// for each .item- id -old-link,
	// replace with item- id - new-link- i .html
	
	ckId = 'content-' + id; // the CKEditor's id
	var sourcehtml = CKEDITOR.instances[ckId].getData(); // This doesn't work. I need the text from the CKEditor control.
	console.log("sourcehtml: " + sourcehtml);

	var resulthtml = sourcehtml;
	
	$(".item-" + id + "-old-link").each(function(index, value){
		var oldLink = $("#item-" + id + "-old-link-" + index).html();
		var shortLink = $("#item-" + id + "-short-link-" + index).html();
		console.log("#item-" + id + "-old-link: " + oldLink);
		console.log("#item-" + id + "-short-link-" + index + ": " + shortLink);
		resulthtml = resulthtml.replace(oldLink, shortLink); // Replace the original links with the new  (skimified) links. We should also handle the case where there is no skimLink and degrade to newLink or no relacement (but don't leave it blank!!)
	})
	
	resulthtml = resulthtml.replace(/font-size: \d\d[\s]*px/g, "font-size: 11px");  // Replace the original links with the new  (skimified) links
	resulthtml = resulthtml.replace(/style[\s]*=[\s]*"[^"]*"/g, "");  // Strip "style" element from html tags

	console.log("resulthtml: " + resulthtml);
	CKEDITOR.instances[ckId].setData(resulthtml);
	
	return;
} // replaceLinks(id)

function endEditItem(id) {
	console.log("Called endEditItem id "+ id);

	// Remove CKEditor control
	var editor = CKEDITOR.instances['content-' + id];
	if (editor) {
		editor.updateElement();
		editor.destroy(true);
	}
	
	// replace the button eith editItem
	console.log("about to toggle button id button-item-" + id + " ...");
	$('button#button-item-' + id).replaceWith("<button id='button-item-" + id + "' class='btn btn-mini' onclick='editItem(" + id + ")' type='button'>" + id + "</button>");
	console.log("toggled button...");

	return;
}
