# Nimble Payments Plugin for Magento
The NimblePayments Plugin for Magneto is an addons that makes it easy to add payment services to your e-commerce.
##Release notes
###1.0.0
- First live release
- Added the single payment service

##Requirements
- Magento 1.9
- NimblePayments SDK for PHP https://github.com/nimblepayments/sdk-php.git

##Installation
The NimblePayments Plugin for Magento can either be installed by the Composer or manually.

The plugin can be downloaded from https://www.magentocommerce.com/magento-connect/

###Composer
To install the plugin via Composer, in a empty folder run the following commands:
```
git clone git@github.com:nimblepayments/magento.git magento
cd magento
composer.phar install
cp -R app PATH_TO_MAGENTO/
cp -R skin PATH_TO_MAGENTO/
cp -R lib PATH_TO_MAGENTO/
```
and replace ```PATH_TO_MAGENTO``` with the Magento folder path. Example: ```/var/www/magento```
###Manual Installation
To install the plugin without using the Composer,  just run the following commands:
```
git clone git@github.com:nimblepayments/magento.git magento
cd magento
cp -R app PATH_TO_MAGENTO/
cp -R skin PATH_TO_MAGENTO/
cd ..
git clone https://github.com/nimblepayments/sdk-php.git
cd sdk-php
git checkout tags/1.0.0.1
cp -R lib PATH_TO_MAGENTO/
```
##Environment
There are two different environment options:
- Sandbox.It is used in the demo environment to make tests.
- Real. It is used to work in the real environment.

The sandbox environment is disabled by default. To activate it, the variable mode must be manually set to “Sandbox” in the code. please, follow these steps:
- Open the file ```Bbva/NimblePayments/Model/Checkout.php```
- Search the line where ```const MODE = 'real';``` is placed
- Change the value ```real``` to ```sandbox```

##Documentation
Please see [Apiary](http://docs.nimblepublicapi.apiary.io/#) for up-to-date documentation.
