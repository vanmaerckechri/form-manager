<?php
define('LANG', 'fr');
require_once  __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'bridge' . DIRECTORY_SEPARATOR . 'bridge_form_manager_example.php';

?>

<!DOCTYPE html>
<html lang="<?=LANG?>">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FormManager - Exemple</title>
</head>
<body>
    <main>
        <h1>FormManager - Exemple</h1>
        <?php
            // Afficher le formulaire HTML.
            echo $form->get_form();
            echo $form2->get_form();
        ?>
    </main>
    <script src="public/js/form_manager.js"></script>
    <?php
        $form->load_js('bridge_form_manager_example.php');
        $form2->load_js('bridge_form_manager_example.php');
    ?> 
</body>
</html>