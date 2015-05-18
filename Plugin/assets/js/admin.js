var fcContainer = "form_creator_container", fcRun = true, fcInputAttr = "class=\"regular-text\"", fcLabelAttr = "class=\"stuff\"";

function _(id) {
    return document.getElementById(id) || document.getElementsByName(id) || document.getElementsByClassName(id);
}

var fc = {
    init: function () {
        if (!_(fcContainer)) {
            fcRun = false;
            return;
        }

        _(fcContainer).addEventListener("click", fc.editObjDelete);
    },

    object: function (obj, name, required) {
        var details = "";
        if (obj && name) {
            details = "<input type=\"hidden\" value=\"" + obj + "\" class=\"detail_obj\" /><input type=\"hidden\" value=\"" + (name.length < 1 ? "_" : name) + "\" class=\"detail_name\" /><input type=\"hidden\" value=\"" + required + "\" class=\"detail_required\" />";
        }
        switch (obj) {
            case "text":
                return details + "<span " + fcInputAttr + ">" + name + "</span>";
            case "textbox":
                return details + "<label " + fcLabelAttr + ">" + name + "</label><input " + fcInputAttr + " type=\"text\" />";
            case "email":
                return details + "<label " + fcLabelAttr + ">" + name + "</label><input " + fcInputAttr + " type=\"email\" />";
            case "textarea":
                return details + "<label " + fcLabelAttr + ">" + name + "</label><textarea " + fcInputAttr + "></textarea>";
            case "checkbox":
                return details + "<input " + fcInputAttr + " type=\"checkbox\" /><label " + fcLabelAttr + ">" + name + "</label>";
            case "dropbox":
                return details + "[Dropbox]";
            case "recaptcha":
                return details + "[reCaptcha will appear here]";
            case "submit":
                return details + "<input " + fcInputAttr + " type=\"submit\" value=" + name + " />";
            case "hidden":
                return details + "<input type=\"hidden\" value=\"" + name + "\" />";
        }
        return "<span>Error: " + name + "</span>";
    },

    createObj: function () {
        if (fcRun === false) return;

        fc.addObj(fc.object(_("objects").options[_("objects").selectedIndex].value, _("label").value, _("required").checked));
    },

    addObj: function (object) {
        var container = _(fcContainer);
        container.innerHTML += "<div class=\"fcFormObj\">" + fc.editObjPane() + object + "</div>";
    },

    editObjPane: function () {
        return "<div class=\"editArea\"><a onclick=\"\" class=\"editArea_delete\" href=\"#\">Delete</a></div>";
    },

    editObjDelete: function (event) {
        var element = event.target || event.srcElement;

        if (element.className === "editArea_delete") {
            element.parentNode.parentNode.parentNode.removeChild(element.parentNode.parentNode);
        }
    },

    exportObj: function () {
        var formPart = document.querySelectorAll("#" + fcContainer + " > .fcFormObj");
        var formParts = {};

        formParts["fcForm" + Math.floor(Math.random() * 9) + 1] = ["formname", _("formdescription").value, _("formsendto").value, _("formforwardto").value];

        for (var i = 0; i < formPart.length; i++) {
            var id = i + "-" + Math.floor(Math.random() * (100000000 - 1000)) + 1000;

            while (formParts[id]) {
                id = i + "-" + Math.floor(Math.random() * (100000000 - 1000)) + 1000;
            }

            console.log(formPart.length + " - " + i);

            formParts[id] = [formPart[i].querySelector(".detail_obj").value, formPart[i].querySelector(".detail_name").value, formPart[i].querySelector(".detail_required").value];
        }

        //formParts.forEach(function(obj) {
        //    anotherParts.unshift(obj);
        //});

        //var a = Object.keys(formParts).sort().reverse();

        //for (var o = 0; o < a.length; o++) {
        //    anotherParts[a[o]] = formParts[a[o]];
        //}

        //document.body.innerHTML = "<textarea class=\"full\">" + JSON.stringify(formParts) + "</textarea>";
        console.log(formParts);
        _("formcode").innerHTML = JSON.stringify(formParts);
    },

    switchEle: function () {
        switch (_("objects").options[_("objects").selectedIndex].value) {
            case "dropbox":
                _("plchlder-text").innerHTML = "<textarea id=\"label\" name=\"label\">placeholder;example text;</textarea>";
                break;
            default:
                _("plchlder-text").innerHTML = "<input type=\"text\" placeholder=\"label\" id=\"label\" name=\"label\" />";
                break;
        }
    }
};