$(document).ready(function() {
	$('.removeDiagnosis').live('click',function(e) {
		e.preventDefault();

		$('#diagnosis_id').val($(this).attr('rel'));

		$('#confirm_remove_diagnosis_dialog').dialog({
			resizable: false,
			modal: true,
			width: 560
		});
	});

	$('button.btn_remove_diagnosis').click(function(e) {
		e.preventDefault();

		$("#confirm_remove_diagnosis_dialog").dialog("close");

		$.ajax({
			'type': 'GET',
			'url': baseUrl+'/patient/removediagnosis?patient_id='+OE_patient_id+'&diagnosis_id='+$('#diagnosis_id').val(),
			'success': function(html) {
				if (html == 'success') {
					$('a.removeDiagnosis[rel="'+$('#diagnosis_id').val()+'"]').parent().parent().remove();
				} else {
					alert("Sorry, an internal error occurred and we were unable to remove the diagnosis.\n\nPlease contact support for assistance.");
				}
			},
			'error': function() {
				alert("Sorry, an internal error occurred and we were unable to remove the diagnosis.\n\nPlease contact support for assistance.");
			}
		});
	});

	$('button.btn_cancel_remove_diagnosis').click(function(e) {
		e.preventDefault();
		$("#confirm_remove_diagnosis_dialog").dialog("close");
	});

	$('tr.all-episode').unbind('click').click(function(e) {
		e.preventDefault();
		window.location.href = baseUrl+'/patient/episode/'+$(this).attr('id');
	});

	$('a.removeContact').die('click').live('click',function(e) {
		e.preventDefault();

		var row = $(this).parent().parent();

		$.ajax({
			'type': 'GET',
			'url': baseUrl+'/patient/unassociateContact?pca_id='+row.attr('data-attr-pca-id'),
			'success': function(resp) {
				if (resp == "1") {
					if (row.attr('data-attr-location-id')) {
						currentContacts['locations'].splice(currentContacts['locations'].indexOf(row.attr('data-attr-location-id')),1);
					} else {
						currentContacts['contacts'].splice(currentContacts['contacts'].indexOf(row.attr('data-attr-contact-id')),1);
					}
					row.remove();
				} else {
					alert("There was an error removing the contact association, please try again or contact support for assistance.");
				}
			}
		});
	});

	$('#contactfilter').change(function() {
		$('#contactname').focus();
	});
});

var contactCache = {};
