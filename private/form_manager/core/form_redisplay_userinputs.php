<?php

class FormRedisplayUserinputs
{
    public static function start(\DOMDocument $dom, bool $isValidPost, ?array $fields): void
    {
        // si le formulaire n'est pas correct réafficher les entrées utilisateurs valides dans leurs champs.
        if ($isValidPost === false && $fields !== null)
        {
            foreach ($fields as $fieldName => $field)
            {
                // récupérer l'entrée utilisateur du champ si elle existe et qu'elle ne contient pas d'erreur.
                if (isset($field['userInput']) && count($field['errors']) === 0)
                {
                    // INPUT
                    if ($field['tag'] == "input")
                    {
                        if (isset($field['attributes']['type']))
                        {
                            if ($field['attributes']['type'] == 'file')
                            {
                                continue;
                            }
                            elseif ($field['attributes']['type'] == 'checkbox')
                            {
                                static::manage_tagWithMultipleValues($dom, "//input[@name='" . $field['attributes']['name'] . "']", $field['userInput'], 'checked');
                            }
                            else
                            {
                                $field['domElem']->setAttribute("value", $field['userInput']);
                            }
                        }
                    }
                    // TEXTAREA
                    elseif ($field['tag'] == "textarea")
                    {
                        $field['domElem']->nodeValue = $field['userInput'];
                    }
                    // SELECT
                    else
                    {
                        static::manage_tagWithMultipleValues($dom, "//select[@name='" . $field['attributes']['name'] . "']/option", $field['userInput'], 'selected');
                    }
                }
            }
        }
    }

    private static function manage_tagWithMultipleValues(\DOMDocument $dom, string $xpathQuery, $userInputs, string $attributeToAdd): void
    {
        $xpath = new \DOMXPath($dom);
        $fields = iterator_to_array($xpath->query($xpathQuery));
        $userInputs = !is_array($userInputs) ? [$userInputs] : $userInputs;

        foreach($fields as $field)
        {
            if (array_search($field->getAttribute('value'), $userInputs) !== false)
            {
                $field->setAttribute($attributeToAdd, true);
            }
            else
            {
                $field->removeAttribute($attributeToAdd);
            }
        }
    }
}