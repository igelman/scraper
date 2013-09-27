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
					{'key': key, 'allowHTML': true}
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