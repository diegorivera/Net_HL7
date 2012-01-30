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
// $Id: Connection.php,v 1.7 2004/08/06 07:38:54 wyldebeast Exp $

/**
 * Usage:
 * <code>
 * $conn =& new Net_HL7_Connection('localhost', 8089);
 *
 * $req =& new Net_HL7_Message();
 * 
 * ... set some request attributes
 * 
 * $res = $conn->send($req);
 * 
 * $conn->close();
 * </code>
 *
 * The Net_HL7_Connection object represents the tcp connection to the
 * HL7 message broker. The Connection has only two useful methods
 * (apart from the constructor), send and close. The 'send' method
 * takes a Net_HL7_Message object as argument, and also returns a
 * Net_HL7_Message object. The send method can be used more than once,
 * before the connection is closed.
 *
 * The Connection object holds the following fields:
 *
 * _MESSAGE_PREFIX
 *
 * The prefix to be sent to the HL7 server to initiate the
 * message. Defaults to \013.
 *
 * _MESSAGE_SUFFIX
 * End of message signal for HL7 server. Defaults to \034\015.
 * 
 *
 * @version    0.10
 * @author     D.A.Dokter <dokter@w20e.com>
 * @access     public
 * @category   Networking
 * @package    Net_HL7
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 */
class Net_HL7_Connection {

    private $_HANDLE;
    private $_MESSAGE_PREFIX;
    private $_MESSAGE_SUFFIX;
    private $_MAX_READ;

    /**
     * Creates a connection to a HL7 server, or returns undef when a
     * connection could not be established.are:
     *
     * @param mixed Host to connect to
     * @param int Port to connect to
     * @return boolean
     */
    public function __construct($host, $port) {
        $this->_HANDLE = $this->_connect($host, $port);
        $this->_MESSAGE_PREFIX = "\013";
        $this->_MESSAGE_SUFFIX = "\034\015";
        $this->_MAX_READ = 8192;

        return true;
    }

    /**
     * Connect to specified host and port
     *
     * @param mixed Host to connect to
     * @param int Port to connect to
     * @return socket
     * @access private
     */
    private function _connect($host, $port) {
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($socket < 0) {
            throw new Exception("Create failed: " . socket_strerror($socket));
        }

        $result = socket_connect($socket, $host, $port);

        if (!$result) {
            throw new Exception("Connect failed: " . socket_strerror(socket_last_error()));
        }

        return $socket;
    }

    /**
     * Sends a Net_HL7_Message object over this connection.
     * 
     * @param Net_HL7_Message Instance of Net_HL7_Message
     * @return Net_HL7_Messages_ACK Instance of Net_HL7_Message_ACK (the response message)
     * @access public
     * @see Net_HL7_Message
     */
    function send(Net_HL7_Message $req) {

        $handle = $this->_HANDLE;
        $hl7Msg = $req->toString();

        if (!socket_write($handle, $this->_MESSAGE_PREFIX . $hl7Msg . $this->_MESSAGE_SUFFIX)) {
            throw new Exception("Could not write data to the socket: " . socket_strerror(socket_last_error()));
        }

        $data = "";
        
        $read_counter = 0;
        while (($buf = socket_read($handle, 256, PHP_BINARY_READ)) !== '') {
            $read_counter += 256;
            $data .= $buf;

            if ($read_counter > $this->_MAX_READ)
                break;

            if (preg_match("/" . $this->_MESSAGE_SUFFIX . "$/", $buf))
                break;
        }

        // Remove message prefix and suffix
        $data = preg_replace("/^" . $this->_MESSAGE_PREFIX . "/", "", $data);
        $data = preg_replace("/" . $this->_MESSAGE_SUFFIX . "$/", "", $data);
        
        if (!empty($data)){
            $resp = Net_HL7::createResponseFromString ($data);
        }
        else {
            throw new Exception("No response from server.");
        }

        return $resp;
    }

    /**
     * Close the connection.
     * 
     * @access public
     * @return boolean
     */
    function close() {
        socket_close($this->_HANDLE);
        return true;
    }

}

/* EOF */