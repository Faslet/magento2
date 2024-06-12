![base-image](https://github.com/magmodules/magento2-faslet-dev/assets/24823946/eba2bce1-6b46-41f0-aa1f-9263c663ed9c)

# Faslet widget for Magento® 2

The official extension to connect Faslet widget with your Magento® 2 store.

## About the Faslet widget

Faslet’s Size Me Up widget helps your retail customers find the perfect size in just a few easy steps. No measuring tape is required! The size recommendation widget is tailored to your online fashion brand or multi-brand store, both in function and design. Our innovative algorithm ensures your customers will be recommended the right size. The widget itself is easy to integrate and customize, making it the perfect addition to your online retail shop.

Offering this type of certainty regarding size will make your customers feel confident about their purchase. They will be more likely to buy and less likely to return. This will not only increase your retail shop’s profits but reduce co2 emissions as well. Meanwhile, our system captures customer intelligence, providing you with valuable insights for future brand strategy.

Faslet’s size recommendation solution, therefore, benefits you, your customers, and the environment. It’s an innovative, user-friendly, and sustainable solution to sizing and online shopping.

[Visit Faslet](https://site.faslet.me/)

## Installation

#### Install via Composer

1. Go to Magento® 2 root folder

2. Enter following commands to install module:

   ```
   composer require faslet/magento2
   ``` 

3. Enter following commands to enable module:

   ```
   php bin/magento module:enable Faslet_Connect
   php bin/magento setup:upgrade
   php bin/magento cache:clean
   ```

4. If Magento® is running in production mode, deploy static content with the following command:

   ```
   php bin/magento setup:static-content:deploy
   ```

#### Install from GitHub

1. Download zip package by clicking "Clone or Download" and select "Download ZIP" from the dropdown.

2. Create an app/code/Faslet/Connect directory in your Magento® 2 root folder.

3. Extract the contents from the "magento2-faslet-dev-main" zip and copy or upload everything to app/code/Faslet/Connect

4. Run the following commands from the Magento® 2 root folder to install and enable the module:

   ```
   php bin/magento module:enable Faslet_Connect
   php bin/magento setup:upgrade
   php bin/magento cache:clean
   ```

5. If Magento® is running in production mode, deploy static content with the following command:

   ```
   php bin/magento setup:static-content:deploy
   ```

## Development by Magmodules

We are a Dutch Magento® Only Agency dedicated to the development of extensions for Magento® 1 and Magento® 2. All our extensions are coded by our own team and our support team is always there to help you out.

[Visit Magmodules.eu](https://www.magmodules.eu/)


## Links

[Terms and Conditions](https://www.magmodules.eu/terms.html)

[Contact Us](https://www.magmodules.eu/contact-us.html)
