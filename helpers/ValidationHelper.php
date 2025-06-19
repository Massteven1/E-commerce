<?php
namespace Helpers; // Añadir namespace

// Asegurarse de que SecurityHelper se incluya para sus funciones estáticas
require_once __DIR__ . '/SecurityHelper.php';

use Helpers\SecurityHelper;

class ValidationHelper {
    
    /**
     * Validar datos de checkout
     */
    public static function validateCheckoutData($data) {
        $errors = [];
        
        // Campos requeridos
        $requiredFields = [
            'first_name' => 'Nombre',
            'last_name' => 'Apellido', 
            'email' => 'Email',
            'phone' => 'Teléfono',
            'address' => 'Dirección',
            'city' => 'Ciudad',
            'state' => 'Estado/Provincia',
            'zip_code' => 'Código Postal',
            'country' => 'País'
        ];
        
        foreach ($requiredFields as $field => $label) {
            if (empty($data[$field]) || trim($data[$field]) === '') {
                $errors[] = "El campo {$label} es requerido.";
            }
        }
        
        // Validaciones específicas
        if (!empty($data['email']) && !SecurityHelper::validateEmail($data['email'])) {
            $errors[] = "El formato del email no es válido.";
        }
        
        if (!empty($data['phone']) && !SecurityHelper::validatePhone($data['phone'])) {
            $errors[] = "El formato del teléfono no es válido.";
        }
        
        // Validar longitudes
        if (!empty($data['first_name']) && strlen($data['first_name']) > 100) {
            $errors[] = "El nombre no puede exceder 100 caracteres.";
        }
        
        if (!empty($data['last_name']) && strlen($data['last_name']) > 100) {
            $errors[] = "El apellido no puede exceder 100 caracteres.";
        }
        
        if (!empty($data['address']) && strlen($data['address']) > 255) {
            $errors[] = "La dirección no puede exceder 255 caracteres.";
        }
        
        return $errors;
    }
    
    /**
     * Validar estructura del carrito
     */
    public static function validateCartStructure($cart) {
        if (!is_array($cart) || empty($cart)) {
            return false;
        }
        
        foreach ($cart as $item) {
            if (!is_array($item)) {
                continue; // Permitir IDs simples para compatibilidad
            }
            
            if (!isset($item['id']) || !is_numeric($item['id'])) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Validar monto de pago
     */
    public static function validatePaymentAmount($amount) {
        return is_numeric($amount) && $amount > 0 && $amount <= 10000; // Máximo $10,000
    }
}
?>
