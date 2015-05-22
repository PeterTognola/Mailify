var Mailify_ServerName = "";
var Mailify_ContactForms = [];
var Mailify_AjaxStatus = 0;
var Mailify_CurrentData = "";
var Mailify = {
    init: function (server, callback) {
        Mailify_ServerName = server;
        callback();
    },

    request: function (id, element) { //Request said form server
        //Mailify._ajaxAction("form.php?formid=" + id, "GET", null, function () { Mailify.serve(element, Mailify_CurrentData); });
        Mailify._ajaxAction("?MailifyForm=" + id, "GET", null, function () { Mailify.serve(element, Mailify_CurrentData); });
    },

    serve: function (element, data) { //display on page-ish
        var form = JSON.parse(data);

        var documentAdditions = "";

        //var formContent = Object.keys(form).map(function (k) { return form[k] });
        //var formContent = JSON.stringify(eval("(" + form + ")"));
        var formContent = form;

        for (var id in formContent) {
            if (formContent.hasOwnProperty(id)) {
                documentAdditions += this.type(id, formContent[id]);
            }
        }

        this.__(element).innerHTML = documentAdditions + "</form>";

        setTimeout(function () { Mailify.__("mailifyContact-form").style.height = Mailify.__("mailifyContact-form").clientHeight + "px"; }, 150);
    },

    type: function (key, object) {
        switch (object[0].toLowerCase()) {
            case "text":
                return "<div><span id=\"" + key + "\">" + object[1] + "</span></div>";
            case "textbox":
                return "<div><label for=\"" + key + "\">" + object[1] + "</label><input type=\"text\" id=\"" + key + "\" name=\"" + key + "\" " + (object[2] ? "required" : "") + " /></div>";
            case "email":
                return "<div><label for=\"" + key + "\">" + object[1] + "</label><input type=\"email\" id=\"" + key + "\" name=\"" + key + "\" " + (object[2] ? "required" : "") + " /></div>";
            case "textarea":
                return "<div><label for=\"" + key + "\">" + object[1] + "</label><textarea id=\"" + key + "\" name=\"" + key + "\" " + (object[2] ? "required" : "") + "></textarea></div>";
            case "checkbox":
                return "<div><input type=\"checkbox\" id=\"" + key + "\" name=\"" + key + "\" " + (object[2] === "true" ? "checked" : "") + " /><label for=\"" + key + "\">" + object[1] + "</label></div>";
            case "dropbox":
                var options = "";
                var optionList = object[3].split(";");
                for (var i = 0; i < optionList.length; i++) {
                    options += "<option value=\"" + optionList[i].toLowerCase() + "\">" + optionList[i] + "</option>";
                }
                return "<div><label for=\"" + key + "\">" + object[1] + "</label><select id=\"" + key + "\" name=\"" + key + "\">" + options + "</select></div>";
            case "formname":

                return "<div><form class=\"mailify\" id=\"" + key + "\" name=\"" + key + "\" method=\"POST\" onsubmit=\"return Mailify.submit('" + key + "', '" + object[3] + "');\">"
                    + "<input type=\"hidden\" id=\"sendto\" name=\"sendto\" value=\"" + object[2] + "\" />"
                    + "<input type=\"hidden\" id=\"subject\" name=\"subject\" value=\"" + object[1] + "\" />"
                    + "<label for=\"location\" style=\"display:none; height:0;\">Location: </label><input type=\"text\" id=\"location\" style=\"display:none; height:0;\" name=\"location\" value=\"" + window.location.href + "\" />";
            case "hidden":
                return "<input type=\"hidden\" id=\"" + key + "\" name=\"" + key + "\" value=\"" + object[1].toString() + "\" />";
            case "submit":
                return "<div><input type=\"submit\" class=\"submit\" id=\"" + key + "\" name=\"" + key + "\" value=\"" + object[1].toString() + "\" /></div>"; //onclick=\"Mailify.submit('" + object[0].toString() + "')\"
            default:
                return "";
        }
    },

    submit: function (id, url) { //check for errors then initiate submit
        //check
        var data = new FormData();

        var formInfoCount = document.querySelectorAll("form#" + id + " input, form#" + id + " textarea");

        id = formInfoCount.length;

        for (var i = 0; i < formInfoCount.length; i++) {
            var element = formInfoCount[i];
            if (element.type !== "submit" && element.type !== "hidden") { //todo move to switch
                data.append("_m-" + i, element.labels[0].innerHTML + ": " + element.value); console.log("_m-" + i + " - " + element.labels[0].innerHTML + ": " + element.value);
            } else if (element.type === "hidden" && element.value !== null) {
                data.append("_m-" + element.name, element.value); console.log("_m-" + element.name + " - " + element.value);
            }
        }

        Mailify.deliver(id, data, url);
        return false;
    },

    deliver: function (id, data, url) { //submit to server using ajax
        return Mailify._ajaxAction("?MailifySubmit=true&id=" + id, "POST", data, function () { Mailify.delivered(id, Mailify_CurrentData, url); }); //todo return custom alert box if sent.
    },

    delivered: function (id, result, url) {
        if (result === "true") {
            //Mailify._alert("Success!", "Your message has been recieved.");
            
            this.__("mailifyContact-form").style.opacity = 0;
            this.__("mailifyContact-form").style.height = "100px";
            if (url !== null && url !== undefined) {
                window.location = url;
            }
            setTimeout(function () {
                Mailify.__("mailifyContact-form").innerHTML = "<span>Your message has been recieved.</span>";
                Mailify.__("mailifyContact-form").style.opacity = 1;
            }, 1000);
        } else {
            Mailify._alert("Ohh Dear...", "There has been an error sending your message, please try again later.");
        }
    },

    _ajaxAction: function (url, method, data, callback) {
        if (Mailify_AjaxStatus === 1) return false;
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function () {
            if (xmlhttp.readyState === 4 && xmlhttp.status === 200) {
                console.log(xmlhttp.responseText);

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

    _pushRequest: function (string) {
        Mailify_ContactForms.push(string);
        return string;
    },

    _alert: function (title, text) {
        alert(title + " - " + text); //todo this will do for now.
    },

    __: function(id) {
        return document.getElementById(id) || document.querySelectorAll("." + id.replace(" ", " ."));
    }
};