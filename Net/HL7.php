<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
// +----------------------------------------------------------------------+
// | PHP version 5                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 2004 D.A.Dokter                                        |
// +----------------------------------------------------------------------+
// | This source file is subject to version 3.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.php.net/license/3_0.txt.                                  |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: D.A.Dokter <dokter@w20e.com>                                |
// | Updated by: D.A.Rivera <diegoriveramdq at gmail.com>                 |
// +----------------------------------------------------------------------+
//
// $Id: HL7.php,v 1.7 2004/08/06 07:38:54 wyldebeast Exp $

/**
 * The Net_HL7 class is a factory class for HL7 messages.
 *
 * The factory class provides the convenience of changing several
 * defaults for HL7 messaging globally, like separators, etc. Note
 * that some default settings use characters that have special meaning
 * in PHP, like the HL7 escape character. To be able to set these
 * values, escape the special characters.
 *
 * @version    0.1.0
 * @author     D.A.Dokter <dokter@w20e.com>
 * @access     public
 * @category   Networking
 * @package    Net_HL7
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 */
require_once '/HL7/Connection.php';
require_once '/HL7/Message.php';
require_once '/HL7/Messages/ACK.php';
require_once '/HL7/Segment.php';
require_once '/HL7/Segments/MSH.php';

class Net_HL7 {

    /**
     * Holds all global HL7 settings.
     */
    private static $_hl7Globals = array('SEGMENT_SEPARATOR' => "\r",
                                        'FIELD_SEPARATOR' => "|",
                                        'NULL' => '""',
                                        'COMPONENT_SEPARATOR' => "^",
                                        'REPETITION_SEPARATOR' => "~",
                                        'ESCAPE_CHARACTER' => "\\",
                                        'SUBCOMPONENT_SEPARATOR' => "&",
                                        'HL7_VERSION' => "2.3");

    /**
     * Create a new Net_HL7_Message, using the global HL7 variables as
     * defaults.
     * 
     * @param string Text representation of an HL7 message
     * @return Net_HL7_Message Net_HL7_Message
     * @access public
     */
    static function &createMessage($msgStr = "") {
        $msg = new Net_HL7_Message($msgStr, self::$_hl7Globals);

        return $msg;
    }

    /**
     * Create a new Net_HL7_Segments_MSH segment, using the global HL7
     * variables as defaults.
     * 
     * @return Net_HL7_Segments_MSH Net_HL7_Segments_MSH
     * @access public
     */
    static function &createMSH() {
        $msh = new Net_HL7_Segments_MSH(array(), self::$_hl7Globals);

        // set time of the message generation
        $msh->setField(7, strftime("%Y%m%d%H%M%S"));

        // Set ID field
        $msh->setField(10, $msh->getField(7) . rand(10000, 99999));

        return $msh;
    }

    /**
     * Creates an ACK message based on a given request.
     * @param Net_HL7_Message $req The request you are going to ACK
     * @return Net_HL7_Messages_ACK 
     */
    static function &createResponseFromRequest(Net_HL7_Message $req) {
        $response = new Net_HL7_Messages_ACK('', self::$_hl7Globals);

        $response->importRequest($req);

        return $response;
    }

    /**
     * Creates an ACK message based on the given ACK raw text
     * (it is useful to parse the remote server's ACK messages)
     * @param string $str
     * @return Net_HL7_Messages_ACK 
     */
    static function &createResponseFromString($str) {
        $response = new Net_HL7_Messages_ACK($str, self::$_hl7Globals);
        return $response;
    }

    /**
     * Set the component separator to be used by the factory. Should
     * be a single character. Default ^
     *
     * @param string Component separator char.
     * @return boolean true if value has been set.
     * @access public
     */
    static function setComponentSeparator($value) {
        if (strlen($value) != 1)
            return false;

        return self::_setGlobal('COMPONENT_SEPARATOR', $value);
    }

    /**
     * Set the subcomponent separator to be used by the factory. Should
     * be a single character. Default: &
     *
     * @param string Subcomponent separator char.
     * @return boolean true if value has been set.
     * @access public
     */
    static function setSubcomponentSeparator($value) {
        if (strlen($value) != 1)
            return false;

        return self::_setGlobal('SUBCOMPONENT_SEPARATOR', $value);
    }

    /**
     * Set the repetition separator to be used by the factory. Should
     * be a single character. Default: ~
     *
     * @param string Repetition separator char.
     * @return boolean true if value has been set.
     * @access public
     */
    static function setRepetitionSeparator($value) {
        if (strlen($value) != 1)
            return false;

        return self::_setGlobal('REPETITION_SEPARATOR', $value);
    }

    /**
     * Set the field separator to be used by the factory. Should
     * be a single character. Default: |
     *
     * @param string Field separator char.
     * @return boolean true if value has been set.
     * @access public
     */
    static function setFieldSeparator($value) {
        if (strlen($value) != 1)
            return false;

        return self::_setGlobal('FIELD_SEPARATOR', $value);
    }

    /**
     * Set the segment separator to be used by the factory. Should
     * be a single character. Default: \015
     *
     * @param string Segment separator char.
     * @return boolean true if value has been set.
     * @access public
     */
    static function setSegmentSeparator($value) {
        if (strlen($value) != 1)
            return false;

        return self::_setGlobal('SEGMENT_SEPARATOR', $value);
    }

    /**
     * Set the escape character to be used by the factory. Should
     * be a single character. Default: \
     *
     * @param string Escape character.
     * @return boolean true if value has been set.
     * @access public
     */
    static function setEscapeCharacter($value) {
        if (strlen($value) != 1)
            return false;

        return self::_setGlobal('ESCAPE_CHARACTER', $value);
    }

    /**
     * Set the HL7 version to be used by the factory.
     *
     * @param string HL7 version character.
     * @return boolean true if value has been set.
     * @access public
     */
    static function setHL7Version($value) {
        return self::_setGlobal('HL7_VERSION', $value);
    }

    /**
     * Set the NULL string to be used by the factory.
     *
     * @param string NULL string.
     * @return boolean true if value has been set.
     * @access public
     */
    static function setNull($value) {
        return self::_setGlobal('NULL', $value);
    }

    /**
     * Convenience method for obtaining the special NULL value.
     *
     * @return string null value
     * @access public
     */
    static function getNull() {
        return self::$_hl7Globals['NULL'];
    }

    /**
     * Set the HL7 global variable
     *
     * @access private
     * @param string name
     * @param string value
     * @return boolean True when value has been set, false otherwise.
     */
    private static function _setGlobal($name, $value) {
        self::$_hl7Globals[$name] = $value;

        return true;
    }

}

/* EOF */