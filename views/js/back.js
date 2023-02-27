$(function() {
	var table = '#table-sps_order',
		form = '#form-sps_order';

	$(document).on("keydown", form, function(event) {
	    return event.key != "Enter";
	});

	$(table+' tbody .weight input[type=text]').blur(function(e) {

		$(this).val($(this).val().replace(/,/g, '.'));

		var action = 'updateParcelInvoicingAmount',
			element = $(this).parent(),
			childElement = 'input',
			parcelInvAmount = e.target.value,
			orderId = $(this).attr('data-id');

		ajaxInputUpdate(parcelInvAmount, orderId, action, element, childElement);

	});

	$(table+' tbody .inv-unit select').change(function(e) {

		var action = 'updateParcelInvoicingUnit',
			element = $(this).parent(),
			childElement = 'select',
			parcelInvUnit = e.target.value,
			orderId = $(this).attr('data-id');

		ajaxInputUpdate(parcelInvUnit, orderId, action, element, childElement);

	});

	$(table+' tbody .notification-type select').change(function(e) {

		var action = 'updateParcelNotification',
			element = $(this).parent(),
			childElement = 'select',
			parcelInvUnit = e.target.value,
			orderId = $(this).attr('data-id');

		ajaxInputUpdate(parcelInvUnit, orderId, action, element, childElement);

	});

	$(table+' tbody .service-type select').change(function(e) {

		var action = 'updateParcelService',
			element = $(this).parent(),
			childElement = 'select',
			parcelInvUnit = e.target.value,
			orderId = $(this).attr('data-id');

		ajaxInputUpdate(parcelInvUnit, orderId, action, element, childElement);

	});

	function ajaxInputUpdate(value, id, action, element, childElement) {
		$.ajax({
			type: 'POST',
			cache: false,
			dataType: 'json',
			url: 'index.php?controller=AdminSps',
			data: {
				ajax: 1,
				controller: 'AdminSps',
				action: action,
				id: id,
				value: value,
				token: token
			},
			beforeSend: function() {
				console.log('start');
				element.append('<span id="loader" class=""><img src="../img/loader.gif" alt=""></span>');
			},
			success: function (data) {
				console.log(data);
				$(element).find(childElement).addClass('db-success', 75);
				setTimeout(function() { $(element).find('.db-success').removeClass('db-success', 750) }, 100);
			},
			error: function( XMLHttpRequest, textStatus, errorThrown ) {
				console.log(XMLHttpRequest);
				$(element).find(childElement).addClass('db-error', 75);
				setTimeout(function() { $(element).find('.db-error').removeClass('db-error', 750) }, 1000);
			},
			complete: function() {
				console.log('end');
				$(element).find('#loader').remove();
			}
		});
	}

	$(table+' .is-cod').click(function() {

		var action = 'updateCod',
			elementParent = $(this),
			element = elementParent.find('i'),
			cod = element.attr('data-value'),
			orderId = element.attr('data-id');

		ajaxBoolUpdate(cod, orderId, action, element, elementParent);

	});

	$(table+' .is-sat').click(function() {

		var action = 'updateSat',
			elementParent = $(this),
			element = elementParent.find('i'),
			cod = element.attr('data-value'),
			orderId = element.attr('data-id');

		ajaxBoolUpdate(cod, orderId, action, element, elementParent);

	});

	function ajaxBoolUpdate(value, id, action, element, elementParent) {
		$.ajax({
			type: 'POST',
			cache: false,
			dataType: 'json',
			url: 'index.php?controller=AdminSps',
			data: {
				ajax: 1,
				controller: 'AdminSps',
				action: action,
				id: id,
				value: value,
				token: token
			},
			beforeSend: function() {
				element.fadeOut(500).remove();
				elementParent.append('<span id="loader" class=""><img src="../img/loader.gif" alt=""></span>');
			},
			success: function(data) {
				console.log(data);
				elementParent.append('<i class="icon-' + data.icon + '" data-value="' + data.value + '" data-id="' + data.id + '"></i>');
			},
			error: function(data) {

			},
			complete: function(data) {
				$(elementParent).find('#loader').remove();
			}
		});
	}
});
