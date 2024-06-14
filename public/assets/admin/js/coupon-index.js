"use strict";
$(".coupon-type-change").on('click', function(){
    let order_type = $(this).val();
    coupon_type_change(order_type);
});
function coupon_type_change(order_type) {
    if(order_type=='first_order'){
        $('#limit-for-user').hide();
    }else{
        $('#limit-for-user').show();
    }
}

"use strict";

$(".discount-amount").on('click', function(){
    let discount_type = $(this).val();

    discount_amount(discount_type);
});
function discount_amount(discount_type)
{
    if(discount_type=='percent'){
        $('#max_discount').show();
    }else{
        $('#max_discount').hide();
    }
}

"use strict";

$(".checkstartDate").on('change', function(){
    checkstartDate();
});
function checkstartDate(){
    let starDate = $("#start_date").val();
    let expiredDate = $("#expire_date").val();
    var todayDate = new Date().toISOString().slice(0, 10);

    if(starDate<todayDate)
    {
        toastr.warning('Start Date can not be previous from today!', {
            CloseButton: true,
            ProgressBar: true
        });
        $("#start_date").val('');
    }
    if(expiredDate)
    {
        if(expiredDate<starDate)
        {
            toastr.warning('Start date can not be greater than expired date!', {
                CloseButton: true,
                ProgressBar: true
            });
            $("#start_date").val('');
        }
    }
}
$(".check-date").on('change', function(){
    checkDate();
});

"use strict";
function checkDate(){
    let starDate = $("#start_date").val();
    let expiredDate = $("#expire_date").val();

    var todayDate = new Date().toISOString().slice(0, 10);
    if(expiredDate<todayDate)
    {
        toastr.warning('Expired Date can not be previous from today!', {
            CloseButton: true,
            ProgressBar: true
        });
        $("#expire_date").val('');
    }

    if(expiredDate < starDate)
    {
        toastr.warning('Expiry Date can not be previous from start Date!', {
            CloseButton: true,
            ProgressBar: true
        });
        $("#expire_date").val('');
    }
}
