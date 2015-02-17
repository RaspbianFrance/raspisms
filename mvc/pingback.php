<?php 
	/*
		Ce fichier permet d'envoyer l'adresse IP du modèle MVC au premier démarrage.
		Il sera détruit immédiatement après.
		Il est légitime que vous vous posiez la question de savoir pourquoi votre IP est envoyée à un serveur central.
		
		Ce modèle MVC est distrubué sous license GNU/GPL, gratuitement. Le seul bénèfice que nous retirons de ce système, en dehors du plaisir d'aider la communauté, et l'expérience qu'il nous apporte.
		Afin de pouvoir justifier de cette expérience, notamment sur un CV, nous devons êtres à même de donner une estimation du nombre de serveurs faisant tourner notre système MVC.
		De plus, cela nous permet de connaître à des fin statistiques le nombre d'installations.
		Afin que tout le monde puisse se rendre compte de l'étendue de la communauté utilisant ce système, la liste de ces serveurs est disponible à l'adresse ajani.fr/mvc.
		
		Aucune autre données que vôtre adresse IP/URL ne seront récupérées. Cette adresse ne sera jamais utilisées dans un autre cadre que celui décrit ci-dessus.
		Vous avez notre parole, nous espérons avoir votre confiance.
	*/
	$url = $_SERVER['HTTP_HOST'];
	$path = $_SERVER['REQUEST_URI'];
	
	$url = rawurlencode($url);
	$path = rawurlencode($path);
?>
	<img src="<?php echo htmlspecialchars('http://ajani.fr/mvc/save.php?url=' . $url . '&path=' . $path, ENT_QUOTES); ?>" style="display:none;" />
<?php
	@file_put_contents(PWD . 'mvc/pingback.php', '<?php #File remove');
