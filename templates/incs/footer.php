	<footer class="text-center">
		RaspiSMS a été créé pour vous par le site <a href="https://raspberry-pi.fr">Raspberry Pi FR</a>, site dédié à la Raspberry Pi<br/>
        Copyright 2014-<?= date('Y'); ?>. RaspiSMS est un programme sous <a href="https://www.gnu.org/licenses/gpl.txt" rel="nofollow">licence GNU GPL V3</a>.<br/>
    </footer>

	<?php if ($_SESSION['user']['settings']['sms_reception_sound'] ?? false) { ?>
		<audio id="reception-sound">
			<source src="<?php echo HTTP_PWD_SOUND; ?>/receptionSound.ogg" type="audio/ogg">
			<source src="<?php echo HTTP_PWD_SOUND; ?>/receptionSound.mp3" type="audio/mpeg">
			<source src="<?php echo HTTP_PWD_SOUND; ?>/receptionSound.wav" type="audio/wav">
		</audio>
	<?php } ?>

    <?php if (! ($_SESSION['user']['settings']['display_help'] ?? false)) { ?>
        <style>.help {display: none;}</style>
    <?php } ?>
    
    <div class="popup-alerts-container">
        <?php while ($message = \FlashMessage\FlashMessage::next()) { ?>
            <?php $this->render('incs/alert', $message); ?>
        <?php } ?>
    </div>

    </body>
</html>
