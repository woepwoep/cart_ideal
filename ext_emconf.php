<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "cart_ideal".
 *
 * Auto generated 13-03-2020 11:32
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array (
  'title' => 'Cart - iDEAL',
  'description' => 'Shopping Cart(s) for TYPO3 - iDEAL Payment Provider',
  'category' => 'services',
  'author' => 'Ronald Wopereis',
  'author_email' => 'woepwoep@gmail.com',
  'author_company' => 'Red-Seadog',
  'state' => 'beta',
  'uploadfolder' => false,
  'createDirs' => '',
  'clearCacheOnLoad' => 0,
  'version' => '0.1.0',
  'constraints' => [
    'depends' => [
      'typo3' => '9.5.0-9.5.99',
      'cart' => '6.3.0',
    ],
    'conflicts' => [],
    'suggests' => [],
  ],
  'clearcacheonload' => false,
);
