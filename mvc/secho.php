<?php
	/*
	Cette fonction affiche une chaine en échappant les entités HTML.
	Le HTML pure est échappé.
	Si le texte fourni n'existe pas, on retourne faux.

	SYNTAXE : escapehtml(string &$text_source[, boolean $no_nl2br = false[, boolean $escape_quotes = false]])

	ARGUMENTS : 
		Obligatoires : 
			- string $text_source : Une variable qui sera utilisé comme texte source
		Optionnels : 
			- boolean $no_nl2br : Par défaut à faux -> les retours à la lignes sont transformé en <br/>. A vrai -> ils sont conservés tels quels
			- boolean $escape_quotes : Par défaut à faux -> les guillemets ne sont pas échappés. A vrai -> ils sont transformés en entitées HTML
			- boolean $authorize_null : Par défaut à vrai -> Les valeurs '', 0, null, ou false sont considérées comme définies. A faux -> considérées comme non définies

	RETOUR : Cette fonction retourne le texte transformé en cas de success, false sinon
	*/

	function secho($text_source, $no_nl2br = false, $escape_quotes = false, $authorize_null = true) //On utilise une reference, si la variable n'existe pas, ça ne lévera pas d'erreur
	{
		if ($authorize_null) //Si on les variables null, false ou '' doivent être prises comme définies
		{
			if(!isset($text_source)) //Si la variable n'existe pas
			{
				return false; //On retourne faux
			}
		}
		else
		{
			if(empty($text_source)) //Si la variable n'existe pas OU est vide
			{
				return false; //On retourne faux
			}
		}
		
		//On met dans une nouvelle variable le texte, pour ne pas le modifier	
		$text = $text_source;
		
		if($escape_quotes)
		{
			$text = htmlspecialchars($text, ENT_QUOTES); //On échappe le html
		}
		else
		{
			$text = htmlspecialchars($text); //On échappe le html
		}

		if(!$no_nl2br) //Si doit activer la transformation des retours à la ligne
		{
			$text = nl2br($text); //On affiche la variable
		}

		echo $text; //On retourne le texte echappé
	}
