{include file="$tpl_dir./breadcrumb.tpl"}
<h2>{l s='Dotpay - payment summanary' mod='dotpay'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

<script>
$(document).ready(function(){
	var btnSub = document.getElementById('dp_submit');
	btnSub.click();
});

</script>

<p>
<form action="https://ssl.dotpay.pl/" method="post" id="dpForm" name="dpForm">
<p class="cart_navigation">
	<!-- <a href="{$base_dir_ssl}order.php?step=3" class="button_large">{l s='Other payment methods' mod='dotpay'}</a> -->
	<input type="hidden" name="id" value="{$dp_id}"/>
	<input type="hidden" name="control" value="{$dp_control}"/>
	<input type="hidden" name="amount" value="{$dp_amount}"/>
	<input type="hidden" name="description" value="{$dp_desc}"/>
	<input type="hidden" name="url" value="{$dp_url}"/>
	<input type="hidden" name="urlc" value="{$dp_urlc}"/>
	<input type="hidden" name="email" value="{$customer->email}"/>
	<input type="hidden" name="type" value="0"/>
	<input type="hidden" name="firstname" value="{$customer->firstname}"/>
	<input type="hidden" name="lastname" value="{$customer->lastname}"/>
	<input type="hidden" name="street" value="{$address->address1}" /> 
	<input type="hidden" name="city" value="{$address->city}" /> 
	<input type="hidden" name="postcode" value="{$address->postcode}" />
	<input type="hidden" name="currency" value="{$currency}" />
	<input type="submit" name="submit" id="dp_submit"  value="{l s='Go to dotpay.pl' mod='dotpay'}" class="exclusive_large" />
</p>
</form>
