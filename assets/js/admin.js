function purchase_safecart(target) {
    var value = jQuery(target).val();
    jQuery('#safecart_link').attr('href', safecart_link + "&price=" + value);
    jQuery('#safecart_link')[0].click();
}
function submitAction(id, action) {
    showLoading('.fancybox-inner', 'Publishing Document');
    jQuery('#action_' + id).val(action);
    jQuery.ajax({
        url: adminLink+"?action=constant-content-ajax-data",
        type: "POST",
        data: jQuery('#create_' + id).serialize(),
        success: function (data) {
            if (data.url) {
                window.location.href = data.url;
                showLoading('.fancybox-inner', 'Publishing Document');
            } else {
                hideLoading('.fancybox-inner');
            }
        }
    });
}

function reviseAction(id) {
    jQuery('#revise_' + id).submit();
}

function downloadAction(id) {
    jQuery('#download_' + id).submit();
}

function activateHide(state) {
    jQuery(".hide").unbind("click");
    jQuery(".hide").click(function () {
        var item = jQuery(this).attr('hide');
        var hidestate = 'false';
        if (jQuery("#" + item + "Container .bottom").hasClass('hidden')) {
            jQuery("#" + item + "Container .bottom").removeClass('hidden');
            jQuery("#" + item + "Container .dataTable").removeClass('hidden');
            jQuery(this).html('hide');
        } else {
            hidestate = 'true';
            jQuery("#" + item + "Container .bottom").addClass('hidden');
            jQuery("#" + item + "Container .dataTable").addClass('hidden');
            jQuery(this).html('open');
        }
        jQuery.ajax({
            url: adminLink+"?action=constant-content-ajax-data",
            data: {
                hidestate: hidestate,
                hide: item
            }
        });
    });
    jQuery(".bottom").each(function () {
        if (jQuery(this).hasClass('hidden')) {
            jQuery(this).parent().find('.dataTable').addClass('hidden');
        }
    });
}

function massAction(source) {
    var action = jQuery(source).val();
    jQuery('#massActionForm input:text').each(function () {
        jQuery(this).remove();
    });
    jQuery('input.massAction:checkbox:checked').each(function () {
        var id = jQuery(this).val();
        var type = jQuery(this).attr('source');
        var title = jQuery(this).attr('title');
        jQuery('#massActionForm').append('<input type="text" name="massaction[' + action + '][' + type + '][' + id + ']" value="' + title + '" />');
    });
    if (jQuery('#massActionForm input:text').length > 0) {
        showLoading('#contentTable tbody', 'Publishing Documents');
        jQuery.ajax({
            url: adminLink+"?action=constant-content-ajax-data",
            data: jQuery('#massActionForm').serialize(),
            success: function (data) {
                jQuery('#massActionMessage').html(data.message);
                showLoading('#contentTable tbody', 'Updating Requests');
                jQuery('#contentTable').dataTable().fnReloadAjax();
            }
        });
    }
}

function massRequestAction(source) {
    var action = jQuery(source).val();
    jQuery('#massRequestActionForm input:text').each(function () {
        jQuery(this).remove();
    });
    jQuery('input.massRequestAction:checkbox:checked').each(function () {
        var id = jQuery(this).val();
        jQuery('#massRequestActionForm').append('<input type="text" name="massRequestAction[' + action + '][' + id + ']" value="' + id + '" />');
    });
    if (jQuery('#massRequestActionForm input:text').length > 0) {
        showLoading('#requestTable tbody', 'Updating Requests');
        jQuery.ajax({
            url: adminLink+"?action=constant-content-ajax-data",
            data: jQuery('#massRequestActionForm').serialize(),
            complete: function () {
                jQuery('#requestTable').dataTable().fnReloadAjax();
            }
        });
    }
}

