<?php
// No se requieren modelos específicos para el dashboard simple,
// pero si en el futuro se necesitan datos (ej. estadísticas),
// se importarían aquí los modelos correspondientes.

class AdminController {
    public function dashboard() {
        // Aquí podrías cargar datos para el dashboard si los tuvieras,
        // por ejemplo, estadísticas de usuarios, productos, ventas, etc.
        // $users_count = ...;
        // $products_count = ...;
        // $monthly_sales = ...;
        // $new_comments = ...;

        // Incluye la vista del dashboard
        require_once __DIR__ . '/../views/admin/dashboard.php';
    }
}
?>
