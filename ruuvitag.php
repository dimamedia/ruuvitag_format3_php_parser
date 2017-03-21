<?php
/*
Example for RuuviTag format 3 (current sensor readings)

PHP parse by Dima Tsvetkov

RAW demo: 0x02010411FF99040336175ABFF8FFE5FFCA03D00B4D
        We are using this |__________________________|

Company reserved ID: 0x0499 
Data: 0x0336175ABFF8FFE5FFCA03D00B4D

HEX2BIN:
Offs.	HEX		BIN			VAL
0:	03		00000011		3	Format
1:	36		00110110		54	Humidity *0.5
2:	17		00010111		23	Temp, signed
3:	5A		01011010		90	Temp fraction 1/100
4-5:	BFF8	        1011111111111000	49144	Pressure Pa
6-7:	FFE5	        1111111111100101	-27	Acceleration-X, signed
8-9:	FFCA	        1111111111001010	-54	Acceleration-Y, signed
10-11:	03D0	        0000001111010000	976	Acceleration-Z, signed
12-13:	0B4D	        0000101101001101	2893	Battery voltage millivolts

*/

function _bin16dec($bin) {
    // converts 16bit binary number string to integer using two's complement
    $num = bindec($bin) & 0xFFFF; // only use bottom 16 bits
    if (0x8000 & $num) {
        $num = - (0x010000 - $num);
    }
    return $num;
} // Thanks: http://stackoverflow.com/a/16127799

// set hex from GET or use default demo, we take only last 28 characters containing the data we need
$hex = isset($_GET['hex']) ? substr($_GET['hex'], -28) : "0336175ABFF8FFE5FFCA03D00B4D";

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
<!doctype html>
<html class="no-js" lang="en" style="height: 100%;">
<head>
<meta charset="utf-8">
<meta http-equiv="x-ua-compatible" content="ie=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>RuuviTag data format 3 hex converter</title>
</head>
<body>
<h1>RuuviTag data format 3 hex converter</h1>
<form method="get">
You can put here any hex data you get from the tag. Raw data, Manufacture data or BT Core 4.1 data. Parser takes only last 28 chars.<br />
For example, you can copy raw data from <b>nRF Connect</b> -app.<br />
<br />
<input type="text" name="hex" value="$hex" size="70" style="font-size: 14px; padding: 4px;"> <input style="font-size: 14px; padding: 4px;" type="submit" value="Parse">
</form>
<br />
<table>
<tr><td>Data format: </td><td>$format</td></tr>
<tr><td>Humidity: </td><td>$humidity</td></tr>
<tr><td>Temperature: </td><td>$temperature</td></tr>
<tr><td>Pressure: </td><td>$pressure</td></tr>
<tr><td>Acceleration X: </td><td>$accelerationX</td></tr>
<tr><td>Acceleration Y: </td><td>$accelerationY</td></tr>
<tr><td>Acceleration Z: </td><td>$accelerationZ</td></tr>
<tr><td>Voltage: </td><td>$voltage</td></tr>
</table>
</body>
</html>
html;

?>