function startRequestForm() {
    writersList = createWritersList();
    jQuery.fn.select2.amd.require(
            ['select2/data/array', 'select2/utils'],
            function (ArrayData, Utils) {
                function CustomData($element, options) {
                    CustomData.__super__.constructor.call(this, $element, options);
                }
                Utils.Extend(CustomData, ArrayData);
                CustomData.prototype.query = function (params, callback) {
                    var data = {
                        results: []
                    };
                    //params.term
                    if (params.term && params.term !== "") {
                        data.results = _.filter(writersList, function (e) {
                            return (e.text.toUpperCase().indexOf(params.term.toUpperCase()) >= 0);
                        });
                    } else {
                        data.results = writersList;
                    }
                    callback(data);
                };
                jQuery(".writers").select2({
                    placeholder: "Select a writer.",
                    dataAdapter: CustomData,
//                    containerCssClass: "hidden"
                });
                choose_request(jQuery('#chooseAuthors'));
            }
    );
    jQuery('#deadline').datetimepicker({
        dateFmt: 'Y',
        altFormat: 'Y',
        addSliderAccess: true,
        sliderAccessArgs: {
            touchonly: false
        },
        minDate: 0,
        dateFormat: 'yy-mm-dd',
        timeFormat: "hh:mm tt",
        minuteGrid: 10,
        stepMinute: 60,
        hourGrid: 6,
        showMinute: false
    });
    jQuery('#newrequest').validate({
        ignore: 'select[type=hidden]',
        rules: {
            'title': {
                'required': true,
                'minlength': 5
            },
            'type': 'required',
            'deadline': 'required',
            'authors': 'required',
            'description': {
                'required': true,
                'minlength': 25
            },
            'price': 'required',
            'wordcount': {
                'required': true,
                'digits': true,
                'min': 100
            },
            'item_count': {
                'required': true,
                'digits': true,
                'min': 1
            },
            'country': {
                required: function (element) {
                    return jQuery('#chooseAuthors option[value="targeted_request_country"]').is(':selected');
                }
            },
            'study': {
                required: function (element) {
                    return jQuery('#chooseAuthors option[value="targeted_request_study"]').is(':selected');
                }
            },
            'certfication': {
                required: function (element) {
                    return jQuery('#chooseAuthors option[value="targeted_request_certification"]').is(':selected');
                }
            },
            'categories': {
                required: function (element) {
                    return jQuery('#chooseAuthors option[value="targeted_request_category"]').is(':selected');
                }
            },
            'expert': {
                required: function (element) {
                    return jQuery('#chooseAuthors option[value="expert_request"]').is(':selected');
                }
            },
            'team': {
                required: function (element) {
                    return jQuery('#chooseAuthors option[value="private_team"]').is(':selected');
                }
            },
            'writers[]': {
                required: function (element) {
                    return jQuery('#chooseAuthors option[value="private_writer"]').is(':selected');
                }
            }
        },
        messages: {
            'country': 'Please select a country.',
            'study': 'Please select an area of study.',
            'certfication': 'Please select a certfication.',
            'categories': 'Please select a category.',
            'expert': 'Please select an expert group.',
            'team': 'Please select a team.',
            'writers[]': 'Please select a writer.'
        },
        errorPlacement: function (error, element) {
            jQuery(element).parent().append(error);
        },
        submitHandler: function (form) {
            showLoading('.fancybox-inner', 'Updating Request');
            jQuery.ajax({
                url: adminLink+"?action=constant-content-ajax-data",
                data: jQuery(form).serialize(),
                complete: function () {
                    jQuery('#defaultModal').modal('hide');
                    hideLoading('.fancybox-inner');
                    showLoading('#requestTable tbody', 'Updating Requests');
                    jQuery('#requestTable').dataTable().fnReloadAjax();
                }
            });
        }
    });
    choose_request(jQuery('#chooseAuthors'));
}

function choose_request(source) {
    var value = jQuery(source).val();
    jQuery(".sublist").addClass('hidden');
    jQuery(".select2-container").addClass('hidden');
    if (value === "targeted_request_country") {
        jQuery(".target_country").removeClass('hidden');
    }
    if (value === "targeted_request_study") {
        jQuery(".target_study").removeClass('hidden');
    }
    if (value === "targeted_request_certification") {
        jQuery(".target_certification").removeClass('hidden');
    }
    if (value === "targeted_request_category") {
        jQuery(".target_category").removeClass('hidden');
    }
    if (value === "expert_request") {
        jQuery(".expert").removeClass('hidden');
    }
    if (value === "private_team") {
        jQuery(".private").removeClass('hidden');
    }
    if (value === "private_team") {
        jQuery(".team").removeClass('hidden');
    }
    if (value === "private_writer") {
        jQuery(".select2-container").removeClass('hidden');
    }
}

var writersList;
var createWritersList = function () {
    var array = [];
    jQuery('.writers option').each(function () {
        var item = {
            id: jQuery(this).val(),
            text: jQuery(this).text()
        };
        array.push(item);
    });
    return array;
};

