(function ($) {
    'use strict';

    /* rt tab active navigation */
    $(".rt-tab-nav li").on('click', 'a', function (e) {
        e.preventDefault();
        var $this = $(this),
            container = $this.parents('.rt-tab-container'),
            nav = container.children('.rt-tab-nav'),
            content = container.children(".rt-tab-content"),
            $id = $this.attr('href');
        console.log($id);
        content.hide();
        nav.find('li').removeClass('active');
        $this.parent().addClass('active');
        container.find($id).show();
    });

    if ($(".rt-select2").length && $.fn.select2) {
        $(".rt-select2").select2({dropdownAutoWidth: true});
    }

    var postType = jQuery("#rc-sc-post-type").val();
    rtTgpFilter();
    detailLinkEffect();
    thpShowHideScMeta();
    renderTpgPreview();
    $("#rttpg_meta")
        .on('change', 'select,input', function () {
            renderTpgPreview();
        })
        .on("input propertychange", function () {
            renderTpgPreview();
        });
    var colorSlt = $("#rttpg_meta .rt-color");
    if (colorSlt.length && $.fn.wpColorPicker) {
        var cOptions = {
            defaultColor: false,
            change: function (event, ui) {
                renderTpgPreview();
            },
            clear: function () {
                renderTpgPreview();
            },
            hide: true,
            palettes: true
        };
        colorSlt.wpColorPicker(cOptions);
    }

    $(document).on('change', '#post_filter input[type=checkbox]', function () {
        var id = $(this).val();
        if (id === 'tpg_taxonomy') {
            if (this.checked) {
                rtTPGTaxonomyListByPostType(postType, $(this));
            } else {
                $('.rt-tpg-filter.taxonomy > .taxonomy-field').hide('slow').html('');
                $('.rt-tpg-filter.taxonomy > .rt-tpg-filter-item .term-filter-holder').hide('slow').html('');
                $('.rt-tpg-filter.taxonomy > .rt-tpg-filter-item .term-filter-item-relation').hide('slow');
            }
        }
        if (this.checked) {
            $(".rt-tpg-filter." + id).show('slow');
        } else {
            $(".rt-tpg-filter." + id).hide('slow');
        }

    });

    $(document).on('change', '#post-taxonomy input[type=checkbox]', function () {
        thpShowHideScMeta();
        rtTPGTermListByTaxonomy($(this));
    });

    $(document).on('change', "#rt-tpg-pagination", function () {
        if (this.checked) {
            $(".rt-field-wrapper.posts-per-page").show();
        } else {
            $(".rt-field-wrapper.posts-per-page").hide();
        }
    });

    $(document).on('change', "#rt-feature-image", function () {
        if (this.checked) {
            $(".rt-field-wrapper.feature-image-options").hide();
        } else {
            $(".rt-field-wrapper.feature-image-options").show();
        }
    });

    $("#rt-tpg-sc-layout").on("change", function (e) {
        thpShowHideScMeta();
    });

    $("#rc-sc-post-type").on("change", function (e) {
        postType = $(this).select2("val");
        if (postType) {
            rtTPGIsotopeFilter($(this));
            $('#post_filter input[type=checkbox]').each(function () {
                $(this).prop('checked', false);
            });
            $(".rt-tpg-filter.taxonomy > .taxonomy-field").html('');
            $(".rt-tpg-filter.taxonomy > .rt-tpg-filter-item .term-filter-item-container").remove();
            $(".rt-tpg-filter.hidden").hide();
            $(".rt-field-wrapper.term-filter-item-relation ").hide();
        }
    });

    $("#link_to_detail_page_holder").on("click", "input[type='radio']", function () {
        detailLinkEffect();
    });

    function detailLinkEffect() {
        var detailPageLink = $("#link_to_detail_page_holder input[name='link_to_detail_page']:checked").val();
        if (detailPageLink === "yes") {
            $(".rt-field-wrapper.tpg-link-target").show();
        } else {
            $(".rt-field-wrapper.tpg-link-target").hide();
        }
    }


    function renderTpgPreview() {
        if ($("#rttpg_meta").length) {
            var data = $("#rttpg_meta").find('input[name],select[name],textarea[name]').serialize(),
                container = $("#tpg-preview-container").find('.rt-tpg-container'),
                loader = container.find(".rt-content-loader");
            // Add Shortcode ID
            data = data + '&' + $.param({'sc_id': $('#post_ID').val() || 0});
            $(".rt-response")
                .addClass('loading')
                .html('<span>Loading...</span>');
            tpgAjaxCall(null, 'tpgPreviewAjaxCall', data, function (data) {
                if (!data.error) {
                    $("#tpg-preview-container").html(data.data);
                    initTpg();
                    loader.find('.rt-loading-overlay, .rt-loading').remove();
                    loader.removeClass('tpg-pre-loader');
                }
                $(".rt-response").removeClass('loading').html('');
            });
        }
    }

    function tpgAjaxCall(element, action, arg, handle) {
        var data;
        if (action) data = "action=" + action;
        if (arg) data = arg + "&action=" + action;
        if (arg && !action) data = arg;

        var n = data.search(rttpg.nonceID);
        if (n < 0) {
            data = data + "&rttpg_nonce=" + rttpg.nonce;
        }
        $.ajax({
            type: "post",
            url: rttpg.ajaxurl,
            data: data,
            beforeSend: function () {
                if (element) {
                    $("<span class='rt-loading'> </span>").insertAfter(element);
                }
            },
            success: function (data) {
                if (element) {
                    element.next(".rt-loading").remove();
                }
                handle(data);
            }
        });
    }


    function rtTPGTaxonomyListByPostType(postType, $this) {

        var arg = "post_type=" + postType;
        tpgAjaxCall($this, 'rtTPGTaxonomyListByPostType', arg, function (data) {
            //console.log(data);
            if (data.error) {
                alert(data.msg);
            } else {
                jQuery('.rt-tpg-filter.taxonomy > .taxonomy-field').html(data.data).show('slow');
            }
        });
    }

    function rtTPGTermListByTaxonomy($this) {
        var term = $this.val();
        var targetHolder = jQuery('.rt-tpg-filter.taxonomy').children('.rt-tpg-filter-item').children('.rt-field-wrapper').children('.term-filter-holder');
        var target = targetHolder.children('.term-filter-item-container.' + term);
        if ($this.is(':checked')) {
            var arg = "taxonomy=" + $this.val();
            var bindElement = $this;
            tpgAjaxCall(bindElement, 'rtTPGTermListByTaxonomy', arg, function (data) {
                //console.log(data);
                if (data.error) {
                    alert(data.msg);
                } else {
                    targetHolder.show();
                    jQuery(data.data).prependTo(targetHolder).fadeIn('slow');
                    tgpLiveReloadScript();
                }
            });
        } else {
            target.hide('slow').html('').remove();
        }

        var termLength = jQuery('input[name="tpg_taxonomy[]"]:checked').length;
        if (termLength > 1) {
            jQuery('.rt-field-wrapper.term-filter-item-relation ').show('slow');
        } else {
            jQuery('.rt-field-wrapper.term-filter-item-relation ').hide('slow');
        }
    }


    function tgpLiveReloadScript() {
        $("select.rt-select2").select2({dropdownAutoWidth: true});
    }

    $("#rt-tpg-settings-form").on('submit', function (e) {
        e.preventDefault();
        var form = $(this),
            response_wrap = form.next('.rt-response'),
            arg = form.serialize(),
            bindElement = form.find('.rtSaveButton');
        response_wrap.hide();
        tpgAjaxCall(bindElement, 'rtTPGSettings', arg, function (data) {
            if (!data.error) {
                response_wrap.removeClass('error').addClass('success');
                response_wrap.show('slow').text(data.msg);
            } else {
                response_wrap.addClass('error').removeClass('success');
                response_wrap.show('slow').text(data.msg);
            }
        });
    });


    function rtTPGIsotopeFilter($this) {
        var arg = "post_type=" + $this.val();
        var bindElement = $this;
        var target = jQuery('.rt-field-wrapper.sc-isotope-filter .field > select');
        tpgAjaxCall(bindElement, 'rtTPGIsotopeFilter', arg, function (data) {
            if (data.error) {
                alert(data.msg);
            } else {
                target.html(data.data);
                tgpLiveReloadScript();
            }
        });
    }

    function rtTgpFilter() {
        $("#post_filter input[type=checkbox]:checked").each(function () {
            var id = $(this).val();
            $(".rt-tpg-filter." + id).show();
        });

        $("#post-taxonomy input[type=checkbox]:checked").each(function () {
            var id = $(this).val();
            $(".filter-item." + id).show();
        });

    }


    function thpShowHideScMeta() {

        var layout = $("#rt-tpg-sc-layout").val();
        if (layout === 'isotope1') {
            $(".rt-field-wrapper.pagination, .rt-field-wrapper.posts-per-page").hide();
            $(".rt-field-wrapper.sc-isotope-filter").show();
        } else {
            $(".rt-field-wrapper.pagination").show();
            $(".rt-field-wrapper.sc-isotope-filter").hide();
            var pagination = $("#rt-tpg-pagination").is(':checked');
            if (pagination) {
                $(".rt-field-wrapper.posts-per-page").show();
            } else {
                $(".rt-field-wrapper.posts-per-page").hide();
            }
        }
        if (layout === 'layout2' || layout === 'layout3') {
            $('.holder-layout2-image-column').show();
        } else {
            $('.holder-layout2-image-column').hide();
        }
        if ($("#post-taxonomy input[name='tpg_taxonomy[]']").is(":checked")) {
            $(".rt-tpg-filter-item.term-filter-item").show();
        } else {
            $(".rt-tpg-filter-item.term-filter-item").hide();
        }


        if ($("#rt-feature-image").is(':checked')) {
            $(".rt-field-wrapper.feature-image-options").hide();
        } else {
            $(".rt-field-wrapper.feature-image-options").show();
        }

    }

})(jQuery);


