<?php
/**
 *  2007-2014 PrestaShop
 *
 *  NOTICE OF LICENSE
 *
 *  This source file is subject to the Academic Free License (AFL 3.0)
 *  that is bundled with this package in the file LICENSE.txt.
 *  It is also available through the world-wide-web at this URL:
 *  http://opensource.org/licenses/afl-3.0.php
 *  If you did not receive a copy of the license and are unable to
 *  obtain it through the world-wide-web, please send an email
 *  to license@prestashop.com so we can send you a copy immediately.
 *
 *  DISCLAIMER
 *
 *  Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 *  versions in the future. If you wish to customize PrestaShop for your
 *  needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2014 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *   International Registered Trademark & Property of PrestaShop SA
 *
 */

/**
 * Description of FileValidator
 *
 * @author Ivan
 */
class CetelemFieldValidator
{

    public static function validateFirstLastName($field)
    {
        $field = str_replace('-', ' ', $field);
        return self::validateField(
            '/[a-zA-ZàáâäãåacceèéêëeiìíîïlnòóôöõøùúûüuuÿýzzñçcšžÀÁÂÄÃÅACCEEÈÉÊËÌÍÎÏILNÒÓÔÖÕØÙÚÛÜUUŸÝZZÑßÇŒÆCŠŽ\\s]{2,40}$/',
            $field
        );
    }

    public static function validateGender($field)
    {
        return self::validateField('/(SR|SRA)/', $field);
    }

    public static function validateDNI($field)
    {
        return self::validateField("/(\\d{8})([A-Z])/", $field) . self::validateField("/[XYZ]\\d{7,8}[A-Z]/", $field);
    }

    public static function validateBirthday($field)
    {
        return self::validateField('/(3[01]|[12][0-9]|0[1-9])\/(1[0-2]|0[1-9])\/[0-9]{4}$/', $field);
    }

    public static function validateAddress($field)
    {
//        $field = utf8_encode($field);
        return self::validateField(
            '/[a-zA-ZàáâäãåacceèéêëeiìíîïlnòóôöõøùúûüuuÿýzzñçcšžÀÁÂÄÃÅACCEEÈÉÊËÌÍÎÏILNÒÓÔÖÕØÙÚÛÜUUŸÝZZÑßÇŒÆCŠŽ \\s-_\/\.,\']{2,50}$/',
            $field
        );
    }

    public static function validateCity($field)
    {
        $field = Tools::substr($field, 0, 21);
        return self::validateField(
            "/[a-zA-ZàáâäãåacceèéêëeiìíîïlnòóôöõøùúûüuuÿýzzñçcšžÀÁÂÄÃÅACCEEÈÉÊËÌÍÎÏILNÒÓÔÖÕØÙÚÛÜUUŸÝZZÑßÇŒÆCŠŽ\\s-_,.']{2,20}$/",
            $field
        );
    }

    public static function validatePostcode($field)
    {
        return self::validateField("/[0-9]{5}/", $field);
    }

    public static function validateEmail($field)
    {
        return self::validateField("/[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\\.[a-zA-Z0-9-.]+$/", $field);
    }

    public static function validatePhone($field)
    {
        return self::validateField("/(9|8)[0-9]{8}$/", $field);
    }

    public static function validateMobilePhone($field)
    {
        return self::validateField("/(6|7)[0-9]{8}$/", $field);
    }

    private static function validateField($regex, $field)
    {
        if (preg_match($regex, $field)) {
            return $field;
        } else {
            return '';
        }
    }
}
