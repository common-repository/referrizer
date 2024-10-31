(function() {

    var script_tag = document.createElement('script');
    script_tag.setAttribute("type", "text/javascript");

    if (window.refReviews === undefined) {
        window.refReviews = {};
        script_tag.setAttribute("src", "https://www.referrizer.com/widgets/reviews/form.min.js?v=" + Date.now());
    }

    (document.getElementsByTagName("head")[0] || document.documentElement).appendChild(script_tag);
})();
