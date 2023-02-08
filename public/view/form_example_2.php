<?php
// Monter le formulaire HTML dans la variable $form.
ob_start();
?>
    <form method="POST" action="">
    <input type="hidden" name="fm_form_name" value="form-example_2">
        <div>
            <label for="user_name">Nom</label>
            <input type="text" name="user_name" id="user_name" maxlength="255" required>
        </div>
        <div>
            <label for="user_age">Âge</label>
            <input type="number" name="user_age" id="user_age" min="18" required>
        </div>
<!--
        <div>
            <label for="user_mail">Mail</label>
            <input type="email" name="user_mail" id="user_mail" required>
        </div>
        <div>
            <label for="subject">Sujet</label>
            <input type="text" name="subject" id="subject" maxlength="255" required>
        </div>
        <div>
            <label for="user_message">Message</label>
            <textarea name="user_message" id="user_message" cols="30" rows="10" maxlength="3000" required></textarea>
        </div>
        <div>
            <label for="captcha">Captcha</label>
            <input fm-captcha="5" name="captcha" id="captcha" required>
        </div>
-->
        <div>
            <input type="submit" value="envoyer">
        </div>
    </form>
<?php
return ob_get_clean();