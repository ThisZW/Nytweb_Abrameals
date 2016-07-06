function filter(tag) {
    setProscon(tag);
    getFiltered();
}
function setProscon(tag) {

    var tags = $('proscons').getValue();
    var inArray = 0;

    if (tags) {
        tags = tags.split(',');

        for (var i = 0; i < tags.length; i++) {
            if (tags[i] == tag) {
                inArray = 1;
                break;
            }
        }

        if (inArray) {
            tags.splice(i, 1);
        } else
            tags.push(tag);
    } else {
        var tags = [];
        tags.push(tag);
    }

    $('proscons').setValue(tags);
}

function showHideProscons(type, form) {

    var prefix = '';

    if (typeof form != 'undefined')
        prefix = '_form';

    if (type == 'pros') {
        var container = $('hiddenProsList' + prefix);
        var switcher = $('hiddenProsListSwitcher' + prefix);
    } else {
        var container = $('hiddenConsList' + prefix);
        var switcher = $('hiddenConsListSwitcher' + prefix);
    }

    var status = container.readAttribute('hidden');

    if (status == 1) {
        container.setStyle({display: 'block'});
        container.setAttribute('hidden', '0');
        switcher.update('show less ' + type.capitalize());
    } else {
        container.setStyle({display: 'none'});
        container.setAttribute('hidden', '1');
        switcher.update('show more ' + type.capitalize());
    }
}
function updatePagerLimit(limit) {
    $('filteredReviews-page').setValue(1);
    $('filteredReviews-pagelimit').setValue(limit);
    getReviews();
}
function updatePagerPage(page) {
    $('filteredReviews-page').setValue(page);
    getReviews();
}
function updateSortType(type) {
    $('filteredReviews-type').setValue(type);
    updatePagerPage(1);
}
function updateSortArrow(dir) {
    if (typeof dir == 'undefined') {
        var dir = $('filteredReviews-dir').getValue();
    }
    var arrow = $('advancedreviews-sorting-arrow');
    var alt = '';
    var img = '';
    var path = arrow.getAttribute('src');
    path = path.substr(0, path.lastIndexOf('/') + 1);
    if (dir == 'DESC') {
        alt = 'Descend';
        img = 'sort_desc_arrow.gif';
    } else {
        dir = 'ASC';
        alt = 'Ascend';
        img = 'sort_asc_arrow.gif';
    }
    arrow.setAttribute('alt', alt + ' sorting');
    arrow.setAttribute('src', path + img);
    $('filteredReviews-dir').setValue(dir);
}
function updateSortDir() {
    if ($('filteredReviews-dir').getValue() == 'DESC') {
        updateSortArrow('ASC');
    } else {
        updateSortArrow('DESC');
    }
    updatePagerPage(1);
}

function getFiltered() {
    showAjaxLoader();
    new Ajax.Request(getFilteredAction, {
        method: 'get',
        parameters: {
            proscons: $('proscons').getValue(),
            customerId: $('filteredReviews-customer').getValue()
        },
        onSuccess: function (transport) {
            var response = transport.responseText || "no response text";
            //hideAjaxLoader();
            $('advancereviews-filters').update(response);
            setTimeout("getReviews();", 1000);
            resetPager();
            updateSortArrow();
        }
    });
}

function updatePager() {
    showAjaxLoader();

    new Ajax.Request(getUpdatePagerAction, {
        method: 'post',
        parameters: {
            page: $('filteredReviews-page').getValue(),
            limit: $('filteredReviews-pagelimit').getValue(),
            reviews: $('filteredReviews-ids').getValue(),
            product: $('filteredReviews-product').getValue()
        },
        onSuccess: function (transport) {
            var response = transport.responseText || "";
            hideAjaxLoader();
            $('advancereviews-pager-reviews').update(response);
            $('advancereviews-pager-reviews-footer').update(response);
            //getReviews();
        }
    });
}

function getReviews() {
    showAjaxLoader();
    new Ajax.Request(getFilteredReviewsAction, {
        method: 'post',
        parameters: {
            product: $('filteredReviews-product').getValue(),
            customerId: $('filteredReviews-customer').getValue(),
            reviews: $('filteredReviews-ids').getValue(),
            page: $('filteredReviews-page').getValue(),
            limit: $('filteredReviews-pagelimit').getValue(),
            type: $('filteredReviews-type').getValue(),
            dir: $('filteredReviews-dir').getValue()
        },
        onSuccess: function (transport) {
            var response = transport.responseText || "";
            //hideAjaxLoader();
            $('advancereviews-filteredReviews').update(response);
            updatePager();
        }
    });
}

function resetPager() {
    $('filteredReviews-page').setValue('1');
    $('filteredReviews-pagelimit').setValue('10');
}

function showAjaxLoader() {
    $('loading-mask').setStyle({display: 'block'});
}

function hideAjaxLoader() {
    $('loading-mask').setStyle({display: 'none'});
}

function reportAbuse(el) {
    //showAjaxLoader();
    var reqLink = el.getAttribute('link');

    new Ajax.Request(reqLink, {
        method: 'get',
        onSuccess: function (transport) {
            var response = transport.responseText || "Oops, no response from server!";
            //hideAjaxLoader();
            var notif = response.evalJSON();
            showNotif(notif.type, notif.message);
            var parent = $(el).up();
            parent.setStyle({display: 'none'});
        }
    });
}

function makeHelpful(el) {
    //showAjaxLoader();
    var reqLink = el.getAttribute('link');

    new Ajax.Request(reqLink, {
        method: 'get',
        onSuccess: function (transport) {
            var response = transport.responseText || "Oops, no response from server!";
            //hideAjaxLoader();
            var notif = response.evalJSON();
            var parent = $(el).up().up();
            parent.setStyle({display: 'none'});
            showNotif(notif.type, notif.message);
        }
    });
}

function showNotif(type, message) {
    var nContainer = $('advancedreviews-notify');
    var nType = $('notify-type');
    var nText = $('notify-text');

    nContainer.removeClassName('hidden');
    nType.setAttribute('class', type + '-msg');
    nText.innerHTML = message;
}