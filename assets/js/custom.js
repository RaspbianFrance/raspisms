/**
 * Ce script contient toutes les fonctions javascript utilisées globalement dans RaspiSMS
 */

/**
 * Cette fonction affiche un message de succès ou d'erreur dans une popup
 */
function showMessage(message, type)
{
	if (type == 1) //Si message de succès
	{
		var type = 'alert-success';
	}
	else
	{
		var type = 'alert-danger';
	}

	var alerthtml = '<div class="col-xs-10 col-xs-offset-1 col-md-6 col-md-offset-3 popup-alert alert ' + type + '"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>' + message + '</div>';
	jQuery('body .popup-alerts-container').prepend(alerthtml);
}

/**
 * Cette fonction vérifie si un message a été reçu
 */
function verifReceived()
{
	jQuery('.popup-alert').fadeOut('slow');
	jQuery.getJSON(HTTP_PWD + "/receiveds/popup", function( data ) {
		$.each(data, function(key, val) {
			showMessage('SMS reçu du ' + val.send_by.replace(/</g, "&lt;").replace(/>/g, "&gt;") + ' : ' + val.content.replace(/</g, "&lt;").replace(/>/g, "&gt;"), 1);
			playReceptionSound();
		});
	});
}

/**
 * Cette fonction permet de scroller au dernier message
 */
function scrollDownDiscussion()
{
	var discussion_height = jQuery('.discussion-container').innerHeight();
	var discussion_scroll_height = jQuery('.discussion-container')[0].scrollHeight;
	var discussion_scroll_top = jQuery('.discussion-container').scrollTop();
	var scroll_before_end = discussion_scroll_height - (discussion_scroll_top + discussion_height);

	//On scroll uniquement si on a pas remonté plus haut que la moitié de la fenetre de discussion
	if (scroll_before_end <= discussion_height / 2)
	{
		jQuery('.discussion-container').animate({scrollTop: 1000000});
	}
}

/**
 * Cette fonction jou le son de reception d'un SMS
 */
function playReceptionSound ()
{
	var receptionSound = jQuery('body').find('#reception-sound');
	if (receptionSound.length)
	{
		receptionSound[0].play();
	}
}

jQuery(document).ready(function()
{
	var verifReceivedInterval = setInterval(verifReceived, 10000);

	jQuery('body').on('click', '.goto', function (e) {
		e.preventDefault();
		if (jQuery(this).attr('url'))
		{
			if (jQuery(this).attr('target'))
			{
				window.open(jQuery(this).attr('url'), jQuery(this).attr('target'));
			}
			else
			{
				window.location = jQuery(this).attr('url');
			}
		}
	});

	jQuery('body').on('submit', '.send-message-discussion', function (e) 
	{
		e.preventDefault();

		var form = jQuery(this);
		var message = form.find('textarea').val();
		var formData = new FormData(form[0]);
		jQuery('.discussion-container').find('#send-message-spiner').remove();
		jQuery('.discussion-container').append('<div class="text-center" id="send-message-spiner"><i class="fa fa-spinner fa-spin"></i></div>');
		scrollDownDiscussion();
		jQuery.ajax({
			url: form.attr('action'),
			type: form.attr('method'),
			data: formData,
			contentType: false,
			processData: false,
			dataType: "json",
			success: function (data)
			{
				if (!data.success)
				{
					showMessage(data.message.replace(/</g, "&lt;").replace(/>/g, "&gt;"), 0);
					jQuery('.discussion-container').find('#send-message-spiner').remove();
				}
			}
		}).done(function()
		{
			form.trigger("reset");
		});
	});
});
