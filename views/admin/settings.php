<?php
// Verificar autenticación y permisos de administrador
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../controllers/AuthController.php';
use Controllers\AuthController;

if (!AuthController::isAuthenticated() || !AuthController::isAdmin()) {
    header('Location: ../../login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - Panel de Administración</title>
    <link rel="stylesheet" href="../../public/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="admin-body">
    <?php include_once __DIR__ . '/sidebar.php'; ?>
    
    <main class="admin-content">
        <div class="admin-header">
            <h1>Configuración del Sistema</h1>
        </div>

        <div class="settings-container">
            <!-- Configuración General -->
            <div class="settings-card">
                <div class="settings-header">
                    <h3><i class="fas fa-cog"></i> Configuración General</h3>
                </div>
                <div class="settings-content">
                    <form class="settings-form">
                        <div class="form-group">
                            <label class="form-label">Nombre del Sitio</label>
                            <input type="text" class="form-input" value="El Profesor Hernán">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email de Contacto</label>
                            <input type="email" class="form-input" value="info@profesorhernan.com">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Moneda por Defecto</label>
                            <select class="form-select">
                                <option value="USD" selected>USD - Dólar Americano</option>
                                <option value="EUR">EUR - Euro</option>
                                <option value="COP">COP - Peso Colombiano</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </form>
                </div>
            </div>

            <!-- Configuración de Pagos -->
            <div class="settings-card">
                <div class="settings-header">
                    <h3><i class="fas fa-credit-card"></i> Configuración de Pagos</h3>
                </div>
                <div class="settings-content">
                    <form class="settings-form">
                        <div class="form-group">
                            <label class="form-label">Stripe Publishable Key</label>
                            <input type="text" class="form-input" placeholder="pk_test_...">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Stripe Secret Key</label>
                            <input type="password" class="form-input" placeholder="sk_test_...">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Modo de Prueba</label>
                            <div class="toggle-switch">
                                <input type="checkbox" id="testMode" checked>
                                <label for="testMode" class="toggle-label"></label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Guardar Configuración</button>
                    </form>
                </div>
            </div>

            <!-- Configuración de Email -->
            <div class="settings-card">
                <div class="settings-header">
                    <h3><i class="fas fa-envelope"></i> Configuración de Email</h3>
                </div>
                <div class="settings-content">
                    <form class="settings-form">
                        <div class="form-group">
                            <label class="form-label">Servidor SMTP</label>
                            <input type="text" class="form-input" placeholder="smtp.gmail.com">
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Puerto</label>
                                <input type="number" class="form-input" value="587">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Encriptación</label>
                                <select class="form-select">
                                    <option value="tls" selected>TLS</option>
                                    <option value="ssl">SSL</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Usuario SMTP</label>
                            <input type="email" class="form-input" placeholder="tu-email@gmail.com">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Contraseña SMTP</label>
                            <input type="password" class="form-input" placeholder="tu-contraseña">
                        </div>
                        <button type="submit" class="btn btn-primary">Guardar Configuración</button>
                    </form>
                </div>
            </div>

            <!-- Herramientas del Sistema -->
            <div class="settings-card">
                <div class="settings-header">
                    <h3><i class="fas fa-tools"></i> Herramientas del Sistema</h3>
                </div>
                <div class="settings-content">
                    <div class="tools-grid">
                        <div class="tool-item">
                            <div class="tool-icon">
                                <i class="fas fa-database"></i>
                            </div>
                            <div class="tool-info">
                                <h4>Respaldo de Base de Datos</h4>
                                <p>Crear un respaldo completo de la base de datos</p>
                                <button class="btn btn-secondary">Crear Respaldo</button>
                            </div>
                        </div>
                        
                        <div class="tool-item">
                            <div class="tool-icon">
                                <i class="fas fa-broom"></i>
                            </div>
                            <div class="tool-info">
                                <h4>Limpiar Caché</h4>
                                <p>Eliminar archivos temporales y caché del sistema</p>
                                <button class="btn btn-secondary">Limpiar Caché</button>
                            </div>
                        </div>
                        
                        <div class="tool-item">
                            <div class="tool-icon">
                                <i class="fas fa-chart-bar"></i>
                            </div>
                            <div class="tool-info">
                                <h4>Generar Reporte</h4>
                                <p>Crear reporte detallado de actividad del sistema</p>
                                <button class="btn btn-secondary">Generar Reporte</button>
                            </div>
                        </div>
                        
                        <div class="tool-item">
                            <div class="tool-icon">
                                <i class="fas fa-sync-alt"></i>
                            </div>
                            <div class="tool-info">
                                <h4>Actualizar Sistema</h4>
                                <p>Verificar y aplicar actualizaciones disponibles</p>
                                <button class="btn btn-secondary">Verificar Actualizaciones</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información del Sistema -->
            <div class="settings-card">
                <div class="settings-header">
                    <h3><i class="fas fa-info-circle"></i> Información del Sistema</h3>
                </div>
                <div class="settings-content">
                    <div class="system-info">
                        <div class="info-item">
                            <span class="info-label">Versión del Sistema:</span>
                            <span class="info-value">1.0.0</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Versión de PHP:</span>
                            <span class="info-value"><?php echo PHP_VERSION; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Servidor Web:</span>
                            <span class="info-value"><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Desconocido'; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Base de Datos:</span>
                            <span class="info-value">MySQL</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Última Actualización:</span>
                            <span class="info-value"><?php echo date('d/m/Y H:i'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Manejo de formularios
        document.querySelectorAll('.settings-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Simular guardado
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.textContent;
                
                submitBtn.textContent = 'Guardando...';
                submitBtn.disabled = true;
                
                setTimeout(() => {
                    submitBtn.textContent = 'Guardado ✓';
                    submitBtn.style.background = 'var(--success-color)';
                    
                    setTimeout(() => {
                        submitBtn.textContent = originalText;
                        submitBtn.disabled = false;
                        submitBtn.style.background = '';
                    }, 2000);
                }, 1000);
            });
        });

        // Herramientas del sistema
        document.querySelectorAll('.tool-item button').forEach(button => {
            button.addEventListener('click', function() {
                const originalText = this.textContent;
                this.textContent = 'Procesando...';
                this.disabled = true;
                
                setTimeout(() => {
                    this.textContent = 'Completado ✓';
                    this.style.background = 'var(--success-color)';
                    
                    setTimeout(() => {
                        this.textContent = originalText;
                        this.disabled = false;
                        this.style.background = '';
                    }, 2000);
                }, 2000);
            });
        });
    </script>

    <style>
        .settings-container {
            display: grid;
            gap: 2rem;
            max-width: 1200px;
        }

        .settings-card {
            background: var(--white);
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }

        .settings-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-color);
            background: var(--light-bg);
        }

        .settings-header h3 {
            margin: 0;
            color: var(--text-color);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .settings-content {
            padding: 2rem;
        }

        .settings-form {
            display: grid;
            gap: 1.5rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .toggle-switch {
            position: relative;
            display: inline-block;
        }

        .toggle-switch input {
            display: none;
        }

        .toggle-label {
            display: block;
            width: 50px;
            height: 24px;
            background: var(--border-color);
            border-radius: 12px;
            cursor: pointer;
            position: relative;
            transition: background 0.3s ease;
        }

        .toggle-label::after {
            content: '';
            position: absolute;
            top: 2px;
            left: 2px;
            width: 20px;
            height: 20px;
            background: white;
            border-radius: 50%;
            transition: transform 0.3s ease;
        }

        .toggle-switch input:checked + .toggle-label {
            background: var(--primary-color);
        }

        .toggle-switch input:checked + .toggle-label::after {
            transform: translateX(26px);
        }

        .tools-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .tool-item {
            display: flex;
            gap: 1rem;
            padding: 1.5rem;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            transition: border-color 0.3s ease;
        }

        .tool-item:hover {
            border-color: var(--primary-color);
        }

        .tool-icon {
            width: 50px;
            height: 50px;
            background: var(--primary-light);
            color: var(--primary-color);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .tool-info h4 {
            margin: 0 0 0.5rem 0;
            color: var(--text-color);
        }

        .tool-info p {
            margin: 0 0 1rem 0;
            color: var(--text-muted);
            font-size: 0.875rem;
        }

        .system-info {
            display: grid;
            gap: 1rem;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            background: var(--light-bg);
            border-radius: 8px;
        }

        .info-label {
            font-weight: 600;
            color: var(--text-color);
        }

        .info-value {
            color: var(--text-muted);
            font-family: monospace;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .tools-grid {
                grid-template-columns: 1fr;
            }
            
            .tool-item {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</body>
</html>
