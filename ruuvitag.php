<?php
/*
Example for RuuviTag format 3 (current sensor readings)

PHP parse by Dima Tsvetkov

RAW demo: 0x02010411FF99040336175ABFF8FFE5FFCA03D00B4D
        We are using this |__________________________|

Company reserved ID: 0x0499 
Data: 0x0336175ABFF8FFE5FFCA03D00B4D

HEX2BIN:
Offs.	HEX		BIN					VAL
0:		03		00000011			3		Format
1:		36		00110110			54		Humidity *0.5
2:		17		00010111			23		Temp, signed
3:		5A		01011010			90		Temp fraction 1/100
4-5:	BFF8	1011111111111000	49144	Pressure Pa
6-7:	FFE5	1111111111100101	-27		Acceleration-X, signed
8-9:	FFCA	1111111111001010	-54		Acceleration-Y, signed
10-11:	03D0	0000001111010000	976		Acceleration-Z, signed
12-13:	0B4D	0000101101001101	2893	Battery voltage millivolts

*/

function _bin16dec($bin) {
    // converts 16bit binary number string to integer using two's complement
    $num = bindec($bin) & 0xFFFF; // only use bottom 16 bits
    if (0x8000 & $num) {
        $num = - (0x010000 - $num);
    }
    return $num;
} // Thanks: http://stackoverflow.com/a/16127799

// set hex from GET or use default demo
$hex = isset($_GET['hex']) ? str_replace("0x","",$_GET['hex']) : "0336175ABFF8FFE5FFCA03D00B4D";

// convert hex to binary
$bin = hex2bin($hex);

// unpacking binary chunks
$dataArray = unpack("C1format/C1humidity/c1temp/C1tempfract/n1pressure/C2accelerationX/C2accelerationY/C2accelerationZ/n1voltage",$bin);

//print "<pre>".print_r($dataArray,true)."</pre>";

$format = $dataArray['format'];
$humidity = ($dataArray['humidity']*0.5)." %";
$temperature = $dataArray['temp'].".".$dataArray['tempfract']." °C";
$pressure = round(($dataArray['pressure']+50000)/1000,2)." kPa";
$accelerationX = _bin16dec(base_convert($dataArray['accelerationX1'],10,2).base_convert($dataArray['accelerationX2'],10,2))." mG";
$accelerationY = _bin16dec(base_convert($dataArray['accelerationY1'],10,2).base_convert($dataArray['accelerationY2'],10,2))." mG";
$accelerationZ = _bin16dec(base_convert($dataArray['accelerationZ1'],10,2).base_convert($dataArray['accelerationZ2'],10,2))." mG";
$voltage = $dataArray['voltage']." mV";

print <<< html
<h1>RuuviTag data format 3 hex converter</h1>
<form method="get">
Hex: <input type="text" name="hex" value="$hex" size="42"> <input type="submit" value="Convert">
</form>
<br />
<table>
<tr><td>Data format:</td><td>$format</td></tr>
<tr><td>Humidity:</td><td>$humidity</td></tr>
<tr><td>Temperature:</td><td>$temperature</td></tr>
<tr><td>Pressure</td><td>$pressure</td></tr>
<tr><td>Acceleration X</td><td>$accelerationX</td></tr>
<tr><td>Acceleration Y</td><td>$accelerationY</td></tr>
<tr><td>Acceleration Z</td><td>$accelerationZ</td></tr>
<tr><td>Voltage</td><td>$voltage</td></tr>
</table>
html;

?>
