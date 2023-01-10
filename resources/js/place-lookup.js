/**
 * Place ID Lookup
 * 
 */

try {
	const input = document.getElementById("placeIdSearchBox");
	const options = {
		fields: ["place_id"],
	};
	const autocomplete = new google.maps.places.Autocomplete(input, options);
	autocomplete.addListener('place_changed', placeIdChosen);

	function placeIdChosen() {
		$('#placeIdSearchResult').val(autocomplete.getPlace().place_id);
	}

	$('#placeIdSearchInsert').click(function() {
		$('#placeId').val($('#placeIdSearchResult').val());
	});
} catch (e) {
	console.log(e);
}