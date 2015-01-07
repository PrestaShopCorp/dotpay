<div class="alert alert-info">
<img src="../modules/dotpay/views/templates/img/Logo_Dotpay_147x56.jpg" style="float:left; margin-right:15px;" >
<p><strong>{l s="This module allows you to accept secure payments by dotpay.pl payment gateway" mod="dotpay"}</strong></p>
<p>{l s="To activate this payment method you need an dotpay account. " mod="dotpay"}<a href="https://ssl.dotpay.pl/registration">{l s="Register" mod="dotpay"}</a></p><br><br>
<p>{l s="Your's dotpay ID and PIN are avalible in dotpay merchant panel in Setting section" mod="dotpay"}</p>
<form action='{$DP_URI}' method='post'>
<input type="radio" name="dp_test" value="0" {if $DP_TEST eq '0'}checked="checked"{/if}>{l s='Production environment' mod="dotpay"}<br>
<input type="radio" name="dp_test" value="1" {if $DP_TEST eq '1'}checked="checked"{/if}>{l s='Testing environment' mod="dotpay"}<br><br>
ID: <input type='text' name='dp_id' value='{$DP_ID}'/>
PIN: <input type='text' name='dp_pin' value='{$DP_PIN}'/> <br><br>
<input type='submit' name='Save_DP' value='Save' /><br>
<p>{$DP_MSG}</p>
<br><br> 
</form>
</div>