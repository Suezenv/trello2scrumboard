$( document ).ready(function() {
	// $('#exampleModal').modal();

	// $('#extract').on('click', getContent($("#message-text").val()));
	getContent();
});

function getContent() {
    $.ajax({
	     // The URL for the request
	    url: "trello.json",
	 
	    // Whether this is a POST or GET request
	    type: "GET",
	 
	    // The type of data we expect back
	    dataType : "json",
	 
	    // Code to run if the request succeeds;
	    // the response is passed to the function
	    success: function( json ) {
	    	for (k in json.lists) {
	    		if (json.cards[k].closed == false) {
	    			$('#content').append('<div class="col-md-3"><div class="panel panel-default" id="panel-3"><div class="panel-heading"><span class="title"><input type="checkbox" id="' + json.lists[k].id + '" value="' + json.lists[k].id + '"> <strong>' + json.lists[k].name + '</strong></span><span class="pull-right"><button class="btn btn-primary">Print</button></span></div><div class="panel-body" id="panel-' + json.lists[k].id + '"><ul></ul></div><div class="panel-footer"></div></div></div>');
	    			$('#' + json.lists[k].id).on('click', function () {
	    				tick($(this));
	    			});
	    		}
	    	}

	    	for (k in json.cards) {
	    		if (json.cards[k].closed == false) {
	    			$('#panel-' + json.cards[k].idList + ' ul').append('<li><input type="checkbox" class="story" id="' + json.cards[k].id + '" value="' + json.cards[k].id + '"> ' + json.cards[k].name +'</li>');
	    		}
	    	}
	    },
	 
	    // Code to run if the request fails; the raw request and
	    // status codes are passed to the function
	    error: function( xhr, status, errorThrown ) {
	        console.log( "Error: " + errorThrown );
	        console.log( "Status: " + status );
	        console.dir( xhr );
	    },
	});
}

function tick(obj) {
	console.log('tick');
	obj.find('input').each(function() {
		console.log('story');
		if ($(this).checked()) {
			$(this).unchecked();
		} else {
			$(this).checked();
		}

	});
}