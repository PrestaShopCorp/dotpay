<p style="text-align:center;"><img src="{$module_dir}/img/dotpay_logo_napisPL.png" /><img width="128" height="128" src="{$module_dir}/img/loading2.gif" /></p>
<p style="text-align:center;">{l s='Please wait for payment confirmation.' mod='dotpay'}</p>
<form action="{$form_url}" method="post" id="dpForm" name="dpForm" target="_parent">
{foreach from=$params key=k item=v}
<input type="hidden" name="{$k}" value="{$v}"/>
{/foreach}
</form>
{literal}
<script language="JavaScript">
setTimeout(function(){document.dpForm.submit()}, 3000);
</script>
{/literal}