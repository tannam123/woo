var wcpa_functions = {};
if (typeof jQuery !== "undefined") {
    jQuery.default_image_url =
        wcpa_backend_vars.plugin_path + "assets/img/default-image.jpg";
}
jQuery(function ($) {
    var $fb = $(document.getElementById("wcpa_editor"));
    var formData = $("#wcpa_fb-editor-json").text();

    if ($fb.length) {
        var formBuilder = $fb.formBuilder({
            formData: formData,
            showActionButtons: false,
        });
    }

    $("#publish,#save-post").click(function () {
        if ($("#wcpa_fb-editor-json").length) {
            $("#wcpa_editor input").attr("disabled", "disabled");
            $("#wcpa_editor select").attr("disabled", "disabled");
            $("#wcpa_fb-editor-json").text(formBuilder.formData);
        }
    });
    document.addEventListener("fieldAdded", function () {
        $("#wcpa_fb-editor-json").text(formBuilder.formData);
    });
    document.addEventListener("fileRemoved", function () {
        $("#wcpa_fb-editor-json").text(formBuilder.formData);
    });

    /* order meta js */
    var wcpa_meta_boxes_order_items = {
        init: function () {
            $("#woocommerce-order-items").on(
                "click",
                "a.wcpa_delete-order-item",
                this.remove_item
            );
            $("#woocommerce-order-items").on(
                "change blur",
                ".wcpa_has_price",
                this.update_total_price
            );
        },
        remove_item: function (e) {
            var answer = window.confirm(
                "It will remove this item and will recalculate the price"
            );

            if (answer) {
                wcpa_meta_boxes_order_items.update_total_price.call(this);
                var el_price = parseFloat(
                    $(this).parents(".item_wcpa").find(".wcpa_has_price").val()
                );
                if (!isNaN(el_price)) {
                    wcpa_meta_boxes_order_items.set_total_price.call(
                        this,
                        -el_price
                    );
                    wcpa_meta_boxes_order_items.set_subtotal_price.call(
                        this,
                        -el_price
                    );
                }

                $(this).parents(".item_wcpa").remove();
            }

            return false;
        },
        update_total_price: function () {
            var $row = $(this).parents("tr.item");
            var original_price = 0;
            var updated_price = 0;
            $(".wcpa_has_price", $row).each(function () {
                updated_price += !isNaN(parseFloat($(this).val()))
                    ? parseFloat($(this).val())
                    : 0;
                original_price += !isNaN(parseFloat($(this).data("price")))
                    ? parseFloat($(this).data("price"))
                    : 0;
                $(this).data("price", parseFloat($(this).val()));
            });
            if (original_price - updated_price != 0) {
                wcpa_meta_boxes_order_items.set_total_price.call(
                    this,
                    parseFloat(updated_price - original_price)
                );
            }
        },
        set_total_price: function (value) {
            var $row = $(this).parents("tr.item");
            var line_total = $("input.line_total", $row);
            line_total.attr(
                "data-total",
                parseFloat(line_total.attr("data-total")) + value
            );
            $("input.quantity", $row).trigger("change");
        },
        set_subtotal_price: function (value) {
            var $row = $(this).parents("tr.item");
            var line_subtotal = $("input.line_subtotal", $row);
            line_subtotal.attr(
                "data-subtotal",
                parseFloat(line_subtotal.attr("data-subtotal")) + value
            );
            $("input.quantity", $row).trigger("change");
        },

        block: function () {
            $("#woocommerce-order-items").block({
                message: null,
                overlayCSS: {
                    background: "#fff",
                    opacity: 0.6,
                },
            });
        },

        unblock: function () {
            $("#woocommerce-order-items").unblock();
        },
    };

    wcpa_meta_boxes_order_items.init();

    $(".wcpa_g_set_tabs").on("click", "a", function (e) {
        e.preventDefault();
        $(".wcpa_tabcontent").not($(this).attr("href")).hide();
        $(".wcpa_g_set_tabs .active").removeClass("active");
        $(this).addClass("active");
        $($(this).attr("href")).show();
    });

    $("#wcpa_form_settings").on("change", "#disp_use_global", function (e) {
        e.preventDefault();
        if (this.checked) {
            $(".options_group", $(this).parents(".wcpa_tabcontent"))
                .not($(this).parents(".options_group"))
                .addClass("disable");
        } else {
            $(
                ".options_group",
                $(this).parents(".wcpa_tabcontent")
            ).removeClass("disable");
        }
    });
    $("#wcpa_form_settings").on("change", "#cont_use_global", function (e) {
        e.preventDefault();
        if (this.checked) {
            $(".options_group", $(this).parents(".wcpa_tabcontent "))
                .not($(this).parents(".options_group"))
                .addClass("disable");
        } else {
            $(
                ".options_group",
                $(this).parents(".wcpa_tabcontent ")
            ).removeClass("disable");
        }
    });

    $("#the-list").on("click", "a.wcpa_duplicate_form", function (e) {
        e.preventDefault();
        // Create the data to pass
        var data = {
            action: "wcpa_duplicate_form",
            original_id: $(this).data("postid"),
            wcpa_nonce: $(this).data("nonce"),
        };

        $.post(ajaxurl, data, function (response) {
            var location = window.location.href;
            if (location.split("?").length > 1) {
                location = location + "&wcpa_duplicated=" + response;
            } else {
                location = location + "?wcpa_duplicated=" + response;
            }
            window.location.href = location;
        });
    });

    $("#wcpa_import_form").click(function (e) {
        e.preventDefault();
        var val = $("#wcpa_import_form_data").val().trim();
        if (val == "" || val.length < 10) {
            alert("Please fill the data ");
        } else {
            var data = {
                action: "wcpa_import_form",
                val: val,
                wcpa_nonce: $("#wcpa_form_import_nonce").val(),
            };

            $.post(ajaxurl, data, function (response) {
                if (response.status) {
                    window.location.href = response.redirect;
                } else {
                    alert("Invalid Data");
                }
            });
        }
    });

    $("#product_custom_field_add .wcpa_add_btn").click(function (e) {
        e.preventDefault();
        if (
            $(
                "#product_custom_field_add input.product_custom_field_name"
            ).val() == ""
        ) {
            alert("Please fill custom field name");
            return false;
        }
        if (
            $(
                "#product_custom_field_add input.product_custom_field_value"
            ).val() == ""
        ) {
            alert("Please fill custom field default value");
            return false;
        }
        var count = $("#product_custom_fields .fields").length + 1;
        var html =
            '<div class="fields"><input type="text" name="product_custom_field_name[' +
            count +
            ']" value="' +
            $(
                "#product_custom_field_add input.product_custom_field_name"
            ).val() +
            '" >' +
            '<input type="text" name="product_custom_field_value[' +
            count +
            ']" value="' +
            $(
                "#product_custom_field_add input.product_custom_field_value"
            ).val() +
            '"/>' +
            ' <input type="submit" class="wcpa_rmv_btn" value="Remove"></div>';

        $("#product_custom_fields").append(html);
        $("#product_custom_field_add input.product_custom_field_name").val("");
        $("#product_custom_field_add input.product_custom_field_value").val("");
    });

    $("#product_custom_fields").on("click", ".wcpa_rmv_btn", function (e) {
        e.preventDefault();
        $(this).parents(".fields").remove();
    });

    $("#wcpa_settings_main").on("submit", function (e) {
        if ($("#field_option_price_format").val().indexOf("price") === -1) {
            alert(
                "Field 'Format for showing price in field options:' must include keyword 'price'"
            );
            e.preventDefault();
        }
    });

    // Extension Section
    $(
        ".wcpa_extension_container .wcpa_custom_create_extension .wcpa_extension_mime_add"
    ).on("click", function (e) {
        e.preventDefault();
        if (
            $(this).siblings('input[name="wcpa_extension_mime[0]"]').val() &&
            $(this).siblings('input[name="wcpa_extension_name[0]"]').val()
        ) {
            var extension = $(this)
                .siblings('input[name="wcpa_extension_name[0]"]')
                .val();
            var mimeType = $(this)
                .siblings('input[name="wcpa_extension_mime[0]"]')
                .val();
            var totalCount = $(
                ".wcpa_extension_container .wcpa_custom_create_extension .wcpa_add_ext_custom_container .ext_fieldWrap"
            ).length;
            totalCount++;
            var appendExtInput = '<div class="ext_fieldWrap">';
            appendExtInput +=
                '<input type="text" name="wcpa_extension_name[' +
                totalCount +
                ']" value="' +
                extension +
                '">';
            appendExtInput +=
                '<input type="text" name="wcpa_extension_mime[' +
                totalCount +
                ']" value="' +
                mimeType +
                '">';
            appendExtInput +=
                '<input type="button" class="wcpa_extension_mime_remove" value="Remove">';
            appendExtInput += "</div>";
            $(
                ".wcpa_extension_container .wcpa_custom_create_extension .wcpa_add_ext_custom_container"
            ).append(appendExtInput);
            $(this).siblings('input[name="wcpa_extension_name[0]"]').val("");
            $(this).siblings('input[name="wcpa_extension_mime[0]"]').val("");
        } else {
            alert("Extension and MIME Type cannot be empty");
        }
    });
    $(document).on(
        "click",
        ".wcpa_extension_container .wcpa_custom_create_extension .wcpa_extension_mime_remove",
        function (e) {
            e.preventDefault();
            $(this).parent().remove();
        }
    );
});