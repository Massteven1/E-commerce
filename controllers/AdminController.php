<?php
namespace Controllers;

// Asegúrate de que la sesión esté iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Playlist.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Order.php';

use Controllers\AuthController;
use Config\Database;
use Models\Playlist;
use Models\User;
use Models\Order;

class AdminController {
    private $db;
    
    public function __construct() {
        // Verificar que el usuario sea administrador
        if (!AuthController::isAdmin()) {
            AuthController::setFlashMessage('error', 'Acceso denegado.');
            header('Location: ../../login.php');
            exit();
        }
        
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    public function dashboard() {
        // Obtener estadísticas
        $stats = $this->getDashboardStats();
        $recentOrders = $this->getRecentOrders();
        $recentUsers = $this->getRecentUsers();
        $popularCourses = $this->getPopularCourses();
        
        require_once __DIR__ . '/../views/admin/dashboard.php';
    }
    
    /**
     * Obtiene todos los datos necesarios para el dashboard
     * @return array
     */
    public function getDashboardData() {
        try {
            return [
                'stats' => $this->getDashboardStats(),
                'recentOrders' => $this->getRecentOrders(),
                'recentUsers' => $this->getRecentUsers(),
                'popularCourses' => $this->getPopularCourses(),
                'monthlyStats' => $this->getMonthlyStats(),
                'topSellingCourses' => $this->getTopSellingCourses(),
                'userGrowth' => $this->getUserGrowthStats(),
                'revenueStats' => $this->getRevenueStats()
            ];
        } catch (\Exception $e) {
            error_log("Error obteniendo datos del dashboard: " . $e->getMessage());
            return [
                'stats' => [
                    'total_courses' => 0,
                    'total_users' => 0,
                    'total_orders' => 0,
                    'total_revenue' => 0
                ],
                'recentOrders' => [],
                'recentUsers' => [],
                'popularCourses' => [],
                'monthlyStats' => [],
                'topSellingCourses' => [],
                'userGrowth' => [],
                'revenueStats' => []
            ];
        }
    }
    
    public function courses() {
        $playlistModel = new Playlist($this->db);
        $playlists = $playlistModel->readAll();
        require_once __DIR__ . '/../views/admin/courses.php';
    }
    
    public function users() {
        $userModel = new User($this->db);
        $users = $userModel->readAll();
        require_once __DIR__ . '/../views/admin/users.php';
    }
    
    public function orders() {
        $orderModel = new Order($this->db);
        $orders = $orderModel->readAll();
        require_once __DIR__ . '/../views/admin/orders.php';
    }
    
    public function settings() {
        require_once __DIR__ . '/../views/admin/settings.php';
    }
    
    private function getDashboardStats() {
        try {
            $stats = [];
            
            // Total de cursos
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM playlists");
            $stmt->execute();
            $stats['total_courses'] = $stmt->fetch(\PDO::FETCH_ASSOC)['total'];
            
            // Total de usuarios
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
            $stmt->execute();
            $stats['total_users'] = $stmt->fetch(\PDO::FETCH_ASSOC)['total'];
            
            // Total de pedidos
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM orders");
            $stmt->execute();
            $stats['total_orders'] = $stmt->fetch(\PDO::FETCH_ASSOC)['total'];
            
            // Ingresos totales
            $stmt = $this->db->prepare("SELECT SUM(amount) as total FROM orders WHERE status = 'completed'");
            $stmt->execute();
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            $stats['total_revenue'] = $result['total'] ?? 0;
            
            // Estadísticas adicionales
            $stats['pending_orders'] = $this->getPendingOrdersCount();
            $stats['active_users'] = $this->getActiveUsersCount();
            $stats['monthly_revenue'] = $this->getMonthlyRevenue();
            $stats['growth_percentage'] = $this->getGrowthPercentage();
            
            return $stats;
        } catch (\Exception $e) {
            error_log("Error obteniendo estadísticas: " . $e->getMessage());
            return [
                'total_courses' => 0,
                'total_users' => 0,
                'total_orders' => 0,
                'total_revenue' => 0,
                'pending_orders' => 0,
                'active_users' => 0,
                'monthly_revenue' => 0,
                'growth_percentage' => 0
            ];
        }
    }
    
    private function getRecentOrders() {
        try {
            $stmt = $this->db->prepare("
                SELECT o.*, u.first_name, u.last_name, u.email
                FROM orders o 
                LEFT JOIN users u ON o.user_id = u.id 
                ORDER BY o.created_at DESC 
                LIMIT 10
            ");
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Error obteniendo pedidos recientes: " . $e->getMessage());
            return [];
        }
    }
    
    private function getRecentUsers() {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM users 
                WHERE role = 'user' 
                ORDER BY created_at DESC 
                LIMIT 10
            ");
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Error obteniendo usuarios recientes: " . $e->getMessage());
            return [];
        }
    }
    
    private function getPopularCourses() {
        try {
            $stmt = $this->db->prepare("
                SELECT p.*, COUNT(uc.id) as enrollments,
                       SUM(o.amount) as total_revenue
                FROM playlists p
                LEFT JOIN user_courses uc ON p.id = uc.playlist_id
                LEFT JOIN orders o ON uc.order_id = o.id AND o.status = 'completed'
                GROUP BY p.id
                ORDER BY enrollments DESC, total_revenue DESC
                LIMIT 10
            ");
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Error obteniendo cursos populares: " . $e->getMessage());
            return [];
        }
    }
    
    private function getMonthlyStats() {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as month,
                    COUNT(*) as orders_count,
                    SUM(amount) as revenue
                FROM orders 
                WHERE status = 'completed' 
                    AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                ORDER BY month ASC
            ");
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Error obteniendo estadísticas mensuales: " . $e->getMessage());
            return [];
        }
    }
    
    private function getTopSellingCourses() {
        try {
            $stmt = $this->db->prepare("
                SELECT p.*, COUNT(uc.id) as sales_count,
                       SUM(o.amount) as total_revenue
                FROM playlists p
                JOIN user_courses uc ON p.id = uc.playlist_id
                JOIN orders o ON uc.order_id = o.id
                WHERE o.status = 'completed'
                GROUP BY p.id
                ORDER BY sales_count DESC, total_revenue DESC
                LIMIT 5
            ");
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Error obteniendo cursos más vendidos: " . $e->getMessage());
            return [];
        }
    }
    
    private function getUserGrowthStats() {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as month,
                    COUNT(*) as new_users
                FROM users 
                WHERE role = 'user' 
                    AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                ORDER BY month ASC
            ");
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Error obteniendo estadísticas de crecimiento: " . $e->getMessage());
            return [];
        }
    }
    
    private function getRevenueStats() {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    DATE_FORMAT(created_at, '%Y-%m-%d') as date,
                    SUM(amount) as daily_revenue
                FROM orders 
                WHERE status = 'completed' 
                    AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY DATE_FORMAT(created_at, '%Y-%m-%d')
                ORDER BY date ASC
            ");
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Error obteniendo estadísticas de ingresos: " . $e->getMessage());
            return [];
        }
    }
    
