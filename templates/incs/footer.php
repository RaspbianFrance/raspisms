	<footer class="text-center">
		RaspiSMS a été créé pour vous par le site <a href="http://raspbian-france.fr">Raspbian-France</a>, site dédié à la Raspberry Pi<br/>
		Copyright 2014. RaspiSMS est un programme sous <a href="https://www.gnu.org/licenses/gpl.txt" rel="nofollow">licence GNU GPL</a>.<br/>
    </footer>

	<?php if ($_SESSION['user']['settings']['sms_reception_sound'] ?? false) { ?>
		<audio id="reception-sound">
			<source src="<?php echo HTTP_PWD_SOUND; ?>/receptionSound.ogg" type="audio/ogg">
			<source src="<?php echo HTTP_PWD_SOUND; ?>/receptionSound.mp3" type="audio/mpeg">
			<source src="<?php echo HTTP_PWD_SOUND; ?>/receptionSound.wav" type="audio/wav">
		</audio>
	<?php } ?>

    <?php if (ENV == 'dev') { ?>
		<script>
			<?php while ($message = \FlashMessage\FlashMessage::next()) { ?>
				alert('<?php echo $message['type'] . ' : ' . $message['text']; ?>');
			<?php } ?>
		</script>
    <?php } ?>

    </body>
</html>
