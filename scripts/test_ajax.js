jQuery(document).ready(function($){
	$("#wpn-test-button").click(function(){
		$.ajax({
			type: "POST",
			dataType: 'json',
			data:{device_iden: "",
				type: "note",
				title: "Test Message",
				body: "Om.. Its Working"},
			url: "https://api.pushbullet.com/v2/pushes",
			async: false,
			beforeSend: function (xhr) {
					xhr.setRequestHeader('Authorization', header($("#wpn_access_token").val(), ''));
			},
			success: function(msg){
			$("#wpn-test-result").html("Test Notification Successfully sent")
			},
			error: function(){
			$("#wpn-test-result").html("Sorry.. Failed. Please check your Access token.")
			}
		});
			
		function header(user, password) {
			var hash = btoa(user + ':' + password);
			return "Basic " + hash;
		}
	});
});