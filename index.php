<?php
/*
MIT License

Copyright (c) 2022 Joseph Mark Taylor

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.


*/

// requires opsgenie url param

if (isset($_GET['url'])) {
    $url = $_GET['url'];


$json_object = file_get_contents('php://input');


$message = $json_object;

// The value returned needs unescaping

$str = json_decode($message, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);

//selects what the message source that needs replacing
$alarmmessage = $str['incident']['policy_name'];

//selects message target
$replacementData =  array('incident' =>  array('summary' =>  ("$alarmmessage")));

//replaces target with the source value. defined above.
$replace2 = array_replace_recursive($str, $replacementData);


$replace3 = json_encode($replace2);
$replace = json_decode($replace3, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);

// encodes the json and uses the URL param above thats sent in the URL to resubmit the json.
$content = json_encode($replace);
$curl = curl_init($url);
curl_setopt($curl, CURLOPT_HEADER, false);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HTTPHEADER,
        array("Content-type: application/json"));
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $content);

$json_response = curl_exec($curl);
$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

// throws an error if the status is gt http response 299.
if ( $status >= 299 ) {
    die("Error: call to URL $url failed with status $status, response $json_response, curl_error " . curl_error($curl) . curl_error($curl) . ", curl_errno " . curl_errno($curl));
}
curl_close($curl);

// echos the response from opsgenie
$response = json_decode($json_response, true);
echo $json_response;

} else {
    exit();
}
