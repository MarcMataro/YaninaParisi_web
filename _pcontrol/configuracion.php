<?php
session_start();

// Verificar si l'usuari ha iniciat sessió
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

// Dades de configuració (després es connectarà amb BD)
$config = [
    'nombre' => 'Yanina Parisi',
    'email' => 'yanina@psicologiayanina.com',
    'telefono' => '+34 972 123 45 67',
    'colegiada' => 'COL-12345',
    'direccion' => 'Carrer de la Pau, 23, Girona',
    'especialidades' => ['Terapia Cognitivo-Conductual', 'Terapia de Pareja', 'Terapia Infantil'],
    'duracion_sesion' => 60,
    'tiempo_descanso' => 15,
    'horario_inicio' => '09:00',
    'horario_fin' => '20:00',
    'tarifas' => [
        'individual' => 60,
        'pareja' => 80,
        'online' => 50,
        'infantil' => 55
    ],
    'notificaciones_email' => true,
    'notificaciones_citas' => true,
    'idioma' => 'es'
];

// Procesar formulario si se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Aquí se procesarían los cambios (guardar en BD)
    $_SESSION['config_saved'] = true;
    header('Location: configuracion.php?saved=1');
    exit;
}

$saved = isset($_GET['saved']) && $_GET['saved'] == '1';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - Panel de Control</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/configuracion.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <header class="top-bar">
            <div class="top-bar-left">
                <button class="menu-toggle" id="menuToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="top-bar-info">
                    <h1>Configuración</h1>
                    <p class="date-today">Personaliza tu panel de control</p>
                </div>
            </div>
            <div class="top-bar-right">
                <button class="notification-btn">
                    <i class="fas fa-bell"></i>
                    <span class="badge">3</span>
                </button>
                <div class="user-profile">
                    <img src="../img/Logo.png" alt="Profile" class="profile-img">
                    <span class="profile-name">Yanina P.</span>
                </div>
            </div>
        </header>

        <!-- Configuration Content -->
        <div class="config-container">
            <?php if ($saved): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span>Los cambios se han guardado correctamente</span>
                <button class="alert-close" onclick="this.parentElement.style.display='none'">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <?php endif; ?>

            <form action="configuracion.php" method="POST" class="config-form">
                
                <!-- Perfil Profesional -->
                <section class="config-section">
                    <div class="section-header">
                        <h2><i class="fas fa-user-md"></i> Perfil Profesional</h2>
                        <p>Información básica sobre tu práctica profesional</p>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nombre">Nombre completo</label>
                            <input type="text" id="nombre" name="nombre" value="<?php echo $config['nombre']; ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="colegiada">Número de colegiada</label>
                            <input type="text" id="colegiada" name="colegiada" value="<?php echo $config['colegiada']; ?>" required>
                        </div>

                        <div class="form-group full-width">
                            <label for="especialidades">Especialidades</label>
                            <input type="text" id="especialidades" name="especialidades" 
                                   value="<?php echo implode(', ', $config['especialidades']); ?>" 
                                   placeholder="Separadas por comas">
                        </div>
                    </div>
                </section>

                <!-- Datos de Contacto -->
                <section class="config-section">
                    <div class="section-header">
                        <h2><i class="fas fa-address-book"></i> Datos de Contacto</h2>
                        <p>Información de contacto visible para los pacientes</p>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <div class="input-icon">
                                <i class="fas fa-envelope"></i>
                                <input type="email" id="email" name="email" value="<?php echo $config['email']; ?>" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="telefono">Teléfono</label>
                            <div class="input-icon">
                                <i class="fas fa-phone"></i>
                                <input type="tel" id="telefono" name="telefono" value="<?php echo $config['telefono']; ?>" required>
                            </div>
                        </div>

                        <div class="form-group full-width">
                            <label for="direccion">Dirección de consulta</label>
                            <div class="input-icon">
                                <i class="fas fa-map-marker-alt"></i>
                                <input type="text" id="direccion" name="direccion" value="<?php echo $config['direccion']; ?>" required>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Horario de Consulta -->
                <section class="config-section">
                    <div class="section-header">
                        <h2><i class="fas fa-clock"></i> Horario de Consulta</h2>
                        <p>Define tu disponibilidad para las citas</p>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="horario_inicio">Hora de inicio</label>
                            <input type="time" id="horario_inicio" name="horario_inicio" value="<?php echo $config['horario_inicio']; ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="horario_fin">Hora de fin</label>
                            <input type="time" id="horario_fin" name="horario_fin" value="<?php echo $config['horario_fin']; ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="duracion_sesion">Duración de sesión (minutos)</label>
                            <select id="duracion_sesion" name="duracion_sesion">
                                <option value="45" <?php echo $config['duracion_sesion'] == 45 ? 'selected' : ''; ?>>45 minutos</option>
                                <option value="50" <?php echo $config['duracion_sesion'] == 50 ? 'selected' : ''; ?>>50 minutos</option>
                                <option value="60" <?php echo $config['duracion_sesion'] == 60 ? 'selected' : ''; ?>>60 minutos</option>
                                <option value="75" <?php echo $config['duracion_sesion'] == 75 ? 'selected' : ''; ?>>75 minutos</option>
                                <option value="90" <?php echo $config['duracion_sesion'] == 90 ? 'selected' : ''; ?>>90 minutos</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="tiempo_descanso">Tiempo entre citas (minutos)</label>
                            <select id="tiempo_descanso" name="tiempo_descanso">
                                <option value="0" <?php echo $config['tiempo_descanso'] == 0 ? 'selected' : ''; ?>>Sin descanso</option>
                                <option value="10" <?php echo $config['tiempo_descanso'] == 10 ? 'selected' : ''; ?>>10 minutos</option>
                                <option value="15" <?php echo $config['tiempo_descanso'] == 15 ? 'selected' : ''; ?>>15 minutos</option>
                                <option value="20" <?php echo $config['tiempo_descanso'] == 20 ? 'selected' : ''; ?>>20 minutos</option>
                                <option value="30" <?php echo $config['tiempo_descanso'] == 30 ? 'selected' : ''; ?>>30 minutos</option>
                            </select>
                        </div>
                    </div>
                </section>

                <!-- Tarifas -->
                <section class="config-section">
                    <div class="section-header">
                        <h2><i class="fas fa-euro-sign"></i> Tarifas</h2>
                        <p>Precios por tipo de sesión</p>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="tarifa_individual">Sesión Individual (€)</label>
                            <div class="input-icon">
                                <i class="fas fa-euro-sign"></i>
                                <input type="number" id="tarifa_individual" name="tarifa_individual" 
                                       value="<?php echo $config['tarifas']['individual']; ?>" min="0" step="5" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="tarifa_pareja">Terapia de Pareja (€)</label>
                            <div class="input-icon">
                                <i class="fas fa-euro-sign"></i>
                                <input type="number" id="tarifa_pareja" name="tarifa_pareja" 
                                       value="<?php echo $config['tarifas']['pareja']; ?>" min="0" step="5" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="tarifa_online">Terapia Online (€)</label>
                            <div class="input-icon">
                                <i class="fas fa-euro-sign"></i>
                                <input type="number" id="tarifa_online" name="tarifa_online" 
                                       value="<?php echo $config['tarifas']['online']; ?>" min="0" step="5" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="tarifa_infantil">Terapia Infantil (€)</label>
                            <div class="input-icon">
                                <i class="fas fa-euro-sign"></i>
                                <input type="number" id="tarifa_infantil" name="tarifa_infantil" 
                                       value="<?php echo $config['tarifas']['infantil']; ?>" min="0" step="5" required>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Notificaciones -->
                <section class="config-section">
                    <div class="section-header">
                        <h2><i class="fas fa-bell"></i> Notificaciones</h2>
                        <p>Configura cómo quieres recibir avisos</p>
                    </div>
                    
                    <div class="form-switches">
                        <div class="switch-group">
                            <label class="switch">
                                <input type="checkbox" name="notif_email" <?php echo $config['notificaciones_email'] ? 'checked' : ''; ?>>
                                <span class="slider"></span>
                            </label>
                            <div class="switch-info">
                                <h4>Notificaciones por email</h4>
                                <p>Recibe emails sobre nuevas citas y cambios</p>
                            </div>
                        </div>

                        <div class="switch-group">
                            <label class="switch">
                                <input type="checkbox" name="notif_citas" <?php echo $config['notificaciones_citas'] ? 'checked' : ''; ?>>
                                <span class="slider"></span>
                            </label>
                            <div class="switch-info">
                                <h4>Recordatorios de citas</h4>
                                <p>Recibe recordatorios antes de cada cita</p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Seguridad -->
                <section class="config-section">
                    <div class="section-header">
                        <h2><i class="fas fa-lock"></i> Seguridad</h2>
                        <p>Cambia tu contraseña</p>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="current_password">Contraseña actual</label>
                            <div class="input-icon">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="current_password" name="current_password" placeholder="Dejar en blanco para no cambiar">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="new_password">Nueva contraseña</label>
                            <div class="input-icon">
                                <i class="fas fa-key"></i>
                                <input type="password" id="new_password" name="new_password" placeholder="Mínimo 8 caracteres">
                            </div>
                        </div>

                        <div class="form-group full-width">
                            <label for="confirm_password">Confirmar contraseña</label>
                            <div class="input-icon">
                                <i class="fas fa-key"></i>
                                <input type="password" id="confirm_password" name="confirm_password" placeholder="Repite la nueva contraseña">
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Botones de Acción -->
                <div class="form-actions">
                    <button type="button" class="btn btn-cancel" onclick="window.location.href='dashboard.php'">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-save">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                </div>

            </form>
        </div>
    </div>

    <script src="js/dashboard.js"></script>
    <script src="js/configuracion.js"></script>
</body>
</html>
