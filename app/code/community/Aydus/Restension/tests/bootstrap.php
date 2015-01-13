<?php
/**
 * Test bootstrap
 * Load Magento instance and run tests
 *
 * @category    Aydus
 * @package     Aydus_Restension
 * @author		Aydus <davidt@aydus.com>
 */

//get the magento path, not the symlink path
$pwd = getenv("PWD");
//split current path on app 
//(since current directory is most likely a modman symlink)
preg_match('/^(.+\/app)(.+)$/', $pwd, $matches);
//change dir to absolute path of magento install
chdir($matches[1]);
//now can require Mage.php
require_once('./Mage.php');
//set mask(?)
umask(0);
//run the app
Mage::app();
