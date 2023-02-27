{*
* 2007-2022 PrestaShop
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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2022 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<div class="panel">
	<div class="row mailtplvarsmoduleconfig-header">
		<div class="col-xs-5 text-right">
			<img src="{$module_dir|escape:'html':'UTF-8'}views/img/logo.png" />
		</div>
		<div class="col-xs-7 text-left">
			<h2>{l s='E-mail template variables' d='Modules.Mailtemplatevars.Settings'}</h2>
			<h4>{l s='Add various e-mail variables to various e-mail templates and the possibility to add custom e-mail subjects to order transactional e-mails.' d='Modules.Mailtemplatevars.Settings'}</h4>
			<ul class="ul-spaced">
				<li><strong>{l s='Author' d='Modules.Mailtemplatevars.Settings'}: {$author}</strong></li>
				<li>{l s='Contact' d='Modules.Mailtemplatevars.Settings'}: <a href="mailto:dani.strba@gmail.com">dani.strba@gmail.com</a></li>
				<li>{l s='Version' d='Modules.Mailtemplatevars.Settings'}: {$module_version}</li>
				<li>{l s='Created' d='Modules.Mailtemplatevars.Settings'}: 23/02/2023</li>
			</ul>
			<p>{$notice}</p>
			<pre>{$code}</pre>
		</div>
	</div>
</div>
