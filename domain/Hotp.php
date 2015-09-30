<?php

namespace otpcalc\domain;

/**
 * HMAC-based OTP (HOTP) implementation <p>
 *
 * @link http://www.ietf.org/rfc/rfc4226.txt <br>
 * @link http://www.ietf.org/rfc/rfc6238.txt <br> <p>
 *
 *
 * @author Yahya <yahya6789@gmail.com>
 *
 */

class Hotp
{
    /**
     * Generate counter/event-based OTP. <p>
     *
     * @param string    $key <p>
     * The shared secret in ASCII string
     *
     * @param integer   $counter <p>
     * The counter, time or other value that changes on a per use basis.
     *
     * @param integer   $length <p>
     * The number of digits in the OTP, not including the checksum, if any. (default is 6)
     *
     * @return array <p>
     * Array containing hash value, truncated value in dec and hex, and the otp.
     *
     */
    public function generateCounterBasedOtp($key, $counter, $length = 6)
    {
        $text = array ('0','0','0','0','0','0','0','0');
        for($i = 7; $i >= 0; $i--)
        {
            // take the last byte of the 4-bytes counter and assign it to $text
            $text[$i] = chr($counter & 0xFF);

            // or we can use 'pack' function
            //$text[$i] = pack('C*', $counter);

            $counter >>= 8;
        }

        // add '0' padding if necessary
        $text = implode($text);
        if(strlen($text < 8))
        {
            $text = str_repeat(chr(0), 8 - strlen($text)) . $text;
        }

        // compute hmac hash
        $hash = hash_hmac('sha1', $text, $key);

        // get truncated value of the hash in dec
        $hmacResult = array();
        foreach(str_split($hash, 2) as $hex)
        {
            $hmacResult[] = hexdec(($hex));
        }

        $offset = $hmacResult[19] & 0xf;
        $decValue = (
            (($hmacResult[$offset + 0] & 0x7f) << 24) |
            (($hmacResult[$offset + 1] & 0xff) << 16) |
            (($hmacResult[$offset + 2] & 0xff) << 8) |
            ($hmacResult[$offset + 3] & 0xff)
        );

        // form the otp
        $otp = str_pad($decValue, $length, "0", STR_PAD_LEFT);
        $otp = substr($otp, (-1 * $length));

        $result = array(
            'hash'  => $hash,
            'dec'   => $decValue,
            'hex'   => dechex($decValue),
            'otp'   => $otp
        );

        return $result;
    }

    /**
     * Generate time-based OTP. <p>
     *
     * @param string    $key <p>
     * The shared secret in ASCII string
     *
     * @param integer   $time <p>
     * UNIX timestamp to start counting time steps (default is current time).
     *
     * @param integer   $timestep <p>
     * The time step in seconds (default is = 30 seconds).
     *
     * @param integer   $length <p>
     * The number of digits in the OTP, not including the checksum, if any. (default is 6)
     *
     * @return array <p>
     * Array containing hash value, truncated value in dec and hex, and the otp.
     *
     * @throws \Exception <p>
     * Unix's time year 2038 problem
     *
     */
    public function generateTimeBasedOtp($key, $time = NULL, $timestep = 30, $length = 6) {
        if($time === NULL)
        {
            $time = $this->getUnixTimestamp();
        }

        $counter = intval($time / $timestep);

        // generate OTP
        $otp = $this->generateCounterBasedOtp($key, $counter, $length);

        // pad-left the 'value of T' with '0'
        $t = str_pad(dechex($counter), 16, "0", STR_PAD_LEFT);

        // for now we handle 2038 year problem by throwing an exception
        if(PHP_INT_SIZE == 4 && $time > PHP_INT_MAX)
        {
            throw new \Exception('This app is running on 32-bit
                which cannot encode times after 03:14:07 UTC on 19 January 2038.
                Please upgrade to 64-bit');
        }

        $result = array(
            'time'  => $time,
            'utc'   => date("Y-m-d H:i:s", $time),
            't'     => $t,
            'otp'   => $otp['otp'],
        );

        return $result;
    }

    /**
     * Get UNIX / UTC time
     *
     * @return integer current UNIX time (always UTC)
     */
    private function getUnixTimestamp()
    {
        return time();
    }
}