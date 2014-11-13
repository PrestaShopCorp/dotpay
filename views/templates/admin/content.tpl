<h2>Dotpay</h2>
<form action='{$DP_URI}' method='post'>
<input type="radio" name="dp_test" value="0" {if $DP_TEST eq '0'}checked="checked"{/if}>{l s='Production environment' mod='dotpay'}<br>
<input type="radio" name="dp_test" value="1" {if $DP_TEST eq '1'}checked="checked"{/if}>{l s='Testing environment' mod='dotpay'}<br><br>
ID : <input type='text' name='dp_id' value='{$DP_ID}'/>
PIN : <input type='text' name='dp_pin' value='{$DP_PIN}'/> <br><br>
<input type='submit' name='Save_DP' value='Save' /><br>
<p>{$DP_MSG}</p>
<br><br> 

</form>
