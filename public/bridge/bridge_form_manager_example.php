<?php
require_once  __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'private' . DIRECTORY_SEPARATOR . 'form_manager' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'form_manager.php';
$form = require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR .  'view' . DIRECTORY_SEPARATOR . 'form_example.php';
$form2 = require_once __DIR__ . DIRECTORY_SEPARATOR . '..'  . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'form_example_2.php';

// Charger le formulaire avec le gestionnaire de formulaire "FormManager".
$form = new FormManager($form);
$form2 = new FormManager($form2);

/* 
    Récupérer les potentielles entrées utilisateurs.

    null: formulaire non envoyé
    tableau: formulaire envoyé
        - vide: au moins une entrée utilisateur n'est pas valide
        - rempli: toutes les entrées utilisateur sont valides
*/
$formPost = $form->get_postResult();
$form2Post = $form2->get_postResult();
$is_succes = false;

if ($formPost !== null)
{
    if (!empty($formPost))
    {
        $is_succes = true;
    }
    echo json_encode(["form" => $form->get_form(), "is_succes" => $is_succes]);
}
elseif ($form2Post !== null)
{
    if (!empty($form2Post))
    {
        $is_succes = true;
    }
    echo json_encode(["form" => $form2->get_form(), "is_succes" => $is_succes]);
}




