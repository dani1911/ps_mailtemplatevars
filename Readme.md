# Custom module to add various e-mail variables to various e-mail templates. The module also has the ability to set-up custom e-mail subjects to order history e-mails. This however needs and override to the OrderHistory.php. I didn't want to do the override via this module so you will need to the override yourself. But it is quite simple:

# Create a copy of classes\order\OrderHistory.php and paste it here override\classes\order\OrderHistory.php
# Open the OrderHistory.php inside the override with a text editor.
# Keep the public function sendEmail method. You can remove all the other methods.
# Do the following changes:
# 33 - SELECT osl.`template`, c.`lastname`, c.`firstname`, osl.`name` AS osname, c.`email`, os.`module_name`, os.`id_order_state`, os.`pdf_invoice`, os.`pdf_delivery`
# 33 + SELECT osl.`template`, c.`lastname`, c.`firstname`, osl.`name` AS osname, c.`email`, os.`module_name`, os.`id_order_state`, os.`pdf_invoice`, os.`pdf_delivery`, msl.`subject`
# 39 + LEFT JOIN `" . _DB_PREFIX_ . "mailtplvars_subjects_lang` msl ON (os.`id_order_state` = msl.`id_order_state` AND msl.`id_lang` = o.`id_lang`)
# 44 - $topic = $result["osname"];
# 44 + $topic = (isset($result["subject"]) && !empty($result["subject"])) ? $result["subject"] : $result["osname"];

# After that clear the cache. Now if you set up a custom subject when sending order history email sould be using your custom subject.

===========================
v 1.0.0 (Mar 22, 2022)
===========================

Initial release
- [+] config page
- [+] custom subject functionality
- [+] various variables added to various e-mail templates
- [+] created new variables for order summery table and order url
- [+] reworked the order_conf_cart_rule file

