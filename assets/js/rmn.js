function init() {

	makeSourceTable ();
/*	makeOutputTable ();
	fetchSource (); // for each row of the table: curl and parse the file
	
	style();
*/
	return;
}

function makeSourceTable() {
console.log("in makeSourceTable()");
	// Create a new YUI instance and populate it with the required modules.
	YUI().use('datatable', function (Y) {
		// DataTable is available and ready for use. Add implementation
		// code here.
		// Columns must match data object property names

		$.get( "ajax/client-select-parsed-content-class.php", function( data ) {
			console.log( data);
			data = jQuery.parseJSON(data);
			var table = new Y.DataTable({
			    columns: ["title", "details", "coupon"],
			    data: data,
			
			    // Optionally configure your table with a caption
			    caption: "My first DataTable!",
			
			    // and/or a summary (table attribute)
			    summary: "Example DataTable showing basic instantiation configuration"
			});
			
			$('body').addClass('yui3-skin-sam');
			table.render("#data");
		});
		
	});
}