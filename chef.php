<?php
session_start();

// Validar que el usuario haya iniciado sesión y sea un Chef
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['cargo']) !== 'chef') {
    // Si no es chef o no está logueado, redirigir al login
    header('Location: login.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard del Chef - Milano</title>
    <link rel="icon" type="image/png" href="logo%20milano.png">

    <link rel="stylesheet" href="style.css">
    <style>
        /* Estilos específicos para el dashboard */
        body {
            align-items: flex-start;
            padding: 20px;
        }

        .dashboard-container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 20px;
            z-index: 10;
        }

        .header-bar {
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 20px;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 10px 30px -10px rgba(0, 40, 60, 0.2);
        }

        .header-info h1 {
            color: var(--ocean);
            font-size: 1.8rem;
            margin-bottom: 5px;
        }

        .header-info p {
            color: var(--text-color);
            opacity: 0.8;
        }

        .logout-btn {
            background: #ff6b6b;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 12px;
            font-family: 'PlayfairDisplay', serif;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background: #ff5252;
            transform: translateY(-2px);
        }

        .orders-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 10px;
        }

        .order-card {
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 15px 35px -15px rgba(0, 40, 60, 0.15);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .order-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: var(--primary);
        }

        .order-card.preparando::before {
            background: #E2A74F;
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .table-number {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--ocean);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .order-time {
            font-size: 0.85rem;
            color: #666;
            background: var(--input-bg);
            padding: 4px 10px;
            border-radius: 20px;
        }

        .order-items {
            list-style: none;
            padding: 0;
            margin: 0 0 20px 0;
        }

        .order-items li {
            padding: 8px 0;
            display: flex;
            justify-content: space-between;
            font-size: 0.95rem;
            color: var(--text-color);
            border-bottom: 1px dashed rgba(0,0,0,0.1);
        }

        .order-items li:last-child {
            border-bottom: none;
        }

        .item-qty {
            font-weight: 700;
            color: var(--primary);
            margin-right: 10px;
        }

        .order-actions {
            display: flex;
            gap: 10px;
        }

        .btn-ready {
            flex: 1;
            background: var(--primary);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 12px;
            font-weight: 600;
            font-family: 'PlayfairDisplay', serif;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-ready:hover {
            background: #3ba694;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(73, 190, 170, 0.3);
        }

        .empty-state {
            grid-column: 1 / -1;
            text-align: center;
            padding: 50px 20px;
            background: var(--card-bg);
            border-radius: 20px;
            color: var(--ocean);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.5;
        }
    </style>
</head>
<body>
    <div class="waves">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
            <path fill="#49beaa" fill-opacity="0.3" d="M0,160L48,154.7C96,149,192,139,288,149.3C384,160,480,192,576,202.7C672,213,768,203,864,176C960,149,1056,107,1152,96C1248,85,1344,107,1392,117.3L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
        </svg>
    </div>

    <div class="dashboard-container">
        <div class="header-bar">
            <div class="header-info">
                <h1>Cocina - Restaurante Milano</h1>
                <p>Bienvenido, Chef <?php echo htmlspecialchars($_SESSION['nombre']); ?></p>
            </div>
            <button class="logout-btn" onclick="logout()">Cerrar Sesión</button>
        </div>

        <div class="orders-grid" id="orders-container">
            <!-- Los pedidos se cargarán aquí por JS -->
            <div class="empty-state">
                <i>🍽️</i>
                <h2>No hay pedidos pendientes</h2>
                <p>La cocina está tranquila por ahora.</p>
            </div>
        </div>
    </div>

    <script>
        // Función para cargar pedidos desde el backend
        async function fetchOrders() {
            try {
                const response = await fetch('api/get_pedidos.php');
                const data = await response.json();

                const container = document.getElementById('orders-container');
                
                if (data.success && data.pedidos.length > 0) {
                    container.innerHTML = ''; // Limpiar contenedor
                    
                    data.pedidos.forEach(pedido => {
                        // Construir lista de platillos
                        let itemsHtml = '';
                        pedido.detalles.forEach(item => {
                            itemsHtml += `
                                <li>
                                    <span><span class="item-qty">${item.cantidad}x</span> ${item.nom_plat}</span>
                                </li>
                            `;
                        });

                        let badgeHtml = '';
                        let buttonHtml = '';
                        let borderColor = '';

                        if (pedido.estado === 'En espera') {
                            badgeHtml = '<span style="background:#e74c3c; color:white; padding:4px 10px; border-radius:12px; font-size:0.8rem; font-weight:bold;">En espera</span>';
                            buttonHtml = `<button class="btn-ready" style="background:#e67e22;" onclick="updateOrderStatus(${pedido.id_pedido}, 'Recibido')">Recibir Pedido</button>`;
                            borderColor = '#e74c3c';
                        } else if (pedido.estado === 'Recibido') {
                            badgeHtml = '<span style="background:#e67e22; color:white; padding:4px 10px; border-radius:12px; font-size:0.8rem; font-weight:bold;">Preparando</span>';
                            buttonHtml = `<button class="btn-ready" onclick="updateOrderStatus(${pedido.id_pedido}, 'Listo')">Marcar como Listo</button>`;
                            borderColor = '#e67e22';
                        } else if (pedido.estado === 'Listo') {
                            badgeHtml = '<span style="background:#27ae60; color:white; padding:4px 10px; border-radius:12px; font-size:0.8rem; font-weight:bold;">Listo (Esperando Mesero)</span>';
                            buttonHtml = `<button class="btn-ready" style="background:#bdc3c7; color:#333; cursor:not-allowed;" disabled>Listo para entrega</button>`;
                            borderColor = '#27ae60';
                        }

                        // Construir tarjeta
                        const card = document.createElement('div');
                        card.className = 'order-card';
                        card.style.borderLeft = `5px solid ${borderColor}`;
                        
                        // Formatear hora (asumiendo formato DATETIME de MySQL)
                        const hora = new Date(pedido.fecha_hora).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});

                        const meseroNombre = pedido.nom_mesero ? `${pedido.nom_mesero} ${pedido.ap_mesero}` : 'Sin asignar';

                        card.innerHTML = `
                            <div class="order-header">
                                <div>
                                    <div class="table-number">🍽️ Mesa ${pedido.num_mesa}</div>
                                    <div style="font-size:0.85rem; color:#666; margin-top:5px;">
                                        🤵 Mesero: <strong>${meseroNombre}</strong>
                                    </div>
                                </div>
                                <div style="display:flex; flex-direction:column; align-items:flex-end; gap:5px;">
                                    <div class="order-time">⏰ ${hora}</div>
                                    ${badgeHtml}
                                </div>
                            </div>
                            <ul class="order-items">
                                ${itemsHtml}
                            </ul>
                            <div class="order-actions">
                                ${buttonHtml}
                            </div>
                        `;
                        container.appendChild(card);
                    });
                } else {
                    container.innerHTML = `
                        <div class="empty-state">
                            <i>🍽️</i>
                            <h2>No hay pedidos pendientes</h2>
                            <p>La cocina está tranquila por ahora.</p>
                        </div>
                    `;
                }
            } catch (error) {
                console.error("Error al obtener pedidos:", error);
            }
        }

        // Actualizar estado del pedido
        async function updateOrderStatus(id_pedido, nuevo_estado) {
            try {
                const formData = new FormData();
                formData.append('id_pedido', id_pedido);
                formData.append('estado', nuevo_estado);

                const response = await fetch('api/update_pedido.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                if(data.success) {
                    // Refrescar lista de pedidos
                    fetchOrders();
                } else {
                    alert("Hubo un error: " + data.message);
                }
            } catch (error) {
                console.error("Error al actualizar pedido:", error);
            }
        }

        // Cerrar sesión
        async function logout() {
            window.location.href = 'logout.php';
        }

        // Cargar pedidos al iniciar y luego cada 10 segundos
        fetchOrders();
        setInterval(fetchOrders, 10000);
    </script>
</body>
</html>
