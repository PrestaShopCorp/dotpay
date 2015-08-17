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
*  @copyright dotpay
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*
*}
{if !$DOTPAY_CONFIGURATION_OK or $DP_TEST}
<div class="panel"><div class="dotpay-offer">
    <h3>{l s='Registration' mod='dotpay'}</h3>
    <p>{l s='In response to the market’s needs Dotpay has been delivering innovative Internet payment services providing the widest e-commerce solution offer for years. The domain is money transfers between a buyer and a merchant within a complex service based on counselling and additional security. Within an offer of Internet payments Dotpay offers over 50 payment channels including: mobile payments, instalments, cash, e-wallets, transfers and credit card payments.' mod='dotpay'}</p>
    <p>{l s='To all new clients who have filled in a form and wish to accept payments we offer promotional conditions:' mod='dotpay'}</p>
    <ul>
        <li><b>1,9%</b> {l s='commission on Internet payments (not less than PLN 0.30) ' mod='dotpay'}</li>
        <li>{l s='instalment payments' mod='dotpay'} <b>{l s='without any commission!' mod='dotpay'}</b></li>
        <li>{l s='an activation fee - only PLN 10' mod='dotpay'}</li>
        <li><b>{l s='without any additional fees' mod='dotpay'}</b> {l s='for refunds and withdrawals!' mod='dotpay'}</li>
    </ul>
    <p>{l s='In short, minimalizing effort and work time you will increase your sales possibilities. Do not hesitate and start your account now!' mod='dotpay'}</p>
    <div class="cta-button-container">
        <a href="http://www.dotpay.pl/prestashop/" class="cta-button">{l s='Register now!' mod='dotpay'}</a>
    </div>
</div></div>
{/if}
<div class="panel"><div class="dotpay-config">
    <h3>{l s='Configuration' mod='dotpay'}</h3>
    <p>{l s='Thanks to Dotpay payment module the only activities needed for integration are: rewriting ID and PIN numbers and URLC confirmation configuration.' mod='dotpay'}</p>
    <p>{l s='ID and PIN can be found in Dotpay panel in Settings on the top bar. ID number is a 6-digit string placed after # in a “Shop” line.' mod='dotpay'}</p>
    <p>{l s='URLC configuration is just setting an address to which information about a payment should be directed. This address is:' mod='dotpay'} <b>{$DP_URLC}</b></p>
    <p>{l s='If you possess a few shops connected with one dotpay account URL must be directed automatically and “Block external urlc” must not be ticked in Edition section.' mod='dotpay'}</p>
    <p>{l s='More information can be found in Dotpay manual.' mod='dotpay'}</p>
</div></div>

<div class="panel"><div class="dotpay-config-state">
    <h3>{l s='Configuration state' mod='dotpay'}</h3>
    {if $DOTPAY_CONFIGURATION_OK}
        <table><tr><td><img width="100" height="100" src="{$module_dir}img/tick.png"></td><td><p>
        <p>{l s='Module is active. If you do not recive payment information, please chcek URLC configuration.' mod='dotpay'}</p>
        <p>{if $DP_TEST}{l s='Module is in TEST mode. All payment informations are fake!' mod='dotpay'}{/if}</p>
        </p></td></tr></table>
    {else}
        <table><tr><td><img width="100" height="100" src="{$module_dir}img/cross.png"></td><td>
        <p>{l s='Module is not active. Please check yours configuration.' mod='dotpay'}</p>
        <p>{l s='ID and PIN can be found in Dotpay panel in Settings on the top bar. ID number is a 6-digit string placed after # in a “Shop” line.' mod='dotpay'}</p>
        </p></td></tr></table>
    {/if}
</div></div>

