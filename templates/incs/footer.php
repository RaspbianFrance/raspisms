    <footer class="text-center">
        <img src="<?= HTTP_PWD_IMG; ?>/logo.png"/><br/>
        Copyright 2014-<?= date('Y'); ?> - RaspiSMS est un logiciel libre distribué sous <a href="https://www.gnu.org/licenses/gpl.txt">licence GNU GPL V3</a>.<br/>
        RaspiSMS est disponible en auto-hébergement, ou <a href="https://raspisms.fr">en mode SaaS</a>.
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
