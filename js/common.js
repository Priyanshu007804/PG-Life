window.addEventListener("load", function () {
    var signup_form = document.getElementById("signup-form");
    if (signup_form) {
        signup_form.addEventListener("submit", function (event) {
            var XHR = new XMLHttpRequest();
            var form_data = new FormData(signup_form);

            XHR.addEventListener("load", signup_success);
            XHR.addEventListener("error", on_error);
            XHR.open("POST", "api/signup_submit.php");
            XHR.send(form_data);

            var loader = document.getElementById("loading");
            if (loader) loader.style.display = 'block';
            event.preventDefault();
        });
    }

    var login_form = document.getElementById("login-form");
    if (login_form) {
        login_form.addEventListener("submit", function (event) {
            var XHR = new XMLHttpRequest();
            var form_data = new FormData(login_form);

            XHR.addEventListener("load", login_success);
            XHR.addEventListener("error", on_error);
            XHR.open("POST", "api/login_submit.php");
            XHR.send(form_data);

            var loader = document.getElementById("loading");
            if (loader) loader.style.display = 'block';
            event.preventDefault();
        });
    }
});

var signup_success = function (event) {
    var loader = document.getElementById("loading");
    if (loader) loader.style.display = 'none';

    try {
        var response = JSON.parse(event.target.responseText);
        if (response.success) {
            alert(response.message);
            window.location.href = "index.php";
        } else {
            alert(response.message);
        }
    } catch (e) {
        alert("Unexpected response from server.");
    }
};

var login_success = function (event) {
    var loader = document.getElementById("loading");
    if (loader) loader.style.display = 'none';

    try {
        var response = JSON.parse(event.target.responseText);
        if (response.success) {
            location.reload();
        } else {
            alert(response.message);
        }
    } catch (e) {
        alert("Unexpected response from server.");
    }
};

var on_error = function (event) {
    var loader = document.getElementById("loading");
    if (loader) loader.style.display = 'none';

    alert('Oops! Something went wrong.');
};
