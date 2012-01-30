<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

// +----------------------------------------------------------------------+
// | PHP version 4                                                        |
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
// $Id: ACK.php,v 1.5 2004/07/05 08:57:28 wyldebeast Exp $

class Net_HL7_Messages_ACK extends Net_HL7_Message {

    private $_ACK_TYPE;

    /**
     * Usage:
     * <code>
     * $ack = new Net_HL7_Messages_ACK($request);
     * </code>
     *
     * Convenience module implementing an acknowledgement (ACK) message. This
     * can be used in HL7 servers to create an acknowledgement for an
     * incoming message.
     *
     * @version    0.10
     * @author     D.A.Dokter <dokter@w20e.com>
     * @access     public
     * @category   Networking
     * @package    Net_HL7
     * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
     */
    public function __construct($msg = '', $hl7Globals = array()) {
        parent::__construct($msg, $hl7Globals);
    }

    /**
     * Set the acknowledgement code for the acknowledgement. Code should be
     * one of: A, E, R. Codes can be prepended with C or A, denoting enhanced
     * or normal acknowledge mode. This denotes: accept, general error and
     * reject respectively. The ACK module will determine the right answer
     *  mode (normal or enhanced) based upon the request, if not provided.
     * The message provided in $msg will be set in MSA 3.
     *
     * @param mixed Code to use in acknowledgement
     * @param mixed Acknowledgement message
     * @return boolean
     * @access public
     */
    function setAckCode($code, $msg = "") {
        $mode = "A";

        // Determine acknowledge mode: normal or enhanced
        //
        if ($this->_ACK_TYPE == "E") {
            $mode = "C";
        }

        if (strlen($code) == 1) {
            $code = "$mode$code";
        }

        $seg1 = $this->getSegmentByIndex(1);

        if ($seg1 instanceof Net_HL7_Segment) {

            $seg1->setField(1, $code);
            if ($msg)
                $seg1->setField(3, $msg);

            return true;
        } else {
            return false;
        }
    }

    /**
     * Set the error message for the acknowledgement. This will also set the
     * error code to either AE or CE, depending on the mode of the incoming
     * message.
     * 
     * @param mixed Error message
     * @return boolean
     * @access public
     */
    function setErrorMessage($msg) {
        $this->setAckCode("E", $msg);
        return true;
    }

    public function importRequest(Net_HL7_Message $req) {
        $reqMsh = $req->getSegmentByIndex(0);

        if ($reqMsh) {
            $msh = new Net_HL7_Segments_MSH($reqMsh->getFields(1));
        } else {
            $msh = new Net_HL7_Segments_MSH();
        }

        $msa = new Net_HL7_Segment("MSA");

        // Determine acknowledge mode: normal or enhanced
        //
        if ($msh->getField(15) || $msh->getField(16)) {
            $this->_ACK_TYPE = "E";
            $msa->setField(1, "CA");
        } else {
            $this->_ACK_TYPE = "N";
            $msa->setField(1, "AA");
        }

        $msh->setField(9, "ACK");

        // Construct an ACK based on the request
        if ($reqMsh) {
            $msh->setField(3, $reqMsh->getField(5));
            $msh->setField(4, $reqMsh->getField(6));
            $msh->setField(5, $reqMsh->getField(3));
            $msh->setField(6, $reqMsh->getField(4));
            $msa->setField(2, $reqMsh->getField(10));
        }

        $this->addSegment($msh);
        $this->addSegment($msa);
    }

}

/* EOF */