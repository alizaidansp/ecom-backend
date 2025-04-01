<?php
function isValidPassword($password) {
    return strlen($password) >= 8 &&
           preg_match('/[A-Z]/', $password) &&      // At least one uppercase letter
           preg_match('/[a-z]/', $password) &&      // At least one lowercase letter
           preg_match('/\d/', $password) &&         // At least one digit
           preg_match('/[!@#$%^&*()_\-]/', $password); // At least one special character
}
?>