function requestFancybox(source) {
    showModalLoading();
    link = jQuery(source).attr('link');
    jQuery.ajax({
        type: "POST",
        cache: false,
        url: link,
        success: function (data) {
            jQuery('#defaultModal .modal-body').html(data);
            jQuery('#defaultModal').modal('show');
            startRequestForm();
        }
    });
}
function fancyboxAction(source) {
    showModalLoading();
    link = jQuery(source).attr('link');
    jQuery.ajax({
        type: "POST",
        cache: false,
        url: link,
        success: function (data) {
            jQuery('#defaultModal .modal-dialog').css('width', '900px');
            jQuery('#defaultModal .modal-body').html(data.data);
            jQuery('#defaultModal').modal('show');
            jQuery('.revisionEditForm :submit').click(function () {
                var button = this;
                var form = jQuery(this).closest('form');
                jQuery(form).append('<input type="hidden" name="revisionAction" value="' + jQuery(button).val() + '" />');
                jQuery.ajax({
                    url: adminLink+"?action=constant-content-ajax-data",
                    data: jQuery(form).serialize(),
                    dataType: 'json',
                    type: "POST",
                    beforeSend: function () {
                        showLoading('.fancybox-inner', 'Submiting Revision');
                    },
                    success: function (data) {
                        if (data.success === 'TRUE') {
                            jQuery('#defaultModal').modal('hide');
                            hideLoading('.fancybox-inner');
                        }
                        if (data.success === 'FALSE') {
                            var errorMessage = data.message;
                            errorMessage = errorMessage.replace('Invalid Price','Please enter a price greater than 0');
                            jQuery('.error_message', form).html(errorMessage).removeClass('hidden');
                            hideLoading('.fancybox-inner');
                        }
                    }
                });
                return false;
            });
        }
    });
}

function signupAction(source) {
    showModalLoading();
    link = jQuery(source).attr('link');
    jQuery.ajax({
        type: "POST",
        cache: false,
        url: link,
        success: function (data) {
            jQuery('#defaultModal .modal-body').html(data.data);
            jQuery('#defaultModal').modal('show');
        }
    });
}

function requestAction(source) {
    var message = jQuery(source).attr('message');
    jQuery('#confirm_dialog').clone().html('Are you sure you want to do this?').dialog({
        resizable: false,
        height: 250,
        modal: true,
        buttons: [{
                text: message,
                "id": "btnOk",
                click: function () {
                    showLoading('#requestTable tbody', 'Updating Requests');
                    link = jQuery(source).attr('link');
                    jQuery.ajax({
                        type: "POST",
                        cache: false,
                        url: link,
                        success: function () {
                            jQuery('#requestTable').dataTable().fnReloadAjax();
                        }
                    });
                    jQuery(this).dialog("close");
                }
            }, {
                text: "Cancel",
                click: function () {
                    jQuery(this).dialog("close");
                }
            }]
    });
}

function orderAction(source) {
    var message = jQuery(source).attr('message');
    jQuery('#confirm_dialog').clone().html('Are you sure you want to do this?').dialog({
        resizable: false,
        height: 250,
        modal: true,
        buttons: [{
                text: message,
                "id": "btnOk",
                click: function () {
                    showLoading('#ordersTable tbody', 'Updating Orders');
                    link = jQuery(source).attr('link');
                    jQuery.ajax({
                        type: "POST",
                        cache: false,
                        url: link,
                        success: function (data) {
                            jQuery('#accountBalance').html(data.credit_balance);
                            jQuery('#ordersTable').dataTable().fnReloadAjax();
                        }
                    });
                    jQuery(this).dialog("close");
                }
            }, {
                text: "Cancel",
                click: function () {
                    jQuery(this).dialog("close");
                }
            }]
    });
}

function fancyboxPreview(source) {
    showModalLoading();
    link = jQuery(source).attr('link');
    jQuery.ajax({
        type: "POST",
        cache: false,
        url: link,
        success: function (data) {
            jQuery('#defaultModal .modal-body').html(data);
            jQuery('#defaultModal').modal('show');
        }
    });
}

function showLoading(target, message) {
    var loader = jQuery('.loader_template').clone().removeClass('loader_template').removeClass('hidden').addClass('loader_display');
    if (message) {
        jQuery('.load-txt', loader).html(message);
    }
    jQuery(target).prepend(loader);
}

function hideLoading(target) {
    if (target) {
        jQuery('.loader_display', target).remove();
    } else {
        jQuery('.loader_display').remove();
    }
}

function unlinkAction() {
    jQuery('#confirm_dialog').clone().html('Are you sure you want to do this?').dialog({
        resizable: false,
        height: 250,
        modal: true,
        buttons: [{
                text: "Unlink Account",
                "id": "btnOk",
                click: function () {
                    jQuery('#unlinkForm').submit();
                }
            }, {
                text: "Cancel",
                click: function () {
                    jQuery(this).dialog("close");
                    return false;
                }
            }]
    });
    return false;
}

function showModalLoading() {
    jQuery('#defaultModal .modal-dialog').css('width', '');
    jQuery('#defaultModal .modal-body').html('<div class="modalSpinner" style="height: 150px;width: 690px;overflow: auto;position: absolute;">');
    showLoading(jQuery('#defaultModal .modal-body .modalSpinner'));
    jQuery('#defaultModal').modal('show');

}
