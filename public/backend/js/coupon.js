/**
 * 
 */
$("select[name='coupon_type']").change(function() {
	

	$val = $(this).val();
	if ($val == 1) {
        $("input[name=use_limit],input[name=max_num]").val('0').prop('disabled',true);
	} else{
        $("input[name=use_limit],input[name=max_num]").val('0').prop('disabled',false);
	}
});
