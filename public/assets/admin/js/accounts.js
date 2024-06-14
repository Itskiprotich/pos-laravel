"use strict";
$(".balance_transfer").on('click', function(){
    let val = $(this).data('id');
    balance_transfer(val);
});

function balance_transfer(val){
    let payableBalance = $('#available_balance-'+val).val();
    $('#transection_id').val(val);
    $('#payment_balance').val(payableBalance).attr('max',payableBalance);
}

"use strict";

$(".balance_transfer_rec").on('click', function(){
    let val = $(this).data('id');
    balance_transfer_rec(val);
});

function balance_transfer_rec(val){
    let payableBalance = $('#available_balance-'+val).val();
    $('#transection_id').val(val);
    $('#payment_balance').val(payableBalance).attr('max',payableBalance);
}
