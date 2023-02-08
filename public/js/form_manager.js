
var CVM = CVM || {};

(function()
{
	"use strict";

	CVM.FormManager = function(bridgeFilename, form, fieldsCfg, messages)
	{
        this.bridgeFilename = bridgeFilename;
        this.form = form;
        this.fieldsCfg = fieldsCfg;
        this.messages = messages;

        this.init();
	};

    CVM.FormManager.prototype.init = function()
	{
        for (var key in this.fieldsCfg)
        {
            var fields = document.querySelectorAll('[name="' + key + '"]');
            for (var i = fields.length - 1; i >= 0; i--)
            {
                fields[i].addEventListener('input', this.check_field.bind(this, this.fieldsCfg[key]));
            }
        }
        this.form.addEventListener('submit', this.submit_form.bind(this));
	};

    CVM.FormManager.prototype.check_field = function(fieldCfg, e)
	{
        fieldCfg['userInput'] = e.target.value;
        // INPUT:
        if (fieldCfg['tag'] == 'input')
        {
            // TYPE:
            if (fieldCfg['attributes']['type'])
            {
                // NUMBER:
                if (fieldCfg['attributes']['type'] == 'number')
                {
                    fieldCfg['userInput'] = e.target.value == '' || isNaN(e.target.value) ? 'T' : e.target.value;
                    this.check_error(fieldCfg, this.check_number.bind(this));
                }
                // EMAIL:
                else if (fieldCfg['attributes']['type'] == 'email')
                {
                    this.check_error(fieldCfg, this.check_mail.bind(this));
                }
                // AUTRES:
                else
                {
                    this.check_error(fieldCfg);
                }
            }
            // INPUTS NON CONVENTIONNELS (ex.: captcha):
            else
            {
                this.check_error(fieldCfg);
            }
        }
        // TEXTAREA, SELECT, etc.
        else
        {
            this.check_error(fieldCfg);
        }
        this.display_errorMessage(e.target, fieldCfg);
	};

    CVM.FormManager.prototype.check_error = function(fieldCfg, check_callback)
	{
        var userInput = fieldCfg['userInput'];

        fieldCfg['errors'] = [];
		// Vérifier que les règles universelles du champ sont respectées.
		for (var key in fieldCfg['attributes'])
		{

			if ((key == 'required' && userInput == '')
				|| (key == 'minlength' && userInput && fieldCfg['attributes'][key] > userInput.length)
				|| (key == 'maxlength' && userInput && fieldCfg['attributes'][key] < userInput.length)
				|| (key == 'min' && userInput && parseFloat(fieldCfg['attributes'][key]) > parseFloat(userInput))
                || (key == 'max' && userInput && parseFloat(fieldCfg['attributes'][key]) < parseFloat(userInput))
				|| (key == 'pattern' && userInput && !(new RegExp(fieldCfg['attributes'][key])).test(userInput)))
			{

				var error = !Array.isArray(fieldCfg['attributes'][key]) ? [fieldCfg['attributes'][key]] : fieldCfg['attributes'][key];
                // Gérer le cas particulier des patterns pour la correspondance des messages d'erreur.
                if (key == "pattern")
                {
                    fieldCfg['errors']['error_pattern_' + fieldCfg['attributes'][key]] = error;
                }
                // Tous les autres cas...
                else
                {
                    fieldCfg['errors']['error_' + key] = error;
                }
			}
		}

		// Vérifier que les règles spécifiques au type de champ sont respectées.
		if (typeof check_callback != 'undefined' && userInput)
		{
			var error = check_callback(fieldCfg);
			if (error !== null)
			{
				fieldCfg['errors'][error] = [userInput];
			}
		}
	};

    CVM.FormManager.prototype.check_number = function(fieldCfg)
	{
        if (isNaN(fieldCfg['userInput']) === true)
        {
            return 'error_number';
        }
        return null;
	};

    CVM.FormManager.prototype.check_mail = function(fieldCfg)
	{
        if (!(new RegExp(/^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/)).test(fieldCfg['userInput']))
        {
            return 'error_mail';
        }
        return null;
	};

    CVM.FormManager.prototype.display_errorMessage = function(domElem, fieldCfg)
	{
        // Récupérer l'id de la liste des messages d'erreur pour ce champ.
        var ulId = 'fm-field-error-' + fieldCfg['attributes']['name'];
        // S'ils existent, effacer la liste des messages affichés précédemment.
        var ul = this.form.querySelector("#" + ulId);
        if (ul != null)
        {
            ul.remove();
        }
        // En cas d'erreur, ajouter les attributs d'accessibilité et afficher le(s) message(s).
        if (fieldCfg['errors'] && Object.keys(fieldCfg['errors']).length > 0)
        {
            // Ajouter les attributs d'accessibilité.
            domElem.setAttribute('aria-invalid', 'true');
            domElem.setAttribute('aria-describedby', 'fm-field-error-' + fieldCfg['attributes']['name']);
            // Créer la nouvelle liste de messages.
            ul = document.createElement('ul');
            ul.setAttribute('id', ulId);
            for (var messageKey in fieldCfg['errors'])
            {
                var er = this.get_message(messageKey, fieldCfg['errors'][messageKey]);
                var li = document.createElement('li');
                li.textContent = er;
                li.setAttribute('class', 'fm-field-error');
                ul.appendChild(li);
            }
            // Placer le(s) message(s) d'erreur sous le champ concerné.
            domElem.parentNode.insertBefore(ul, domElem.nextSibling);
        }
	};

    CVM.FormManager.prototype.get_message = function(messageKey, messageValues)
	{
        var lang = document.documentElement.lang;
        var message = this.messages[messageKey][lang] ? this.messages[messageKey][lang] : null;
        for (var i = messageValues.length - 1; i >= 0; i--)
        {
            message = message.replace("%" + i + "%", messageValues[i]);
        }
        return message;
    };

    CVM.FormManager.prototype.submit_form = function(e)
	{
        e.preventDefault();
        // Récupérer les entrées utlisateur du formulaire.
        this.data = new FormData(this.form);
        // Envoyer les entrées utilisateur au serveur.
        var httpRequest = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("Microsoft.XMLHTTP");
		httpRequest.addEventListener('readystatechange', this.listen_server.bind(this, httpRequest));
		httpRequest.open(this.form.getAttribute("method"), 'public\\bridge\\' + this.bridgeFilename, true);
		httpRequest.setRequestHeader('X-Requested-With', 'xmlhttprequest');
		httpRequest.send(this.data);
    };

    CVM.FormManager.prototype.listen_server = function(httpRequest)
	{
        // Si la requête est un succès:
		if (httpRequest.readyState === 4 && httpRequest.status === 200)
		{
            // Récupérer le formulaire et le status de la soumission.
            var result = JSON.parse(httpRequest.responseText);
            // Convertir la chaîne de caractères du formulaire en élément DOM.
            var doc = new DOMParser();
            var newForm = (doc.parseFromString(result['form'], 'text/html')).querySelector('form');
            // Remplacer le formulaire par celui provenant du serveur.
            this.form.parentNode.insertBefore(newForm, this.form.nextSibling);
            this.form.remove();
            this.form = newForm;
            // Relancer l'initialisation sur le nouveau formualire.
            this.init();
            // Afficher un message concernant le resultat de la soumission.
            if (result['is_succes'])
            {
                console.log('succès');
            }
            else
            {
                console.log('echec');
            }
		}
	};
}());
