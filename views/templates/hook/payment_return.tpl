{*
*
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    Piotr Karecki <tech@dotpay.pl>
*  @copyright Dotpay
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  @last modified: Dotpay: 2015-08-04
*  				   add: Dotpay logo proper depending on the language Store (pl or en)	
*
*}
<p class="dotpay_return"><img src="{$module_dir}img/Dotpay_logo_napis{if $lang_iso == 'pl'}_pl{else}_en{/if}.png" /><img width="128" height="128" src="{$module_dir}img/loading2.gif" /></p>
<p class="dotpay_return">{l s='Please wait for payment confirmation.' mod='dotpay'}</p><br/><br/>
<form action="{$form_url}" method="post" id="dpForm" name="dpForm" target="_parent">
{foreach from=$params key=k item=v}
<input type="hidden" name="{$k|escape}" value="{$v|escape}"/>
{/foreach}
</form>
{literal}
<script language="JavaScript">
setTimeout(function(){document.dpForm.submit()}, 3000);
</script>
{/literal}