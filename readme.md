## EOS Custom Reorder Report

Version: 2.0

* Returns all products whose reorder point is equal to or exceeds the available quantity
* Exclusively for Lightspeed Onsite
* Created to address the limitation of Onsite where the comparison is made using the total quantity as opposed to the available quantity

### Tested and updated on
Apache: 2.4.10
PHP: 5.6.5
mySQL: 5.6.15

### Requirements

The 'report' is a web application so it needs to be installed as such. How you wish to configure the domain or virtualhost to access the index page is entirely up to you.

### Instructions

Once you have the application 'installed' navigate to includes/rest_connector2.php and update the *user-agent* and *privateID* to reflect the options of your app. Thats it!