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

<div class="panel"><div class="dotpay-offer">
    <h3>{l s='Registration' mod='dotpay'}</h3>
    <p>{l s='W odpowiedzi na zapotrzebowanie rynku Dotpay od lat świadczy innowacyjne usługi płatności internetowych, dostarczając najszerszą ofertę rozwiązań dla klientów z branży e-commerce. Domeną są transfery pieniężne przesyłane pomiędzy kupującym a sprzedającym, wraz z kompleksowym serwisem, opartym na fachowym doradztwie i stosowaniu dodatkowych zabezpieczeń. W ramach oferty płatności internetowych Dotpay oferuje ponad 50 kanałów płatności, w tym: płatności mobilne, ratalne, gotówkowe, e-portfele, przelewy i karty płatnicze.' mod='dotpay'}</p>
    <p>{l s='Wszystkim nowym klientom, którzy wypełnią formularz i chcą już dzisiaj zacząć przyjmować płatności, oferujemy promocyjne warunki współpracy:' mod='dotpay'}</p>
    <ul>
        <li><b>1,9%</b> {l s='prowizji od płatności internetowych (nie mniej niż 0.30 PLN)' mod='dotpay'}</li>
        <li>{l s='płatności ratalne' mod='dotpay'} <b>{l s='bez prowizji!' mod='dotpay'}</b></li>
        <li>{l s='opłata aktywacyjna tylko 10 PLN' mod='dotpay'}</li>
        <li><b>{l s='bez dodatkowych opłat' mod='dotpay'}</b> {l s='za zwroty i wypłaty!' mod='dotpay'}</li>
    </ul>
    <p>{l s='Reasumując – minimalizując nakłady i czas pracy, zwiększasz swoje możliwości sprzedażowe. Nie zwlekaj i już teraz uruchom swoje konto!' mod='dotpay'}</p>
    <div class="cta-button-container">
        <a href="http://www.dotpay.pl/prestashop/" class="cta-button">{l s='Zarejestruj się!' mod='dotpay'}</a>
    </div>
</div></div>

<div class="panel"><div class="dotpay-config">
    <h3>{l s='Configuration' mod='dotpay'}</h3>
    <p>{l s='Dzięki modułowi płatności dotpay, jedyne czynności niezbędne do integracji to przepisanie numeru ID, PIN oraz odpowiednia konfiguracja powiadomień URLC.' mod='dotpay'}</p>
    <p>{l s='ID oraz PIN można odnaleźć w panelu dotpay klikając "Ustawienia" na górnym pasku. Numer ID to 6-cyfrowy ciąg umieszczony po znaku # w wierszu "Sklep".' mod='dotpay'}</p>
    <p>{l s='Konfiguracja URLC polega na ustawieniu adresu na który mają być kierowane informacje o płatnościach. Tym adresem jest: ' mod='dotpay'}<b>{$DP_URLC}</b></p>
    <p>{l s='Jeżeli posiadają Państwo wiele sklepów połączonych z kontem, adres URL musi być przekazywany automatycznie i należy odznaczyć opcję "Blokuj zewnętrzne urlc" klikając w przycisk "Edycja".' mod='dotpay'}</p>
    <p>{l s='Więcej szczegółów znają Państwo w dokumentacji dotpay po zalogowaniu do panelu.' mod='dotpay'}</p>
</div></div>
