<?php

require_once('form_captcha.php');

class FormCheckField
{
	protected static $mimeTypeList = [];
	protected static $messages = [];

	public static function check_field(\DOMDocument $dom, array $field): array
	{
		$fieldName = $field['attributes']['name'];
		// INPUT:
		if ($field['tag'] == 'input')
		{
			// TYPE:
			if (isset($field['attributes']['type']))
			{
				if ($field['attributes']['type'] == 'email')
				{
					return static::check_error($dom, $field, 'static::check_mail');
				}
				elseif ($field['attributes']['type'] == 'number')
				{
					return static::check_error($dom, $field, 'static::check_number');
				}
				elseif ($field['attributes']['type'] == 'checkbox')
				{
					return static::check_error($dom, $field, 'static::check_checkbox');
				}
				elseif ($field['attributes']['type'] == 'file')
				{
					return static::check_error($dom, $field, 'static::check_file');
				}
				else
				{
					return static::check_error($dom, $field);
				}
			}
			// FM-CAPTCHA:
			elseif (isset($field['attributes']['fm-captcha']))
			{
				return static::check_error($dom, $field, 'static::check_captcha');
			}
		}
		// SELECT:
		elseif ($field['tag'] == 'select')
		{
			return static::check_error($dom, $field, 'static::check_select');
		}
		// TEXTAREA:
		elseif ($field['tag'] == 'textarea')
		{
			return static::check_error($dom, $field);
		}
		return []; // temporaire
	}

	private static function check_error(\DOMDocument $dom, array $field, ?callable $filter_value = null): array
	{
		$errors = [];
		// Vérifier que les règles universelles du champ sont respectées.
		foreach ($field['attributes'] as $key => $value)
		{
			if (($key == 'required' && $field['userInput'] == null)
				|| ($key == 'minlength' && isset($field['userInput']) && !empty($field['userInput']) && ($value > strlen($field['userInput'])))
				|| ($key == 'maxlength' && isset($field['userInput']) && !empty($field['userInput']) && ($value < strlen($field['userInput'])))
				|| ($key == 'min' && isset($field['userInput']) && !empty($field['userInput']) && is_numeric($field['userInput']) && (floatval($value) > floatval($field['userInput'])))
				|| ($key == 'max' && isset($field['userInput']) && !empty($field['userInput']) && is_numeric($field['userInput']) && (floatval($value) < floatval($field['userInput'])))
				|| ($key == 'pattern' && isset($field['userInput']) && !empty($field['userInput']) && !preg_match('/' . $value . '/', $field['userInput'])))
			{
				$value = !is_array($value) ? [$value] : $value;
				// Gérer le cas particulier des patterns pour la correspondance des messages d'erreur.
				if ($key == "pattern")
                {
					$errors['error_pattern_' . $value[0]] = $value;
                }
				// Tous les autres cas...
                else
                {
                    $errors['error_' . $key] = $value;
                }
			}
		}
		// Vérifier que les règles spécifiques au type de champ sont respectées.
		if ($filter_value !== null && isset($field['userInput']))
		{
			$error = call_user_func_array($filter_value, array($field, $dom));
			if ($error !== null)
			{
				$errors[$error] = [];
			}
		}

		return $errors;
	}

	private static function check_mail(array $field): ?string
	{
		if (!filter_var($field['userInput'], FILTER_VALIDATE_EMAIL))
		{
			return 'error_mail';
		}
		return null;
	}

	private static function check_number(array $field): ?string
	{
		if (!is_numeric($field['userInput']))
		{
			return 'error_number';
		}
		return null;
	}

	private static function check_file(array $field): ?string
	{
		// Éviter d'importer le contenu du fichier json s'il a déjà été importé.
		self::$mimeTypeList = count(self::$mimeTypeList) === 0 ? json_decode(file_get_contents('private' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'form_manager' . DIRECTORY_SEPARATOR . 'mime-type-list.json'), true) : self::$mimeTypeList;
		$userInputTypeMime = is_string($field['userInput']['type']) ? [$field['userInput']['type']] : $field['userInput']['type'];
		$accept = isset($field['attributes']['accept']) ? $field['attributes']['accept'] : "*";
		// Envoyer une erreur lors d'une tentative d'envoi multiple de fichiers alors que l'attibut "mutliple" n'a pas été précisé dans la balise.
		if (!isset($field['attributes']['multiple']) && count($field['userInput']['name']) > 1)
		{
			return 'error_fileMultiple';
		}
		// Si tous les formats de fichier sont acceptés, passer la vérification de type mime.
		if ($accept == "*")
		{
			return null;
		}
		$accept = array_map('trim', explode(',', $accept));
		// Parcourir le type mime de tous les fichiers envoyés au serveur et vérifier s'ils sont compabiles avec la configuration de la balise html.
		foreach($userInputTypeMime as $type)
		{
			$t = explode('/', $type);
			if (count($t) === 1)
			{
				return null;
			}
			$isValid = false;
			foreach($accept as $a)
			{
				$a = explode('/', $a);
				// Attribut "accept" configuré avec l'extension.
				if (count($a) === 1)
				{
					$a = substr($a[0], 1);
					if (array_search($a, self::$mimeTypeList[$t[0]][$t[1]]) !== false)
					{
						$isValid = true;
					}
				}
				// Attribut "accept" configuré avec la famille.
				else
				{
					if ($a[0] == $t[0] && ($a[1] == $t[1] || $a[1] == "*"))
					{
						$isValid = true;
					}
				}
			}
			if ($isValid === false)
			{
				return 'error_fileFormat';
			}
		}
		return null;
	}

	private static function check_captcha(array $field): ?string
	{
		if (!FormCaptcha::is_validCaptcha($field['userInput']))
		{
			return 'error_captcha';
		}
		return null;
	}

	private static function check_checkbox(array $field, \DOMDocument $dom): ?string
	{
		return static::check_tagWithMultipleValues($dom, $field, "//input[@name='" . $field['attributes']['name'] . "']/@value");
	}

	private static function check_select(array $field, \DOMDocument $dom): ?string
	{
		return static::check_tagWithMultipleValues($dom, $field, "//select[@name='" . $field['attributes']['name'] . "']/option/@value");
	}

	private static function check_tagWithMultipleValues(\DOMDocument $dom, array $field, string $xpathQuery): ?string
	{
		$userInputs = !is_array($field['userInput']) ? [$field['userInput']] : $field['userInput'];
		// Vérifier si les entrées utilisateur existent dans la valeur des options disponibles.
		$xpath = new \DOMXPath($dom);
        $fields = iterator_to_array($xpath->query($xpathQuery));
		foreach($fields as $field)
		{
			$values[] = $field->nodeValue;
		}
		foreach($userInputs as $userInput)
		{
			if (array_search($userInput, $values) === false)
			{
				return 'error_html';
			}
		}
		return null;
	}
}