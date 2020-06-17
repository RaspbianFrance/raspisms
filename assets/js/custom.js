/**
 * Ce script contient toutes les fonctions javascript utilisées globalement dans RaspiSMS
 */

/**
 * Cette fonction affiche un message de succès ou d'erreur dans une popup
 */
function showMessage(message, type, random_id = null)
{
	if (type == 1) //Si message de succès
	{
		var type = 'alert-success';
	}
	else
	{
		var type = 'alert-danger';
	}

	var alerthtml = '<div id="' + (random_id ? 'popup-' + random_id : '') + '" class="col-xs-10 col-xs-offset-1 col-md-6 col-md-offset-3 popup-alert alert ' + type + '"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>' + message + '</div>';
	jQuery('body .popup-alerts-container').prepend(alerthtml);
}

/**
 * Hide a popup
 */
function fadeAlert(popup_id)
{
	jQuery('#popup-' + popup_id).fadeOut('slow');
}

/**
 * Cette fonction vérifie si un message a été reçu
 */
function verifReceived()
{
	jQuery.getJSON(HTTP_PWD + "/received/popup", function( data ) {
		$.each(data, function(key, val) {
            var rand_id = Math.random().toString(36).substring(2);
			showMessage('SMS reçu du ' + val.origin + ' : ' + val.text, 1, rand_id);
			playReceptionSound();
            setTimeout(function() { fadeAlert(rand_id); }, 10000);
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
    jQuery('.datatable').DataTable({
        "pageLength": 25,
        "bLengthChange": false,
        "language": {
            "url": HTTP_PWD + "/assets/js/datatables/french.json",
        },
        "columnDefs": [{
            'targets': 'checkcolumn',
            'orderable': false,
        }],
    });
    
    jQuery('.datatable').on('draw.dt', function (){
        jQuery('body :checkbox').off('shiftcheckbox');
        jQuery('body :checkbox').shiftcheckbox();
    });

	
    var verifReceivedInterval = setInterval(verifReceived, 10000);
    
    jQuery('body').on('click', '.btn-confirm', function (e)
    {
        e.preventDefault();
        jQuery(this).addClass('btn-warning');
        jQuery(this).removeClass('btn-confirm');
    
        var btn_text = jQuery(this).attr('data-confirm-text') ? jQuery(this).attr('data-confirm-text') : '<span class="fa fa-trash-o"></span> Valider la suppression';

        jQuery(this).html(btn_text);
        return false;
    });

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
