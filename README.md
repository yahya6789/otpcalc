# otpcalc
OTP calculator PHP-REST

OTPCalc is implementation of HMAC-based OTP algorithm (RFC 4226, 6238).

GET Request
-----------

Generating counter-based otp with key 12345678901234567890 and counter 7

<pre>http://example.org/api/v1/hotp/key/12345678901234567890/counter/7</pre>


The response
------------

The response are hash value, trucated values of the hash in decimal and hex, and the OTP

<pre>{"error":false,"message":{"hash":"a4fb960c0bc06e1eabb804e5b397cdc4b45596fa","dec":82162583,"hex":"4e5b397","otp":"162583"}}</pre>
