jQuery(document).ready(function ($) {
    let wrapper = $("#pcs-card-wrapper");
    let button = $("#pcs-load-more");

    let page = parseInt(wrapper.attr("data-page"));
    let category = wrapper.attr("data-category");
    let posts_per_page = parseInt(wrapper.attr("data-posts-per-page"));
    let skip = parseInt(wrapper.attr("data-skip"));

    button.on("click", function () {
        page++;
        $.ajax({
            url: pcs_ajax.ajax_url,
            type: "POST",
            data: {
                action: "pcs_load_more",
                page: page,
                posts_per_page: posts_per_page,
                category: category,
                skip: skip
            },
            beforeSend: function () {
                button.text("Loading...");
            },
            success: function (data) {
                if ($.trim(data)) {
                    wrapper.append(data);
                    button.text("Load More");
                } else {
                    button.text("No More Posts").prop("disabled", true);
                }
            }
        });
    });
});
