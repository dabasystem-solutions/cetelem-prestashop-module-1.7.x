## Synopsis

Module developed to integrate Cetelem Payments in PrestaShop 1.5 and 1.6

## Installation

Follow installation in backoffice of PrestaShop.

## Manual Installation if calculator not appear in product page.

This module comes by default with hooks hookDisplayLeftColumnProduct and hookDisplayRightColumnProduct for product.tpl,
and is hooked in hookDisplayRightColumnProduct. If in your template not appear the calculator you can add this one new
line in product.tpl {hook h="displayRightColumnProduct" mod="cetelem"}

IMPORTANT Only one instance of calcualtor can appear.

It's all.

## API Reference

In this module is used:

- Cetelem API integratoin

## Contributors

If you detect any bug or add any improvement please contact us at addons@ecomm360.es

## License

Module developed by eComm360 (www.ecomm360.es)
