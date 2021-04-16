<?php
	//Template dashboard
	
	$this->render('incs/head', ['title' => 'Discussions - Show All'])
?>
<div id="wrapper">
<?php
	$this->render('incs/nav', ['page' => 'discussions'])
?>
	<div id="page-wrapper">
		<div class="container-fluid">
			<!-- Page Heading -->
			<div class="row">
				<div class="col-lg-12">
					<h1 class="page-header">
						Discussion <small><?php $this->s($contact ? $contact['name'] . ' (' . \controllers\internals\Tool::phone_format($number) . ')' : \controllers\internals\Tool::phone_format($number)); ?></small>
					</h1>
					<ol class="breadcrumb">
						<li>
							<i class="fa fa-dashboard"></i> <a href="<?php echo \descartes\Router::url('Dashboard', 'show'); ?>">Dashboard</a>
						</li>
						<li>
							<i class="fa fa-comments-o"></i> <a href="<?php echo \descartes\Router::url('Discussion', 'list'); ?>">Discussions</a>
						</li>
						<li class="active">
							<?php $this->s(\controllers\internals\Tool::phone_format($number)); ?>
						</li>
					</ol>
				</div>
			</div>
			<!-- /.row -->

			<div class="row">
				<div class="col-lg-12 discussion-container">
					<div class="text-center" id="load-message-spinner"><i class="fa fa-spinner fa-spin"></i></div>
				</div>
				<div class="col-lg-12 message-input-container">
					<div class="discussion-message message-input">
						<form class="send-message-discussion" action="<?php $this->s(\descartes\Router::url('Discussion', 'send', ['csrf' => $_SESSION['csrf']])); ?>" method="POST">
							<textarea name="text" placeholder="Envoyer un message..."></textarea>
							<input type="hidden" name="destination" value="<?php $this->s($number); ?>" />
                            <?php if ($response_phone ) { ?>
                                <input type="hidden" name="id_phone" value="<?php $this->s($response_phone['id']); ?>" />
                            <?php } ?>
                            <?php if ($_SESSION['user']['settings']['mms'] ?? false) { ?>
                                <input name="medias[]" type="file" multiple />
                            <?php } ?>
							<button class="btn" ><span class="fa fa-fw fa-send-o"></span> Envoyer</button>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script>
	jQuery(document).ready(function () {

        //List of messages already loaded
        var cachedMessages = {};
        
        /**
		 * Cette fonction vérifie régulièrement les sms pour mettre à jour l'affichage
		 */
		function getmessages ()
		{
            ajaxTransactionId = Date.now();
            var first_load = true;
            url = HTTP_PWD + "/discussion/getmessage/<?php echo htmlspecialchars(urlencode($number)); ?>/" + ajaxTransactionId;
            var newMessages = false;

			jQuery.getJSON(url, function( data ) {

                if (data.transaction_id != ajaxTransactionId)
				{
					return false;
                }

                jQuery('.discussion-container #load-message-spinner').remove();
                jQuery('.discussion-container #send-message-spinner').remove();

                //We also remove all in-progress messages because they are added again in the new response if not sended yet, and if sended they should not appear in double
                jQuery('.discussion-container .message-in-progress-container').remove();

				$.each(data.messages, function(key, message) {
                    //If message already loaded, continue
                    if (message.uid in cachedMessages)
                    {
                        if (cachedMessages[message.uid]['type'] == 'sended') 
                        {
                            if (message.status != cachedMessages[message.uid]['status']) 
                            {
                                cachedMessages[message.uid] = message;
                                var htmlStatus = (message.status == 'delivered' ? '<span class="message-status fa fa-check-circle fa-fw text-success"></span>' : (message.status == 'failed' ? '<span class="message-status fa fa-times-circle fa-fw text-danger"></span>' : '<span class="message-status fa fa-clock-o fa-fw text-info"></span>' ));
                                jQuery('.discussion-container #' + message.uid + " .message-status").replaceWith(htmlStatus);
                            }
                        }
                        return;
                    }

                    //Add the message to the list of already receiveds once
                    cachedMessages[message.uid] = message;
                    newMessages = true;

					<?php if ($_SESSION['user']['settings']['detect_url']) { ?>
						//On ajoute la detection de lien dans le texte du message
						message.text = Autolinker.link(message.text, {newWindow:true});
                    <?php } ?>

                    var medias = message.medias.map((mediaUrl, index) => {
                        var extension = mediaUrl.split('.').pop();
                        if (['jpg', 'jpeg', 'png', 'gif'].includes(extension))
                        {
                            return '<div class="discussion-message-media"><a href="' + mediaUrl + '" target="_blank"><img src="' + mediaUrl + '"/></a></div>';
                        }
                        else if (['webm', 'ogv', 'mp4'].includes(extension))
                        {
                            return '<video controls class="discussion-message-media"><source src="' + mediaUrl + '"/></video>';
                        }
                        else if (['wav', 'ogg', 'mp3'].includes(extension))
                        {
                            return '<audio controls class="discussion-message-media"><source src="' + mediaUrl + '"/></audio>';
                        }
                        else
                        {
                            return '<div class="discussion-message-media"><a href="' + mediaUrl + '" target="_blank">Voir le fichier ' + (Number(index) + 1) + '</a></div>';
                        }
                    });
                    var medias_html = '<div class="discussion-message-medias">' + medias.join('') + '</div>';

					switch (message.type)
                    {
						case 'received' :
							var texte = '' +
							'<div class="clearfix message-container" id="' + message.uid + '">' +
								'<div class="discussion-message message-received">' +
                                    '<div class="discussion-message-text">' + message.text.replace(/\n/g,"<br>") + '</div>' +
                                    medias_html + 
                                    '<div class="discussion-message-date">' + message.date + '</div>' +
								'</div>' +
							'</div>';

							if (!first_load) //If new message received and not first time loading
							{
								playReceptionSound();
							}

							break;
                        case 'sended' :
							var texte = '' +
							'<div class="clearfix message-container" id="' + message.uid + '">' +
								'<div class="discussion-message message-sended">' +
									'<div class="discussion-message-text">' + message.text.replace(/\n/g,"<br>") + '</div>' +
                                    medias_html +
                                    '<div class="discussion-message-date">' + message.date + ' ' + (message.status == 'delivered' ? '<span class="message-status fa fa-check-circle fa-fw text-success"></span>' : (message.status == 'failed' ? '<span class="message-status fa fa-times-circle fa-fw text-danger"></span>' : '<span class="message-status fa fa-clock-o fa-fw text-info"></span>' )) + '</div>' +
								'</div>' +
							'</div>';
							break;
						case 'inprogress' :
							var texte = '' +
								'<div class="clearfix message-container message-in-progress-container" id="' + message.uid + '">' +
									'<div class="discussion-message message-sended">' +
										'<div class="message-in-progress-hover"><i class="fa fa-spinner fa-spin"></i></div>' +
                                        '<div class="discussion-message-text">' + message.text.replace(/\n/g,"<br>") + '</div>' +
                                        medias_html + 
										'<div class="discussion-message-date">' + message.date + '</div>' +
									'</div>' +
								'</div>';
							break;
						default :
							var texte = '';
							break;
					}

					jQuery('.discussion-container').append(texte);
                });

                if (newMessages) {
                    scrollDownDiscussion(first_load);
                }
			});
		}

		/**
		 * Cette fonction permet de fixer la taille de la fenetre de discussion
		 */
		function fullHeightDiscussion()
		{
			var containerPosition = jQuery('.discussion-container').position();
			var windowHeight = jQuery(window).height();
			var messageInputContainer = jQuery('.message-input-container').outerHeight();
			var footerHeight = jQuery('footer').outerHeight();

			var containerHeight = Math.floor(windowHeight - (containerPosition.top + messageInputContainer) - 20); //-20 px for aesthetic

			jQuery('.discussion-container').outerHeight(containerHeight);
		}

		fullHeightDiscussion();

		jQuery(window).on('resize', function () {
			fullHeightDiscussion();
		});

		var getmessagesInterval = setInterval(getmessages, 2500);
	});
</script>
<?php
	$this->render('incs/footer');
