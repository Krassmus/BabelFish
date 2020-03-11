jQuery(function () {
    jQuery(document).on("click", ".translatorfish > a", function (event) {
        let id = jQuery(this).parent().data("id");
        let item_type = jQuery(this).parent().data("item_type");
        let container = jQuery(this).parent().parent().find(".formatted-content");
        if (container.data("other_html")) {
            let html = container.html();
            container.html(container.data("other_html"));
            container.data("other_html", html);
            return false;
        }
        jQuery.ajax({
            "url": STUDIP.ABSOLUTE_URI_STUDIP + "plugins.php/babelfish/translator/text",
            "data": {
                "id": id,
                "item_type": item_type
            },
            "dataType": "json",
            "success": function (translation) {
                container.data("other_html", container.html());
                container.html(translation.html);
            }
        });
        return false;
    });
});
jQuery(document).on('studip-ready', function() {
    function getUrlVars() {
        var vars = {};
        var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
            vars[key] = value;
        });
        return vars;
    }

    //News:
    jQuery("article[data-visiturl]").each(function () {
        if (jQuery(this).find("article .translatorfish").length === 0) {
            let news_id = jQuery(this).attr("id");
            let icon = jQuery("<div class='translatorfish' data-id='" + news_id + "' data-item_type='news'><a href='#' title='Babelfish: Show translation'></a></div>");
            jQuery(this).find("article").prepend(icon);
        }
    });
    //Messages:
    jQuery(".message_body").each(function () {
        if (jQuery(this).find(".translatorfish").length === 0) {
            let message_id = jQuery(this).parent().find("#message_metadata").data("message_id");
            let icon = jQuery("<div class='translatorfish' data-id='" + message_id + "' data-item_type='message'><a href='#' title='Babelfish: Show translation'></a></div>");
            jQuery(this).prepend(icon);
        }
    });
    //Wiki:
    jQuery("#wiki article#main_content > section").each(function () {
        if (jQuery(this).find(".translatorfish").length === 0) {
            let url_params = getUrlVars();
            let wiki_id = window.STUDIP.URLHelper.parameters.cid + "_" + url_params['keyword'];
            let icon = jQuery("<div class='translatorfish' data-id='" + wiki_id + "' data-item_type='wikipage'><a href='#' title='Babelfish: Show translation'></a></div>");
            jQuery(this).prepend(icon);
        }
    });
    //Forum:

    jQuery(".real_posting .postbody .content").each(function () {
        if (jQuery(this).find(".translatorfish").length === 0) {
            let entry_id = jQuery(this).closest(".real_posting").attr("id").split("_")[1];
            let icon = jQuery("<div class='translatorfish' data-id='" + entry_id + "' data-item_type='forumentry'><a href='#' title='Babelfish: Show translation'></a></div>");
            jQuery(this).prepend(icon);
        }
    });
});
