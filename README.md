# Form Manager (beta)

## Sommaire

- Présentation
- Installation
- Configuration
- Utilisation
- Formulaire

## Présentation

Comme son nom l'indique, "Form Manager" est un gestionnaire de formulaire. Son objectif est d'automatiser la validation des entrées utilisateur en se basant principalement sur les balises et les attributs HTML standards tout en respectant les normes d'accessibilité.

## Installation

Extraire le fichier "form-manager.zip" à la racine du projet.

## Configuration

- ### La langue (facultatif)
 
    Par défaut, la langue française sera configurée.
    ```php
        define('LANG', 'fr');
    ```
    
    Importer FormManager
    ```php
        require_once('form_manager\core\form_manager.php');
    ```

- ### Les messages d'alerte

    Pour configurer les messages d'alerte, il suffit de modifier le fichier suivant:
    "/private/src/config/form_messages.json"
    
    Par défaut, seuls les messages en français sont configurés:
    ```php
    {
        "error_captcha":
        {
            "fr": "Les captchas ne correspondent pas!"
        },
        "error_fileFormat":
        {
            "fr": "Format de fichier invalide!"
        },
        ...
    }
    ```

    Pour ajouter des traductions, il suffit de pratiquer comme suit:
    ```php
    {
        "error_captcha":
        {
            "en": "Captchas don't match!",
            "fr": "Les captchas ne correspondent pas!"
        },
        "error_fileFormat":
        {
            "en": "Invalid file format!",
            "fr": "Format de fichier invalide!"
        },
        ...
    }
    ```
    
    Il est aussi possible de choisir l'emplacement des messages d'alerte liés aux champs à l'aide de marqueurs HTML en utiliser une balise "div" avec un attribut "id" ayant pour valeur "fm-error-field-" suivi du nom du champ concerné:
    ```html
        <div id="fm-field-error-title"></div>
        <label for="title">Titre</label>
        <input type="text" name="title" id="title" required>
    ```

## Utilisation

- Instancier la classe FormManager avec le formulaire HTML:

    ```php
        /*
            $form: chaîne de caractères du formulaire HTML.
        */
        $formManager = new FormManager($form);
    ```

- Afficher le formulaire:

    ```php
        echo $formManager->get_form();
    ```

- Récupérer les entrées utilisateur:

    ```php
        /* 
            retourne:
                null: formulaire non soumis.
                [empty]: entrées utilisateur non valides.
                ['nomDeChamp1' => $valeurDeChamp1 ,'nomDeChamp2' => $valeurDeChamp2, etc.]: entrées utlisateur valides.
        */
        $postResult = $formManager->get_postResult();
    ```

- Injecter une erreur à un champ après validation des entrées utilisateurs:

    Bien que les entrées utilisateur aient été validé par le gestionnaire de formulaire, il se peut, pour diverses raisons (requête SQL qui échoue, enregistrement de fichier impossible sur le serveur, etc.), que l'on désire communiquer un message d'erreur à un champ et ainsi lui annuler sa validité.
    ```php
        /*
            $nomDeChamp: chaîne de caractères du nom de champ.
            $error_nomErreur: chaîne de caractères du nom de l'erreur (voir Configuration/Les messages d'alerte).
        */
        $formManager->add_errorAfterValidation($nomDeChamp, $error_nomErreur);
    ```
## Formulaire

- ### Les balises HTML conventionnelles
    
    Toutes les balises conventionnelles peuvent potentiellement être utlisées par le framework, cependant le traitement des balises suivantes a été optimisé:
    - input (text, email, checkbox, file, hidden, submit)
    - select
    - textarea


- ### Les attributs HTML conventionnels

    Les attributs conventionnels suivants sont pris en compte lors de la validation des entrées utilisateurs côté serveur:
    - minlength
    - maxlength
    - pattern
    - accept
    
    
- ### Les attributs HTML non conventionnels

    Les attributs non conventionnels permettent de proposer des options supplémentaires:
    - fm-captcha:
        - Objectif: Proposer un captcha maison et facile d'utilisation (actuellement pensé pour des pages ne possédant qu'un unique formulaire).
        - Balise: "input" sans type avec un attribut "name" et "required".
        - Valeur: Un entier déterminant la longeur du captcha.


- ### Informations complémentaires
    
    Lors de l'utilisation de **plusieurs formulaires sur une même page, il est nécessaire de leur attribuer un nom** à l'aide d'une balise "input" de type "hidden" dont l'attribut "name" à pour valeur "fm_form_name" et l'attribut "value" le nom que vous désirez assigner au formulaire.

    exemple:
    ```html
        <input type="hidden" name="fm_form_name" value="form-create-article">
    ```
