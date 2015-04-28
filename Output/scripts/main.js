var Mailify_ServerName = "";
var Mailify_ContactForms = [];
var Mailify_AjaxStatus = 0;
var Mailify = {
    init: function(server, callback) {
        Mailify_ServerName = server;
        callback();
    },

    request: function(id, element) { //Request said form server
        Mailify.serve(element, Mailify._pushRequest("/\/"
            + id
            + "/\/"
            + Mailify._ajaxAction("form.php?formid=" + id, "GET")));
    },

    serve: function(element, id) { //display on page
        
    },

    submit: function() { //check for errors then initiate submit
        
    },
    
    deliver: function() { //submit to server using ajax
        
    },

    _ajaxAction: function (url, method, data) {
        if (Mailify_AjaxStatus === 1) return false;
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function () {
            if (xmlhttp.readyState === 4 && xmlhttp.status === 200) {
                return xmlhttp.responseText;
            } else if (xmlhttp.status === 404) { //todo finish...
                return xmlhttp.responseText;
            }
        }
        xmlhttp.open(method, Mailify_ServerName + url, true);
        xmlhttp.send(data !== null ? data : "");

        return 0; //todo finish the function to return correct data...
    },

    _pushRequest: function(string) {
        Mailify_ContactForms.push(string);
        return string;
    }
};