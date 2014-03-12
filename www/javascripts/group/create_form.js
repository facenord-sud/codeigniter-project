$(function() {
	$('#private-groupe').toggle($('#is_private').prop('checked'));
	$("#is_private").click(function(){
    		$('#private-groupe').slideToggle();
	});
});