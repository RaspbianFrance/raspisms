<?php
	/**
	 * Cette fonction permet d'afficher un texte de façon sécurisée.
	 *
	 * @param string $text : Le texte à afficher
	 * @param boolean $nl2br (= true) : Si vrai, on transforme le "\n" en <br/>.
	 * @param boolean $escapeQuotes (= true) : Si vrai, on transforme les ' et " en équivalent html
	 * @param boolean $echo (= true) : Si vrai, on affiche directement la chaine modifiée. Sinon, on la retourne sans l'afficher.
	 *
	 * @return mixed : Si $echo est vrai, void. Sinon, la chaine modifiée
	 */
	function secho($text, $nl2br = true, $escapeQuotes = true, $echo = true)
	{
		//On echappe le html, potentiellement avec les quotes
		$text = $escapeQuotes ? htmlspecialchars($text, ENT_QUOTES) : htmlspecialchars($text, ENT_NOQUOTES);

		//On transforme les "\n" en <br/>
		$text = $nl2br ? nl2br($text) : $text;

		//On retourne le texte ou on l'affiche
		if ($echo)
		{
			echo $text;
		}
		else
		{
			return $text;
		}
	}
