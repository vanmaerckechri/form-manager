<?php

class FormConvertToConventionalDom
{
    public static function start(\DOMDocument $dom): string
    {
        $newDom = clone $dom;
        $xpath = new \DOMXPath($newDom);
        // Parcourir les balises pour vérifier s'il s'agit d'un champ particulier à transformer en code HTML conventionnel.
        $tags = $xpath->query("//input | //select | //textarea");

        // Gérer les attributs.
        foreach ($tags as $tag)
        {
            /* ATTRIBUTS CONVENTIONNELS */
            // Attribut "type" avec la valeur "file".
            if ($tag->hasAttribute("type") && $tag->getAttribute("type") == "file")
            {
                // S'assurer que les attributs du formulaire permettant l'envoi de fichier au serveur sont correctement configurés.
                $form = $xpath->query("//form")[0];
                $form->setAttribute("method", "POST");
                $form->setAttribute("enctype", "multipart/form-data");
                // S'assurer que la valeur de l'attribut "name" soit adaptée aux envois de fichiers multiples.
                if ($tag->hasAttribute("multiple") && substr($tag->getAttribute("name"), -2) != '[]')
                {
                    $tag->setAttribute("name", $tag->getAttribute("name") . "[]");
                }
            }
            /* ATTRIBUTS NON CONVENTIONNELS */
            foreach ($tag->attributes as $attrName => $attrValue)
            {
                if (substr($attrName, 0, 3) === "fm-")
                {
                    static::manage_unconventionalAttributes($newDom, $tag, $attrName, $attrValue->value);
                }
            }
        }

        return $newDom->saveHTML();
    }

    private static function manage_unconventionalAttributes(\DOMDocument $dom, \DOMElement $tag, string $attrName, string $value): void
    {
        // Captcha.
        if ($tag->hasAttribute("fm-captcha"))
        { 
            static::convert_captcha($dom, $tag, $value);
        }
        // Supprimer l'attribut non conventionnel.          
        $tag->removeAttribute($attrName);
    }

    private static function convert_captcha(\DOMDocument $dom, \DOMElement $tag, string $value): void
    {
        /*
            => le nom du formulaire servira à la gestion de plusieurs formulaires sur une même page par le captcha...
            // Récupérer le nom du formulaire.
            $xpath = new \\DOMXPath($dom);
            $inputWithFormName = $xpath->query("//input[@name='fm_form_name']");
            $formName = isset($inputWithFormName[0]) ? $inputWithFormName[0]->getAttribut('value') : null;
            ... qq chose comme ça
        */
        // Créer un container.
        $span = $dom->createElement('span');
        $span->setAttribute('class', 'captcha');
        ($tag->parentNode)->insertBefore($span, $tag);

        // Générer un captcha et l'ajouter dans le container.
        $span->appendChild(FormCaptcha::get($dom, intval($value)));
        // Déplacer la balise "input" dans le container.
        $span->appendChild($tag);
        // Ajouter un attribut "type" de type "text".
        $tag->setAttribute("type", "text");
    }
}
