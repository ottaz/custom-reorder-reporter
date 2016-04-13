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

The 'report' is a **web application** so it needs to be installed as such. How you wish to configure the domain or virtualhost to access the index page is entirely up to you.

### Install instructions

These instructions assume you have your web environment set up whether it's local or remote and you are familiar with git.

Clone the repository
```
$ git clone git@github.com:ottaz/custom-reorder-reporter.git
```

Once the files have been downloaded, open ```includes/rest_connector2.php``` for editing in a text editor or VIM, for example, if you are comfortable with the command line, 
```
$ cd custom-reorder-reported/includes
$ vim rest_connector.php
```

Update the ```user-agent``` and ```privateID``` values to reflect the options of your app.

Navigate to the homepage via the virtualhost you have setup.