//show action buttons when hovering
$(document).ready(function() {
    $(".action-button-wrapper")
        .on("mouseover", function () {
            $(this).addClass("active");
        })
        .on("mouseleave", function () {
            $(this).removeClass("active");
        });
});
