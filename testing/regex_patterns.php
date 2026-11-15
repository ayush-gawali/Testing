<?php
// testing/regex_patterns.php - SINGLE SOURCE OF TRUTH for validation rules.
// PHP uses these patterns for server-side checks; includes/footer.php sends the same
// patterns to JavaScript (js/validation.js), so client and server can never disagree.
// Rules: patterns contain no "/" character (PHP wraps them in / /D, JS uses new RegExp).

function covicare_rules() {
    return [
        'name'     => ['pattern' => '^[A-Za-z][A-Za-z .\'-]{1,49}$',
                       'message' => 'Name must be 2-50 characters: letters, spaces, . \' - only.'],
        'email'    => ['pattern' => '^(?=.{6,100}$)[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$',
                       'message' => 'Enter a valid email address (e.g. name@example.com).'],
        'phone'    => ['pattern' => '^[6-9][0-9]{9}$',
                       'message' => 'Phone must be 10 digits and start with 6, 7, 8 or 9.'],
        'password' => ['pattern' => '^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).{8,20}$',
                       'message' => 'Password: 8-20 characters with uppercase, lowercase and a digit.'],
        'age'      => ['pattern' => '^(?:[1-9]|[1-9][0-9]|1[01][0-9]|120)$',
                       'message' => 'Age must be a whole number from 1 to 120.'],
        'date'     => ['pattern' => '^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])$',
                       'message' => 'Enter a valid date (YYYY-MM-DD).'],
        'comment'  => ['pattern' => '^[\s\S]{5,500}$',
                       'message' => 'Comment must be 5-500 characters.'],
    ];
}

function validate_field($rule, $value) {
    $r = covicare_rules();
    return isset($r[$rule]) && preg_match('/' . $r[$rule]['pattern'] . '/D', $value) === 1;  // D = strict end
}

function rule_message($rule) { return covicare_rules()[$rule]['message']; }
