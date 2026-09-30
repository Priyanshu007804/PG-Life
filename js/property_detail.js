window.addEventListener("load", function () {
    const search = window.location.search;
    const params = new URLSearchParams(search);
    const property_id = params.get('property_id');

    var is_interested_image = document.getElementsByClassName("is-interested-image")[0];
    if (is_interested_image) {
        is_interested_image.addEventListener("click", function (event) {
            var XHR = new XMLHttpRequest();

            XHR.addEventListener("load", toggle_interested_success);
            XHR.addEventListener("error", on_error);
            XHR.open("GET", "api/toggle_interested.php?property_id=" + property_id);
            XHR.send();

            var loader = document.getElementById("loading");
            if (loader) loader.style.display = 'block';
            event.preventDefault();
        });
    }
});

var toggle_interested_success = function (event) {
    var loader = document.getElementById("loading");
    if (loader) loader.style.display = 'none';

    try {
        var response = JSON.parse(event.target.responseText);
        if (response.success) {
            var is_interested_image = document.getElementsByClassName("is-interested-image")[0];
            var interested_user_count = document.getElementsByClassName("interested-user-count")[0];

            if (response.is_interested) {
                is_interested_image.classList.add("fas");
                is_interested_image.classList.remove("far");
                if (interested_user_count) {
                    interested_user_count.innerHTML = parseInt(interested_user_count.innerHTML, 10) + 1;
                }
            } else {
                is_interested_image.classList.add("far");
                is_interested_image.classList.remove("fas");
                if (interested_user_count) {
                    interested_user_count.innerHTML = Math.max(0, parseInt(interested_user_count.innerHTML, 10) - 1);
                }
            }
        } else if (!response.success && !response.is_logged_in) {
            window.$("#login-modal").modal("show");
        }
    } catch (e) {
        console.error("Invalid response", e);
    }
};
