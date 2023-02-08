<?php

class FormManageMessages
{
	protected static $messages = [];

	public static function display_messages(\DOMDocument $dom, array $field): void
	{
		// En cas d'erreur, ajouter les attributs d'accessibilité et afficher le(s) message(s).
		if (isset($field['errors']) && count($field['errors']) > 0)
		{
			// Ajouter les attributs d'accessibilité.
			$field['domElem']->setAttribute('aria-invalid', 'true');
			$field['domElem']->setAttribute('aria-describedby', 'fm-field-error-' . $field['attributes']['name']);
			// Créer les éléments DOM permettant d'affficher le(s) message(s).
			$ul = $dom->createElement('ul');
			foreach ($field['errors'] as $messageKey => $messageValues)
			{
				$er = static::get_message($messageKey, $messageValues);
				$li = $dom->createElement('li', $er);
				$li->setAttribute('class', 'fm-field-error');
				$ul->appendChild($li);
			}
			// Vérifier s'il existe un marqueur HTML pour placer le(s) message(s).
			$tagError = $dom->getElementById('fm-field-error-' . $field['attributes']['name']);
			if ($tagError === null)
			{
				// Ajouter les attributs (un id lié au nom du champ et une classe par défaut).
				$ul->setAttribute('id', 'fm-field-error-' . $field['attributes']['name']);
				// Placer le(s) message(s) d'erreur sous le champ concerné.
				$field['domElem']->parentNode->insertBefore($ul, $field['domElem']->nextSibling);
			}
			else
			{
				// Récupérer et ajouter les attributs à la liste d'erreur.
				foreach ($tagError->attributes as $attr)
				{
					$ul->setAttribute($attr->nodeName, $attr->nodeValue);
				}
				// Placer le(s) message(s) d'erreur avant l'élément DOM de réf.
				$tagError->parentNode->insertBefore($ul, $tagError);
			}
		}
	}

	public static function remove_markers(\DOMDocument $dom): void
	{
        $xpath = new \DOMXPath($dom);
        $tagError = $xpath->query("//div[starts-with(@id,'fm-field-error-')]");
		foreach ($tagError as $tag)
		{
			$tag->parentNode->removeChild($tag);
		}
	}

	private static function get_message(string $messageKey, array $values = []): ?string
	{
		// Éviter d'importer le contenu du fichier json s'il a déjà été importé.
		self::$messages = count(self::$messages) === 0 ? json_decode(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'form_messages.json'), true) : self::$messages;
		$message = isset(self::$messages[$messageKey][LANG]) ? self::$messages[$messageKey][LANG] : null;
		foreach ($values as $key => $value)
		{
			$message = str_replace("%$key%", $value, $message);
		}
		return $message;
	}
}