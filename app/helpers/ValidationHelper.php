<?php
// Helpers pour validation
class ValidationHelper {
    public static function validate($data, $rules) {
        $errors = [];
        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;
            $rule_list = is_array($rule) ? $rule : explode('|', $rule);

            foreach ($rule_list as $r) {
                $r = trim($r);
                if ($r === 'required' && empty($value)) {
                    $errors[$field] = "Le champ $field est obligatoire";
                } elseif (strpos($r, 'min:') === 0) {
                    $min = (int)substr($r, 4);
                    if (!empty($value) && strlen($value) < $min) {
                        $errors[$field] = "Le champ $field doit contenir au moins $min caractères";
                    }
                } elseif (strpos($r, 'max:') === 0) {
                    $max = (int)substr($r, 4);
                    if (!empty($value) && strlen($value) > $max) {
                        $errors[$field] = "Le champ $field ne doit pas dépasser $max caractères";
                    }
                } elseif ($r === 'email' && !empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field] = "Le champ $field doit être une adresse email valide";
                } elseif ($r === 'numeric' && !empty($value) && !is_numeric($value)) {
                    $errors[$field] = "Le champ $field doit être numérique";
                } elseif ($r === 'date' && !empty($value) && !self::isValidDate($value)) {
                    $errors[$field] = "Le champ $field doit être une date valide";
                } elseif ($r === 'phone' && !empty($value) && !self::isValidPhone($value)) {
                    $errors[$field] = "Le champ $field doit être un numéro de téléphone valide";
                }
            }
        }
        return $errors;
    }

    public static function isValidDate($date) {
        $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y'];
        foreach ($formats as $format) {
            $d = \DateTime::createFromFormat($format, $date);
            if ($d && $d->format($format) === $date) return true;
        }
        return false;
    }

    public static function isValidPhone($phone) {
        return preg_match('/^[0-9\s\-\+\(\)]+$/', $phone) && strlen(preg_replace('/[^0-9]/', '', $phone)) >= 9;
    }

    public static function isValidPostalCode($code) {
        return preg_match('/^[0-9]{5}$/', $code);
    }

    public static function isStrongPassword($password) {
        return strlen($password) >= 8 &&
               preg_match('/[A-Z]/', $password) &&
               preg_match('/[a-z]/', $password) &&
               preg_match('/[0-9]/', $password);
    }
}
