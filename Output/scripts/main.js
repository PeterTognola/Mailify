var Mailify_ServerName = "";
var Mailify_ContactForms = [];
var Mailify_AjaxStatus = 0;
var Mailify_CurrentData = "";
var Mailify = {
    init: function(server, callback) {
        Mailify_ServerName = server;
        callback();
    },

    request: function (id, element) { //Request said form server
        Mailify._ajaxAction("form.php?formid=" + id, "GET", null, function () { Mailify.serve(element, Mailify_CurrentData); });
    },

    serve: function (element, data) { //display on page-ish
        var form = JSON.parse(data);

        var documentAdditions = "";

        for (var id in form) {
            if (form.hasOwnProperty(id)) {
                documentAdditions += this.type(id, form[id]);
            }
        }

        document.getElementById(element).innerHTML = documentAdditions + "</form>";
    },

    type: function(key, object) {
        switch (object[0]) {
            case "Textbox":
                return "<div><label>" + object[1] + "</label><input type=\"text\" id=\"" + key + "\" name=\"" + key + "\" " + object[2] + " /></div>";
            case "Email":
                return "<div><label>" + object[1] + "</label><input type=\"email\" id=\"" + key + "\" name=\"" + key + "\" " + object[2] + " /></div>";
            case "Textarea":
                return "<div><label>" + object[1] + "</label><textarea id=\"" + key + "\" name=\"" + key + "\" " + object[2] + "></textarea></div>";
            case "Checkbox":
                return "<div><input type=\"checkbox\" id=\"" + key + "\" name=\"" + key + "\" /><label>" + object[1] + "</label></div>";
            case "FormName":
                return "<div><form class=\"mailify\" id=\"" + key + "\" name=\"" + key + "\" method=\"POST\">";
            case "Hidden":
                return "<input type=\"hidden\" id=\"" + key + "\" name=\"" + key + "\" value=\"" + object[1].toString() + "\" />";
            case "Submit":
                return "<div><input type=\"button\" id=\"" + key + "\" name=\"" + key + "\" value=\"" + object[1].toString() + "\" onclick=\"Mailify.submit('" + object[2].toString() + "')\" /></div>";
            default:
                return "";
        }
    },

    submit: function (id) { //check for errors then initiate submit
        //check 
        var data = new FormData();

        var formInfoCount = document.querySelectorAll("form#" + id + " input");

        for (var i = 0; i < formInfoCount.length; i++) {
            var element = formInfoCount[i];
            data.append(element.id, element.value);
        }

        Mailify.deliver(id, data);
    },
    
    deliver: function (id, data) { //submit to server using ajax
        return Mailify._ajaxAction("send.php?id=" + id, "POST", data, function () { Mailify.delivered(id, Mailify_CurrentData); }); //todo return custom alert box if sent.
    },

    delivered: function(id, result) {
        if (result === "true") {
            Mailify._alert("Success!", "Your message has been recieved.");
        } else {
            Mailify._alert("Ohh Dear...", "There has been an error sending your message, please try again later.");
        }
    },

    _ajaxAction: function (url, method, data, callback) {
        if (Mailify_AjaxStatus === 1) return false;
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function () {
            if (xmlhttp.readyState === 4 && xmlhttp.status === 200) {
                Mailify_CurrentData = xmlhttp.responseText;
                callback(this, Mailify_CurrentData);
            } else if (xmlhttp.status === 404) { //todo finish...
                return xmlhttp.responseText;
            }
            return xmlhttp.responseText;
        }
        xmlhttp.open(method, Mailify_ServerName + url, true);
        xmlhttp.send(data !== null ? data : "");
        return "";
    },

    _pushRequest: function(string) {
        Mailify_ContactForms.push(string);
        return string;
    },

    _alert: function(title, text) {
        alert(title + " - " + text); //todo this will do for now.
    }
};