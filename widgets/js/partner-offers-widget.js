(function() {

    var script_tag = document.createElement('script');
    script_tag.setAttribute("type", "text/javascript");

    if (window.refOffers === undefined) {
        window.refOffers = {};
        script_tag.setAttribute("src","https://www.referrizer.com/widgets/partner-offers/form.min.js?v=" + Date.now());
    }

    (document.getElementsByTagName("head")[0] || document.documentElement).appendChild(script_tag);

})();
