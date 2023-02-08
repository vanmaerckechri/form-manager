<?php

require_once('form_convert_to_conventional_dom.php');
require_once('form_check_field.php');
require_once('form_manage_messages.php');
require_once('form_redisplay_userinputs.php');

class FormManager
{
    private $dom;
    private $fields = null;
    private $isValidPost = true;
    private $formId;

    public function __construct(string $form)
	{
        // Définir la langue par défaut (pour les messages d'alerte).
        if (!defined('LANG'))
        {
            define('LANG', 'fr');
        }

        // Charger le formulaire et configurer son type d'encodage.
        $this->dom = new \DOMDocument;
        $this->dom->loadHTML(mb_convert_encoding($form, 'HTML-ENTITIES', 'UTF-8'));

        // Générer un attribut ID au formulaire si celui-ci n'en dispose pas.
        $this->formId = !empty($this->dom->getElementsByTagName('form')->item(0)->getAttribute('id')) ? $this->dom->getElementsByTagName('form')->item(0)->getAttribute('id') : uniqid('cvm-form-');
        $this->dom->getElementsByTagName('form')->item(0)->setAttribute('id', $this->formId);

        // Vérifier si une soumission de formulaire a été faite.
        if ($this->is_post())
        {
            // Vérifier la validité des entrées utilisateur.
            $this->fields = $this->get_fields();
        }
    }

    public function get_form(): string
	{
        if ($this->fields !== null)
        {
            // Parcourir les champs.
            foreach ($this->fields as $field)
            {
                // Ajouter les potentiels messages d'alerte au formulaire.
                FormManageMessages::display_messages($this->dom, $field);
            }
            // En cas d'entrées utilisateur non valides, réécrire les données utilisateur valides dans leur champ respectif.
            FormRedisplayUserinputs::start($this->dom, $this->isValidPost, $this->fields);
        }
        // Retirer les marqueurs HTML utilisés pour la personnalisation du positionnement des messages d'alerte.
        FormManageMessages::remove_markers($this->dom);
        // Retourner le formulaire sous forme de code HTML conventionnel après traduction potentielle des attributs non conventionnels et/ou de leur suppression.
        return FormConvertToConventionalDom::start($this->dom);
	}

    public function get_postResult(): ?array
    {
        if ($this->fields !== null)
        {
            $output = [];
            if ($this->isValidPost === true)
            {
                foreach($this->fields as $name => $values)
                {
                    // Les élements ne devant pas être récupérés.
                    if (isset($values['attributes']['fm-captcha']) || isset($values['attributes']['fm_form_name']))
                    {
                        continue;
                    }
                    $output[$name] = $values['userInput'];
                }
            }
            return $output;
        }
        return null;
    }

    public function add_errorAfterValidation(string $fieldName, string $fieldError, array $value = []): void
    {
        $this->isValidPost = false;
        $this->fields[$fieldName]['errors'][$fieldError] = $value;
    }

    public function load_js(string $bridgeFilename): void
    {
        $fieldsCfg = json_encode($this->get_fields(true));
        $messages = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'form_messages.json');
        ob_start();
        ?>
            <script>
                (function(){
                    "use strict";
                    new CVM.FormManager("<?=$bridgeFilename?>", document.getElementById("<?=$this->formId?>"), <?=$fieldsCfg?>, <?=$messages?>);
                }());
            </script>
       <?php
       echo ob_get_clean();
    }

    private function is_post(): bool
	{
        // S'il existe, récupérer le nom du formulaire (indispensable pour les pages disposant de plusieurs formulaires).
        $xpath = new \DOMXPath($this->dom);
        $tagsWithFormName = $xpath->query("//input[@name='fm_form_name']/@value");
        $formName = count($tagsWithFormName) > 0 ? $tagsWithFormName[0]->value : null;
        // Si le formulaire a été envoyé traiter les entrées utilisateur.
        return isset($_POST) && !empty($_POST) && ($formName == null || isset($_POST['fm_form_name']) && $_POST['fm_form_name'] == $formName);
	}

    private function get_fields(bool $is_forFrontendValidation = false): array
    {
        $output = [];
        // Parcourir tous les types de champs potentiellement présent dans le formulaire.
        $tagsName = ["input", "select", "textarea"];
        foreach ($tagsName as $tagName)
        {
            // Sélectionner toutes les balises du type actuel.
            $tags = $this->dom->getElementsByTagName($tagName);
            // Parcourir les balises de ce type.
            foreach ($tags as $tag)
            {
                // Récupérer les attributs de la balise.
                $attributes = $this->get_attributes($tag);
                if (isset($attributes['name']))
                {
                    // Éviter de tester plus d'une fois les balises partageant un même nom (checkbox).
                    if (isset($output[$attributes['name']]))
                    {
                        continue;
                    }
                    // Récupérer les informations liées à la balise.
                    $field = $this->get_field($tag, $attributes, $is_forFrontendValidation);
                    // Enregistrer les diverses informations de la balise.
                    $output[$attributes['name']] = $field;
                    // Uniquement pour une requête côté backend.
                    if ($is_forFrontendValidation === false)
                    {
                        // Vérifier s'il y a une d'erreur.
                        if (count($field['errors']) > 0)
                        {
                            $this->isValidPost = false;
                        }
                    }
                }
            }
        }
        return $output;
    }

    private function get_field(\DOMElement $tag, array $attributes, bool $is_forFrontendValidation): array
    {
        // Enregistrer le type de champ.
        $output['tag'] = $tag->tagName;
        // Enregistrer les attributs de la balise.
        $output['attributes'] = $attributes;
        // Formater le nom de la balise pour qu'elle soit identique aux cléfs des variables "$_POST" et "$_FILES" (utile pour les balises possédant un attribut "multiple").
        $name = substr($attributes['name'], -2) == '[]' ? substr($attributes['name'], 0, -2) : $attributes['name'];
        // Gérer la balise "input" de type "file".
        if (
            isset($attributes['type']) && $attributes['type'] == 'file'
            && ((is_string($_FILES[$name]['name']) && !empty($_FILES[$name]['name'])) || (is_array($_FILES[$name]['name']) && !empty($_FILES[$name]['name'][0])))
        )
        {
            $files = $_FILES[$name];
            // Si un seul fichier est envoyé, modifier la structure de ses informations pour qu'elle ait la même forme que l'envois multi-fichiers.
            if (!is_array($files['name']))
            {
                foreach ($files as $var => $val)
                {
                    $files[$var] = [$val];
                }
            }               
            $output['userInput'] = $files;
        }
        // Gérer les autres balises.
        elseif (isset($_POST) && isset($_POST[$name]) && !empty($_POST[$name]))
        {
            $output['userInput'] = $_POST[$name];
        }
        else
        {
            $output['userInput'] = null;
        }
        // Uniquement pour une requête côté backend.
        if ($is_forFrontendValidation === false)
        {
            // Enregister l'élément DOM.
            $output['domElem'] = $tag;
            // Récupérer les erreurs potentielles.
            $output['errors'] = FormCheckField::check_field($this->dom, $output);
        }
        return $output;
    }

    private function get_attributes(\DOMElement $tag): array
    {
        $output = [];
        // Vérifier qu'il existe des attributs pour la balise.
        if ($tag->hasAttributes())
        {
            // Parcourir tous les attributs.
            foreach ($tag->attributes as $attr)
            {
                // Enregistrer les attributs.
                $output[$attr->nodeName] = $attr->nodeValue;
            }
        }
        return $output;
    }
}