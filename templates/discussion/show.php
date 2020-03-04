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
					<div class="text-center"><i class="fa fa-spinner fa-spin"></i></div>
				</div>
				<div class="col-lg-12 message-input-container">
					<div class="discussion-message message-input">
						<form class="send-message-discussion" action="<?php $this->s(\descartes\Router::url('Discussion', 'send', ['csrf' => $_SESSION['csrf']])); ?>" method="POST">
							<textarea name="text" placeholder="Envoyer un message..."></textarea>
							<input type="hidden" name="destination" value="<?php $this->s($number); ?>" />
                            <?php if ($response_phone ) { ?>
                                <input type="hidden" name="id_phone" value="<?php $this->s($response_phone['id']); ?>" />
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

		var alreadyReceivedMessages = [];

		/**
		 * Cette fonction vérifie régulièrement les sms pour mettre à jour l'affichage
		 */
		function getmessages ()
		{
			ajaxTransactionId = Date.now();
			jQuery.getJSON(HTTP_PWD + "/discussion/getmessage/<?php echo htmlspecialchars(urlencode($number)); ?>/" + ajaxTransactionId , function( data ) {

                if (data.transaction_id != ajaxTransactionId)
				{
					return false;
				}

				jQuery('.discussion-container').html('');


				$.each(data.messages, function(key, message) {

					<?php if ($_SESSION['user']['settings']['detect_url']) { ?>
						//On ajoute la detection de lien dans le texte du message
						message.text = Autolinker.link(message.text, {newWindow:true});
					<?php } ?>

					switch (message.type)
					{
						case 'received' :
							var texte = '' +
							'<div class="clearfix message-container">' +
								'<div class="discussion-message message-received">' +
									'<div class="discussion-message-text">' + message.text + '</div>' +
									'<div class="discussion-message-date">' + message.date + '</div>' +
								'</div>' +
							'</div>';

							if (alreadyReceivedMessages.indexOf(message.md5) == -1)
							{
								playReceptionSound();
								alreadyReceivedMessages.push(message.md5);
							}

							break;
                        case 'sended' :
							var texte = '' +
							'<div class="clearfix message-container">' +
								'<div class="discussion-message message-sended">' +
									'<div class="discussion-message-text">' + message.text + '</div>' +
									'<div class="discussion-message-date">' + message.date + ' ' + (message.status == 'delivered' ? '<span class="fa fa-check-circle fa-fw text-success"></span>' : (message.status == 'failed' ? '<span class="fa fa-times-circle fa-fw text-danger"></span>' : '<span class="fa fa-clock-o fa-fw text-info"></span>' )) + '</div>' +
								'</div>' +
							'</div>';
							break;
						case 'inprogress' :
							var texte = '' +
								'<div class="clearfix message-container">' +
									'<div class="discussion-message message-sended">' +
										'<div class="message-in-progress-hover"><i class="fa fa-spinner fa-spin"></i></div>' +
										'<div class="discussion-message-text">' + message.text + '</div>' +
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
				scrollDownDiscussion();
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

			var containerHeight = Math.floor(windowHeight - (containerPosition.top + footerHeight * 2 + messageInputContainer));

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
