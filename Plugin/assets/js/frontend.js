var Mailify_ServerName = "";
var Mailify_ContactForms = [];
var Mailify_AjaxStatus = 0;
var Mailify_CurrentData = "";
var Mailify_reCaptchaKey = "6LeQFAgTAAAAAEpN01KtRBCjfpkDgXK-TyaVplE8"; //Site key. todo put into wp_options Mailify.
var Mailify_reCaptcha = false;
var Mailify = {
    init: function (server, callback) {
        Mailify_ServerName = server;
        callback();
    },

    request: function (id, element) {
        Mailify._ajaxAction("?MailifyForm=" + id, "GET", null, function () { Mailify.serve(element, Mailify_CurrentData); });
    },

    serve: function (element, data) {
        var form = JSON.parse(data), documentAdditions = "", formContent = form;

        for (var id in formContent) {
            if (formContent.hasOwnProperty(id)) {
                documentAdditions += this.type(id, formContent[id]);
            }
        }

        this.__(element).innerHTML = documentAdditions + "</form>";

        if (Mailify_reCaptcha) {
            window.grecaptcha.render("renderG-reCap", {
                "sitekey": Mailify_reCaptchaKey
            });
        }

        setTimeout(function () { Mailify.__("mailifyContact-form").style.height = Mailify.__("mailifyContact-form").clientHeight + (Mailify_reCaptcha ? 85 : 1) + "px"; }, 50);
    },

    type: function (key, object) {
        switch (object[0].toLowerCase()) {
            case "text":
                return "<div><span class=\"header-text\" id=\"" + key + "\">" + object[1] + "</span></div>";
            case "textbox":
                return "<div><label for=\"" + key + "\">" + object[1] + "</label><input type=\"text\" id=\"" + key + "\" name=\"" + key + "\" " + (object[2] ? "required" : "") + " /></div>";
            case "number": /* todo: dirty so remove */
                return "<div><label for=\"" + key + "\">" + object[1] + "</label><input type=\"text\" id=\"" + key + "\" name=\"" + key + "\" " + (object[2] ? "required" : "") + " onkeypress=\"return event.charCode >= 48 && event.charCode <= 57\" /></div"
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
            case "recaptcha":
                Mailify_reCaptcha = true;
                return "<div id=\"renderG-reCap\"></div>";
            case "formname":
                return "<div><form class=\"mailify\" id=\"" + key + "\" name=\"" + key + "\" method=\"POST\" onsubmit=\"return Mailify.submit('" + key + "', '" + object[3] + "');\">"
                    + "<input type=\"hidden\" id=\"sendto\" name=\"sendto\" value=\"" + object[2] + "\" />"
                    + "<input type=\"hidden\" id=\"subject\" name=\"subject\" value=\"" + object[1] + "\" />"
                    + "<label for=\"location\" style=\"display:none; height:0;\">Location </label><input type=\"text\" id=\"location\" style=\"display:none; height:0;\" name=\"location\" value=\"" + window.location.href + "\" />";
            case "hidden":
                return "<input type=\"hidden\" id=\"" + key + "\" name=\"" + key + "\" value=\"" + object[1].toString() + "\" />";
            case "submit":
                return "<div><input type=\"submit\" class=\"submit\" id=\"" + key + "\" name=\"" + key + "\" value=\"" + object[1].toString() + "\" /></div>"; //onclick=\"Mailify.submit('" + object[0].toString() + "')\"
            default:
                return "";
        }
    },

    submit: function (id, url) { //todo check for errors then initiate submit
        var data = new FormData();
        var formInfoCount = document.querySelectorAll("form#" + id + " input, form#" + id + " textarea, form#" + id + " select");

        id = formInfoCount.length;

        for (var i = 0; i < formInfoCount.length; i++) { //todo move to switch.
            var element = formInfoCount[i];
            try {
                if (element.type !== "submit" && element.type !== "hidden" && element.type !== "checkbox") { //todo move to switch
                    if (element.className === "g-recaptcha-response") {
                        data.append("g-recaptcha-response", "" + element.value);
                        break;
                    }
                    data.append("_m-" + i, element.labels[0].innerHTML + ": " + element.value);
                    console.log("_m-" + i + " - " + element.labels[0].innerHTML + ": " + element.value);
                } else if (element.type === "checkbox") {
                    data.append("_m-" + i, element.labels[0].innerHTML + ": " + (element.checked ? "Yes" : "No"));
                    console.log("_m-" + i + " - " + element.checked);
                } else if (element.type === "hidden" && element.value !== null) {
                    data.append("_m-" + element.name, element.value);
                    console.log("_m-" + element.name + " - " + element.value);
                } else if (element.type === "select-one") {
                    data.append("_m-" + element.name, element.options[element.selectedIndex].text);
                    console.log("_m-" + element.name + " - " + element.options[element.selectedIndex].text);
                }
            } catch (e) {
                console.log(e);
            }
        }

        Mailify.deliver(id, data, url);
        return false;
    },

    deliver: function (id, data, url) {
        return Mailify._ajaxAction("?MailifySubmit=true&id=" + id, "POST", data, function () { Mailify.delivered(id, Mailify_CurrentData, url); }); //todo return custom alert box if sent.
    },

    delivered: function (id, result, url) {
        if (result === "true") {
            this.__("mailifyContact-form").style.opacity = 0;
            this.__("mailifyContact-form").style.height = "100px";
            if (url !== null && url !== undefined && url.length > 2) {
                window.location = url;
            }
            setTimeout(function () {
                Mailify.__("mailifyContact-form").innerHTML = "<span>Your message has been recieved.</span>";
                Mailify.__("mailifyContact-form").style.opacity = 1;
            }, 1000);
        } else {

            Mailify._alert("Ohh Dear...", result === undefined || result === null ? "There has been an error sending your message, please try again later." : result);
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
        alert(title + " - " + text);
    },

    __: function (id) {
        return document.getElementById(id) || document.querySelectorAll("." + id.replace(" ", " ."));
    }
};