<h2>Dotpay</h2>
<form action='{$DP_URI}' method='post'>
<input type="radio" name="DP_TEST" value="0" {if $DP_TEST eq '0' || $DP_TEST eq false}checked="checked"{/if}>{l s='Production environment' mod='dotpay'}<br>
<input type="radio" name="DP_TEST" value="1" {if $DP_TEST eq '1' || $DP_TEST eq true}checked="checked"{/if}>{l s='Testing environment' mod='dotpay'}<br><br>
<input type="radio" name="DP_CHK" value="1" {if $DP_CHK  eq '1' || $DP_CHK eq true}checked="checked"{/if}>{l s='CHK Blockade ON' mod='dotpay'}<br>
<input type="radio" name="DP_CHK" value="0" {if $DP_CHK  eq '0' || $DP_CHK eq false}checked="checked"{/if}>{l s='CHK Blockade OFF' mod='dotpay'}<br><br>
<input type="radio" name="DP_SSL" value="1" {if $DP_SSL  eq '1' || $DP_SSL eq true}checked="checked"{/if}>{l s='SSL ON' mod='dotpay'}<br>
<input type="radio" name="DP_SSL" value="0" {if $DP_SSL  eq '0' || $DP_SSL eq false}checked="checked"{/if}>{l s='SSL OFF' mod='dotpay'}<br><br>
ID : <input type='text' name='DP_ID' value='{$DP_ID}'/>
PIN : <input type='text' name='DP_PIN' value='{$DP_PIN}'/> <br><br>
<input type='submit' name='submitDotpayModule' value='Zapisz' /><br>
<br><br>
</form>
{literal}
<style type="text/css">
.tg  {border-collapse:collapse;border-spacing:0;}
.tg td{font-family:Arial, sans-serif;font-size:14px;padding:10px 5px;border-style:solid;border-width:1px;overflow:hidden;word-break:normal;}
.tg th{font-family:Arial, sans-serif;font-size:14px;font-weight:normal;padding:10px 5px;border-style:solid;border-width:1px;overflow:hidden;word-break:normal;}
.tg .tg-s6z2{text-align:center}
.tg .tg-vkov{background-color:#32cb00;text-align:center}
.tg .tg-pn40{background-color:#fe0000;text-align:center}
.tg .tg-vl7g{background-color:#fe0000;color:#000000;text-align:center}
</style>
{/literal}
<table class="tg">
  <tr>
    <th class="tg-s6z2">Moduł aktywny<br></th>
    {if $DOTPAY_CONFIGURATION_OK eq '1' || $DOTPAY_CONFIGURATION_OK eq true}<th class="tg-vkov">True</th>{/if}
    {if $DOTPAY_CONFIGURATION_OK eq '0' || $DOTPAY_CONFIGURATION_OK eq false}<td class="tg-pn40">False</td>{/if}
  </tr>
  <tr>
    <td class="tg-s6z2">Środowisko produkcyjne</td>
    {if $DP_TEST eq '0' || $DP_TEST eq false}<th class="tg-vkov">True</th>{/if}
    {if $DP_TEST eq '1' || $DP_TEST eq true}<td class="tg-pn40">False</td>{/if}
  </tr>
  <tr>
    <td class="tg-s6z2">Tryb CHK</td>
    {if $DP_CHK eq '1' || $DP_CHK eq true}<th class="tg-vkov">True</th>{/if}
    {if $DP_CHK eq '0' || $DP_CHK eq false}<td class="tg-pn40">False</td>{/if}
  </tr>
    <tr>
    <td class="tg-s6z2">Aktywacja SSL w sklepie<br>(Preferencje->Ogólny)</td>
    {if $SSL_ENABLED eq '1' || $SSL_ENABLED eq true}<th class="tg-vkov">True</th>{/if}
    {if $SSL_ENABLED eq '0' || $SSL_ENABLED eq false}<td class="tg-pn40">False</td>{/if}
  </tr>  
  <tr>
  <td class="tg-s6z2">Aktywacja SSL w module</td>
    {if $DP_SSL eq '1' || $DP_SSL eq true}<th class="tg-vkov">True</th>{/if}
    {if $DP_SSL eq '0' || $DP_SSL eq false}<td class="tg-pn40">False</td>{/if}
  </tr>  
</table>
