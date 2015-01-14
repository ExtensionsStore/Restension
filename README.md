Restension
==========
Restension extends the Magento REST API. The most useful feature is to be able to login/authorize
a user without requiring that the user go to your web page to authorize your app (Step 2 in the 
OAuth 1 dance). 

Description
-----------
Adds additional features to the Magento REST API. Currently supports:

- Getting the Step 2 authorization code without displaying the authorization web page
- Register the customer
- Send forgot password email
- Get shipping rates

How to use
-------------------------
Upload the files to the root of your Magento install and let the install script run.
The table aydus_restension_shippingmethods will be created, a test app and test customer. 

Enable the new resources: