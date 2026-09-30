window.addEventListener("load", function () {
    var is_interested_images = document.getElementsByClassName("is-interested-image");
    Array.from(is_interested_images).forEach(element => {
        element.addEventListener("click", function (event) {
            var XHR = new XMLHttpRequest();
            var property_id = event.target.getAttribute("property_id");

            XHR.addEventListener("load", remove_interested_success);
            XHR.addEventListener("error", on_error);
            XHR.open("GET", "api/toggle_interested.php?property_id=" + property_id);
            XHR.send();

            var loader = document.getElementById("loading");
            if (loader) loader.style.display = 'block';
            event.preventDefault();
        });
    });
});

var remove_interested_success = function (event) {
    var loader = document.getElementById("loading");
    if (loader) loader.style.display = 'none';

    try {
        var response = JSON.parse(event.target.responseText);
        if (response.success) {
            var property_id = response.property_id;
            var propertyCard = document.getElementsByClassName("property-id-" + property_id)[0];
            if (propertyCard) {
                propertyCard.style.display = 'none';
            }
        }
    } catch (e) {
        console.error("Invalid response", e);
    }
};
