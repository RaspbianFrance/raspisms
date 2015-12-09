	<footer class="text-center">
		RaspiSMS a été créé pour vous par le site <a href="http://raspbian-france.fr">Raspbian-France</a>, site dédié à la Raspberry Pi<br/>
		Copyright 2014. RaspiSMS est un programme sous <a href="https://www.gnu.org/licenses/gpl.txt" rel="nofollow">licence GNU GPL</a>.<br/>
	</footer>
	<?php if (RASPISMS_SETTINGS_SMS_RECEPTION_SOUND) { ?>
		<audio id="reception-sound">
			<source src="<?php echo HTTP_PWD; ?>sounds/receptionSound.ogg" type="audio/ogg">
			<source src="<?php echo HTTP_PWD; ?>sounds/receptionSound.mp3" type="audio/mpeg">
			<source src="<?php echo HTTP_PWD; ?>sounds/receptionSound.wav" type="audio/wav">
		</audio>
	<?php } ?>
	</body>
</html>