    private function getPendingOrdersCount() {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'");
            $stmt->execute();
            return $stmt->fetch(\PDO::FETCH_ASSOC)['count'];
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    private function getActiveUsersCount() {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(DISTINCT user_id) as count 
                FROM orders 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ");
            $stmt->execute();
            return $stmt->fetch(\PDO::FETCH_ASSOC)['count'];
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    private function getMonthlyRevenue() {
        try {
            $stmt = $this->db->prepare("
                SELECT SUM(amount) as revenue 
                FROM orders 
                WHERE status = 'completed' 
                    AND MONTH(created_at) = MONTH(NOW()) 
                    AND YEAR(created_at) = YEAR(NOW())
            ");
            $stmt->execute();
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $result['revenue'] ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    private function getGrowthPercentage() {
        try {
            // Ingresos del mes actual
            $stmt = $this->db->prepare("
                SELECT SUM(amount) as current_month 
                FROM orders 
                WHERE status = 'completed' 
                    AND MONTH(created_at) = MONTH(NOW()) 
                    AND YEAR(created_at) = YEAR(NOW())
            ");
            $stmt->execute();
            $currentMonth = $stmt->fetch(\PDO::FETCH_ASSOC)['current_month'] ?? 0;
            
            // Ingresos del mes anterior
            $stmt = $this->db->prepare("
                SELECT SUM(amount) as previous_month 
                FROM orders 
                WHERE status = 'completed' 
                    AND MONTH(created_at) = MONTH(DATE_SUB(NOW(), INTERVAL 1 MONTH)) 
                    AND YEAR(created_at) = YEAR(DATE_SUB(NOW(), INTERVAL 1 MONTH))
            ");
            $stmt->execute();
            $previousMonth = $stmt->fetch(\PDO::FETCH_ASSOC)['previous_month'] ?? 0;
            
            if ($previousMonth > 0) {
                return round((($currentMonth - $previousMonth) / $previousMonth) * 100, 2);
            }
            
            return 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function handleAjax() {
        if (!isset($_POST['action'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Acción no especificada']);
            return;
        }

        $action = $_POST['action'];

        switch ($action) {
            case 'toggle_user_status':
                $this->toggleUserStatus();
                break;
            case 'update_order_status':
                $this->updateOrderStatus();
                break;
            case 'get_course_stats':
                $this->getCourseStats();
                break;
            case 'get_dashboard_data':
                echo json_encode($this->getDashboardData());
                break;
            case 'refresh_stats':
                echo json_encode($this->getDashboardStats());
                break;
            default:
                http_response_code(400);
                echo json_encode(['error' => 'Acción no válida']);
        }
    }

    private function toggleUserStatus() {
        $userId = $_POST['user_id'] ?? null;
        $newStatus = $_POST['status'] ?? null;

        if (!$userId || $newStatus === null) {
            echo json_encode(['error' => 'Datos incompletos']);
            return;
        }

        try {
            $query = "UPDATE users SET is_active = ? WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $success = $stmt->execute([$newStatus, $userId]);
            echo json_encode(['success' => $success]);
        } catch (\Exception $e) {
            echo json_encode(['error' => 'Error al actualizar el estado del usuario']);
        }
    }

    private function updateOrderStatus() {
        $orderId = $_POST['order_id'] ?? null;
        $newStatus = $_POST['status'] ?? null;

        if (!$orderId || !$newStatus) {
            echo json_encode(['error' => 'Datos incompletos']);
            return;
        }

        try {
            $query = "UPDATE orders SET status = ? WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $success = $stmt->execute([$newStatus, $orderId]);
            echo json_encode(['success' => $success]);
        } catch (\Exception $e) {
            echo json_encode(['error' => 'Error al actualizar el estado del pedido']);
        }
    }

    private function getCourseStats() {
        $courseId = $_POST['course_id'] ?? null;

        if (!$courseId) {
            echo json_encode(['error' => 'ID del curso no especificado']);
            return;
        }

        try {
            $query = "SELECT 
                        COUNT(uc.playlist_id) as total_enrollments,
                        SUM(o.amount) as total_revenue
                      FROM user_courses uc
                      JOIN orders o ON uc.order_id = o.id
                      WHERE uc.playlist_id = ? AND o.status = 'completed'";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$courseId]);
            $stats = $stmt->fetch(\PDO::FETCH_ASSOC);
            echo json_encode($stats);
        } catch (\Exception $e) {
            echo json_encode(['error' => 'Error al obtener estadísticas del curso']);
        }
    }
    
    /**
     * Obtiene estadísticas específicas para un período
     */
    public function getStatsForPeriod($period = '30days') {
        try {
            $dateCondition = '';
            switch ($period) {
                case '7days':
                    $dateCondition = 'DATE_SUB(NOW(), INTERVAL 7 DAY)';
                    break;
                case '30days':
                    $dateCondition = 'DATE_SUB(NOW(), INTERVAL 30 DAY)';
                    break;
                case '90days':
                    $dateCondition = 'DATE_SUB(NOW(), INTERVAL 90 DAY)';
                    break;
                case '1year':
                    $dateCondition = 'DATE_SUB(NOW(), INTERVAL 1 YEAR)';
                    break;
                default:
                    $dateCondition = 'DATE_SUB(NOW(), INTERVAL 30 DAY)';
            }
            
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(DISTINCT o.id) as orders_count,
                    SUM(o.amount) as total_revenue,
                    COUNT(DISTINCT o.user_id) as unique_customers,
                    AVG(o.amount) as average_order_value
                FROM orders o
                WHERE o.status = 'completed' 
                    AND o.created_at >= {$dateCondition}
            ");
            $stmt->execute();
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Error obteniendo estadísticas del período: " . $e->getMessage());
            return [
                'orders_count' => 0,
                'total_revenue' => 0,
                'unique_customers' => 0,
                'average_order_value' => 0
            ];
        }
    }
}
