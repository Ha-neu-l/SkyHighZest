jQuery(document).ready(function() {
    jQuery("#edit-kk-settings").click(function() {
        jQuery(".kk-wrapper .settings input[type=text]").prop("disabled", false);
        jQuery(".kk-wrapper .settings").toggleClass("is-editing");
    });

    jQuery("#cancel-edit-kk-settings").click(function() {
        jQuery(".kk-wrapper .settings input[type=text]").prop("disabled", true);
        jQuery(".kk-wrapper .settings").toggleClass("is-editing");
    });

    jQuery("#kk-settings-form").submit(function() {
        jQuery(".kk-wrapper .settings").addClass("is-saving");
        jQuery("#save-kk-settings").prop("disabled", true);
    });
});

if (typeof getStarted !== "function") {
    function getStarted(button) {
        button.disabled = true;
        button.classList.add("loading");

        setTimeout(() => {
            button.classList.remove("loading");
            button.disabled = false;
        }, 10000);

        window.location = kkfbPluginPage.signupLink;
    }
}
