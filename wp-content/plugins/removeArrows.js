$(document).ready(function() {
    $(".woocommerce-cart").html(function(i, val) {
        return val.replace(" ?", "");
    });
    $(".woocommerce-cart").html(function(i, val) {
        return val.replace("? ", "");
    });
    alert()
});
