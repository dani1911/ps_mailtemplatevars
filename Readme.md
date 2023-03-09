# mailtemplatevars
Custom module to add various e-mail variables to various e-mail templates. Modification of some template files is needed and even creation of new template files is encouraged to take full advantage of this module.

The module also has the ability to set-up custom e-mail subjects to order history e-mails. This however needs an override to the OrderHistory.php. I didn't want to do the override via this module since it is discouraged by presta and you already might have an override for this file and method in place. You will need to the override this file by yourself. But the required changes are small and quite simple:
If you do not have the override yet, create a copy of classes\order\OrderHistory.php and paste it here override\classes\order\OrderHistory.php  
Open the OrderHistory.php inside the override with a text editor.  
Keep the public function sendEmail method. You can remove all the other methods.  
Do the following changes:  
```diff   
- 32 SELECT osl.`template`, c.`lastname`, c.`firstname`, osl.`name` AS osname, c.`email`, os.`module_name`, os.`id_order_state`, os.`pdf_invoice`, os.`pdf_delivery`  
+ 32 SELECT osl.`template`, c.`lastname`, c.`firstname`, osl.`name` AS osname, c.`email`, os.`module_name`, os.`id_order_state`, os.`pdf_invoice`, os.`pdf_delivery`, msl.`subject`  
+ 38 LEFT JOIN `" . _DB_PREFIX_ . "mailtplvars_subjects_lang` msl ON (os.`id_order_state` = msl.`id_order_state` AND msl.`id_lang` = o.`id_lang`)  
- 43 $topic = $result["osname"];  
+ 43 $topic = (isset($result["subject"]) && !empty($result["subject"])) ? $result["subject"] : $result["osname"];  
```  

After that save the file and clear the cache. Now if you set up a custom subject when sending order history email sould be using your custom subject.  

---
v 1.0.0 (Feb 27, 2023)
---

Initial release
- [+] config page
- [+] custom subject functionality
- [+] various variables added to various e-mail templates
- [+] created new variables for order summary table, order url and preorder products

E-mail Template modifications you might consider
- add button with {order_url} to all order transactional e-mail. This will take the customer to the concrete order details if he is logged into his account with your shop
- rework the order_conf file to include {totals} variable. This adds custom block with order summary totals. I didn't like the way it was done by Prestashop. Essentialy all summary items that are 0 are not shown (gift-wrapping, discounts etc.)
- for this new order_conf_summary.tpl and order_conf_summary.txt files are needed. These need to be placed into your language folders either in core mail folder or inside your theme mail folder if you use that. Contents of the files are up to you as long as you use the variables that are available

Below is list of new variables and template files that can use them:

| VARIABLE | TEMPLATE FILES |
| -------- | -------------- |
| {reference} | backoffice_order, order_changed |
| {date} | backoffice_order, order_changed |
| {products} | backoffice_order, order_changed |
| {products_txt} | backoffice_order, order_changed |
| {discounts} | backoffice_order, order_changed |
| {discounts_txt} | backoffice_order, order_changed |
| {totals} | backoffice_order, order_changed |
| {totals_txt} | backoffice_order, order_changed |
| {delivery_block_txt} | backoffice_order, order_changed |
| {invoice_block_txt} | backoffice_order, order_changed |
| {delivery_block_html} | backoffice_order, order_changed |
| {invoice_block_html} | backoffice_order, order_changed |
| {carrier} | backoffice_order, order_changed |
| {payment} | backoffice_order, order_changed |
| {bankwire_owner} | bankwire |
| {bankwire_details} | bankwire |
| {total_paid} | bankwire |
| {carrier} | in_transit |
| {tracking_number} | in_transit |
| {order_url} | backoffice_order, bankwire, in_transit, order_canceled, order_changed, order_conf, order_merchant_comment, outofstock, payment, payment_error, preparation, shipped |
