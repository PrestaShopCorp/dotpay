<center><img src="{$module_dir}/img/dotpay_logo_napisPL.png"><img width="128" height="128" src="{$module_dir}/img/loading2.gif"><br />
<p>{l s='Please wait for payment confirmation.' mod='dotpay'}</p></center>
</center><br><br>

<form method="post" action="{if $is_guest}{$link->getPageLink('guest-tracking', true)|escape:'html':'UTF-8'}{else}{$link->getPageLink('history', true)|escape:'html':'UTF-8'}{/if}" id="dpForm" name="dpForm">
<input class="form-control" type="hidden" name="order_reference" value="{$reference}" />
<input class="form-control" type="hidden" name="email" value="{$email}" />
<input class="form-control" type="hidden" name="submitGuestTracking" value="1" />
</form>

{literal}
<script language="JavaScript">
setTimeout(function(){document.dpForm.submit()}, 1000);
</script>
{/literal}