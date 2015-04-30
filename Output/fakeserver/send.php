<?php
$emailContent = explode("[={\\|/}=]", file_get_contents('email.php'));
$innerEmailContent = "";
$finalEmail = "";

$formContent = file_get_contents('form.php');
$formJsonContent = json_decode($formContent, true);
$formFields = array();

foreach ($formJsonContent as $post => $value) {
    if (is_array($value)) {
        array_push($formFields, $post);
    }
}

foreach ($formFields as $i) {
    if (isset($_POST[$i])) {
        $innerEmailContent += $i . ": " . $_POST[$i] . ", <br>";
    }
}

$finalEmail = $emailContent[0] + $innerEmailContent + $emailContent[1];