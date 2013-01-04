<?php
/**
 * UUID class definition
 *
 * @author Yoann Mikami <yoann.mikami@gmail.com>
 */

/**
 * Utility to generate Universally Unique IDentifier, aka UUID.
 * (Or Unique User IDenfitier, as you like)
 *
 * This is the preferred way to interact with iPhone applications,
 * for privacy concerns. Moreover, UDID is deprecated as of iOS5 and
 * should not be used as specified in the Apple documentations.
 */
final class UUID
{
    /**
     * Returns a UUID in its canonical form, as described below :
     * 32 hexadecimal digits, displayed in 5 groups separated by hyphens
     * in the form 8-4-4-4-12 for a total of 36 characters.
     * If $short is true, the result is then shortened by expanding the
     * range of characters to ascii (lowercase and uppercase).
     *
     * If $short is true, then a base64 encoded version is returned instead,
     * after converting the raw hexadecimal value into the ascii character equiv.
     *
     * @param boolean $short true to shorten the uuid using base64 (modified)
     * @return @type string the new UUID generated
     */
    public static function generate($short=false)
    {
        $delim  = $short?'':'-';
        $uuid   = array();
        $chars  = uniqid(md5(mt_rand()), true);
        $uuid[] = substr($chars,0,8);
        $uuid[] = substr($chars,8,4);
        $uuid[] = substr($chars,12,4);
        $uuid[] = substr($chars,16,4);
        $uuid[] = substr($chars,20,12);
        $uuid   = implode($delim, $uuid);
        return (!$short?$uuid:self::_shorten($uuid));
    }

    /**
     * Shortens given UUID into a base64 encoded version
     *
     * Splits uuid into packs of 2 characters defining an hex
     * value, converted into the raw ascii equivalent, then
     * base64 the result.
     * To make it URL-friendly, + and / characters are replaced
     * with _ and - resp.
     *
     * @param $uuid the UUID to convert
     * @return @type string the base64 encoded equivalent
     */
    private static function _shorten($uuid)
    {
        return strtr(
                 substr(
                   base64_encode(
                     implode('',
                       array_map('chr',
                         array_map('hexdec', str_split($uuid, 2))))), 0, -2),
                 '/+', '_-');
    }
}
