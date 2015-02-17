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
	jQuery('body').prepend(alerthtml);
}
