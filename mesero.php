<?php
session_start();

// Validar que el usuario haya iniciado sesión y sea un Mesero
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['cargo']) !== 'mesero') {
    header('Location: login.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard del Mesero - Milano</title>
    <link rel="icon" type="image/png" href="logo%20milano.png">

    <link rel="stylesheet" href="style.css">
    <style>
        body {
            align-items: flex-start;
            padding: 20px;
            flex-direction: column;
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
            color: var(--text-muted);
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

        /* ===========================
           TABS DE NAVEGACIÓN
           =========================== */
        .tabs-container {
            display: flex;
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 16px;
            padding: 5px;
            box-shadow: 0 10px 30px -10px rgba(0, 40, 60, 0.2);
        }

        .tab-btn {
            flex: 1;
            background: transparent;
            border: none;
            color: var(--text-muted);
            padding: 12px 20px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'PlayfairDisplay', serif;
        }

        .tab-btn.active {
            background: var(--primary);
            color: white;
            box-shadow: 0 4px 6px -1px rgba(73, 190, 170, 0.4);
        }

        .tab-btn .tab-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,0.3);
            color: white;
            font-size: 0.75rem;
            font-weight: 700;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            margin-left: 8px;
        }

        .tab-btn:not(.active) .tab-badge {
            background: var(--primary);
            color: white;
        }

        /* ===========================
           CONTENIDO DE TABS
           =========================== */
        .tab-content {
            display: none;
            animation: fadeIn 0.4s ease forwards;
        }

        .tab-content.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* ===========================
           GRID DE ORDENES
           =========================== */
        .orders-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
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
        }

        .order-card.listo::before {
            background: #4CAF50;
        }

        .order-card.entregado::before {
            background: var(--sand);
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

        .client-name {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin-bottom: 12px;
            font-weight: 500;
        }

        .order-items {
            list-style: none;
            padding: 0;
            margin: 0 0 15px 0;
        }

        .order-items li {
            padding: 8px 0;
            display: flex;
            justify-content: space-between;
            font-size: 0.95rem;
            color: var(--text-main);
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

        .item-price {
            font-weight: 600;
            color: var(--ocean);
        }

        .order-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-top: 2px solid var(--border-color);
            margin-bottom: 15px;
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--ocean);
        }

        /* ===========================
           BOTONES DE ACCIÓN
           =========================== */
        .order-actions {
            display: flex;
            gap: 10px;
        }

        .btn-deliver {
            flex: 1;
            background: #4CAF50;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 12px;
            font-weight: 600;
            font-family: 'PlayfairDisplay', serif;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }

        .btn-deliver:hover {
            background: #43A047;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.3);
        }

        .btn-factura {
            flex: 1;
            background: var(--sand);
            color: #5A3A0A;
            border: none;
            padding: 12px;
            border-radius: 12px;
            font-weight: 600;
            font-family: 'PlayfairDisplay', serif;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }

        .btn-factura:hover {
            background: #E2A74F;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(247, 201, 132, 0.4);
        }

        .btn-factura:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        /* ===========================
           ESTADO VACÍO
           =========================== */
        .empty-state {
            grid-column: 1 / -1;
            text-align: center;
            padding: 50px 20px;
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            color: var(--ocean);
        }

        .empty-state .empty-icon {
            font-size: 3.5rem;
            margin-bottom: 15px;
            display: block;
        }

        .empty-state h2 {
            margin-bottom: 8px;
        }

        /* ===========================
           STATUS BADGE
           =========================== */
        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-badge.listo {
            background: rgba(76, 175, 80, 0.15);
            color: #2E7D32;
        }

        .status-badge.entregado {
            background: rgba(247, 201, 132, 0.3);
            color: #8D6E2D;
        }

        /* ===========================
           RESPONSIVE
           =========================== */
        @media (max-width: 600px) {
            .dashboard-container {
                gap: 12px;
            }

            .header-bar {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }

            .header-info h1 {
                font-size: 1.4rem;
            }

            .orders-grid {
                grid-template-columns: 1fr;
            }

            .tab-btn {
                font-size: 0.85rem;
                padding: 10px 12px;
            }
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
        <!-- Barra superior -->
        <div class="header-bar">
            <div class="header-info">
                <h1>🍽️ Servicio - Restaurante Milano</h1>
                <p>Bienvenido, Mesero <?php echo htmlspecialchars($_SESSION['nombre']); ?></p>
            </div>
            <button class="logout-btn" onclick="logout()">Cerrar Sesión</button>
        </div>

        <!-- Pestañas -->
        <div class="tabs-container" style="flex-wrap: wrap;">
            <button class="tab-btn active" onclick="switchTab('nuevo')" id="tab-nuevo">
                📝 Tomar Pedido
            </button>
            <button class="tab-btn" onclick="switchTab('reservas')" id="tab-reservas">
                📅 Reservas
            </button>
            <button class="tab-btn" onclick="switchTab('cocina')" id="tab-cocina">
                🔥 En Cocina <span class="tab-badge" id="badge-cocina" style="background:#e67e22; color:white;">0</span>
            </button>
            <button class="tab-btn" onclick="switchTab('listos')" id="tab-listos">
                🟢 Pedidos Listos <span class="tab-badge" id="badge-listos">0</span>
            </button>
            <button class="tab-btn" onclick="switchTab('entregados')" id="tab-entregados">
                📋 Entregados <span class="tab-badge" id="badge-entregados">0</span>
            </button>
            <button class="tab-btn" onclick="switchTab('facturas')" id="tab-facturas">
                🧾 Facturas Despachadas
            </button>
        </div>

        <!-- Tab 0: En Cocina -->
        <div class="tab-content" id="content-cocina">
            <div class="orders-grid" id="grid-cocina">
                <p style="text-align:center; width:100%;">Cargando pedidos en cocina...</p>
            </div>
        </div>

        <!-- Tab: Reservas -->
        <div class="tab-content" id="content-reservas">
            <h2 style="color:var(--ocean); margin-bottom:20px; font-family:'PlayfairDisplay', serif;">📅 Próximas Reservas</h2>
            <div class="orders-grid" id="grid-reservas">
                <p style="text-align:center; width:100%;">Cargando reservas...</p>
            </div>
        </div>

        <!-- Tab 1: Tomar Pedido -->
        <div class="tab-content active" id="content-nuevo">
            <div class="order-card" style="max-width: 800px; margin: 0 auto; overflow: visible;">
                <h2 style="color:var(--ocean); margin-bottom:20px; font-family:'PlayfairDisplay', serif;">📝 Nuevo Pedido</h2>
                <form id="form-nuevo-pedido" onsubmit="enviarPedido(event)">
                    <div style="margin-bottom: 20px;">
                        <label style="font-weight:600; color:var(--ocean); display:block; margin-bottom:8px;">Número de Mesa:</label>
                        <input type="number" id="num_mesa" min="1" required style="width:100%; padding:10px; border-radius:10px; border:1px solid var(--border-color); font-family:'PlayfairDisplay', serif; font-size:1rem;">
                    </div>
                    
                    <div style="margin-bottom: 20px; background:rgba(73,190,170,0.1); padding:15px; border-radius:10px; border:1px solid rgba(73,190,170,0.3);">
                        <label style="font-weight:600; color:var(--ocean); display:block; margin-bottom:8px;">Vincular a Cliente (Opcional):</label>
                        <div style="display:flex; gap:10px; margin-bottom:10px;">
                            <input type="text" id="cliente_cedula" placeholder="Cédula" style="flex:1; padding:10px; border-radius:8px; border:1px solid var(--border-color); font-family:'PlayfairDisplay', serif;">
                            <button type="button" onclick="buscarCliente()" style="background:var(--ocean); color:white; border:none; padding:10px 15px; border-radius:8px; cursor:pointer; font-family:'PlayfairDisplay', serif;">Verificar</button>
                            <button type="button" onclick="abrirModalRegistroFlash()" style="background:#e67e22; color:white; border:none; padding:10px 15px; border-radius:8px; cursor:pointer; font-family:'PlayfairDisplay', serif; font-weight:bold;">➕ Nuevo</button>
                        </div>
                        <p id="cliente-encontrado-msg" style="margin:5px 0 0 0; font-size:0.9rem; color:#27ae60; font-weight:bold; display:none;">✅ Cliente reconocido: <span id="cliente-nombre-display"></span></p>
                        <p id="cliente-no-encontrado-msg" style="margin:5px 0 0 0; font-size:0.9rem; color:#e74c3c; font-weight:bold; display:none;">❌ Cliente no encontrado. Puede registrarlo presionando 'Nuevo'.</p>
                    </div>
                    
                    <div style="margin-bottom: 20px;">
                        <label style="font-weight:600; color:var(--ocean); display:block; margin-bottom:8px;">Menú (Seleccione platillos):</label>
                        <div id="menu-grid-mesero" style="display:flex; flex-direction:column; gap:15px;">
                            <p>Cargando menú...</p>
                        </div>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label style="font-weight:600; color:var(--ocean); display:block; margin-bottom:8px;">Resumen del Pedido:</label>
                        <ul id="lista-pedido" style="list-style:none; padding:0; margin:0; border:1px solid var(--border-color); border-radius:10px; padding:10px; min-height:100px; background:rgba(255,255,255,0.5);">
                            <li style="color:#666; font-style:italic;" id="empty-pedido-msg">No hay platillos añadidos.</li>
                        </ul>
                        <div style="margin-top:10px; text-align:right; font-size:1.1rem; font-weight:bold; color:var(--ocean);">
                            Subtotal: $<span id="lbl-subtotal">0.00</span><br>
                            IVA (16%): $<span id="lbl-iva">0.00</span><br>
                            Total a Pagar: $<span id="lbl-total">0.00</span>
                        </div>
                    </div>

                    <div style="display:flex; gap:10px; margin-top:20px;">
                        <button type="submit" style="flex:1; background:linear-gradient(135deg, #1a5c5e, #2a7c7e); color:white; border:none; padding:15px; border-radius:12px; font-weight:600; font-size:1.1rem; font-family:'PlayfairDisplay', serif; cursor:pointer;">🍳 Enviar a Cocina</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tab 1: Pedidos Listos (desde cocina) -->
        <div class="tab-content" id="content-listos">
            <div class="orders-grid" id="grid-listos">
                <div class="empty-state">
                    <span class="empty-icon">👨‍🍳</span>
                    <h2>Esperando al Chef...</h2>
                    <p>Los pedidos listos para servir aparecerán aquí.</p>
                </div>
            </div>
        </div>

        <!-- Tab 2: Pedidos Entregados (pendientes de factura) -->
        <div class="tab-content" id="content-entregados">
            <div class="orders-grid" id="grid-entregados">
                <div class="empty-state">
                    <span class="empty-icon">📋</span>
                    <h2>Sin entregas recientes</h2>
                    <p>Los pedidos entregados aparecerán aquí para generar la factura.</p>
                </div>
            </div>
        </div>

        <!-- Tab 3: Facturas Despachadas -->
        <div class="tab-content" id="content-facturas">
            <div class="orders-grid" id="grid-facturas">
                <div class="empty-state">
                    <span class="empty-icon">🧾</span>
                    <h2>Sin facturas aún</h2>
                    <p>Las facturas que generes aparecerán aquí.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL VERIFICACIÓN DE FACTURA -->
    <div id="modal-factura" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:1000; align-items:center; justify-content:center; overflow-y:auto; padding:20px;">
        <div style="background:#fff; padding:0; border-radius:16px; width:90%; max-width:480px; box-shadow:0 25px 60px rgba(0,0,0,0.3); margin:auto;">
            <!-- Encabezado -->
            <div style="background:linear-gradient(135deg, #1a5c5e, #2a7c7e); color:white; padding:25px 30px; border-radius:16px 16px 0 0; text-align:center;">
                <img src="imagenes/logo%20milano.png" alt="Logo Milano" style="width:80px; margin-bottom:10px; filter:brightness(1.2) drop-shadow(0 2px 4px rgba(0,0,0,0.3));">
                <h2 style="margin:0 0 5px 0; font-family:'PlayfairDisplay', serif; font-size:1.6rem;">Restaurante Milano</h2>
                <p style="margin:0; opacity:0.8; font-size:0.9rem;">Verificación de Factura</p>
            </div>
            <!-- Cuerpo -->
            <div style="padding:25px 30px;">
                <div style="display:flex; justify-content:space-between; margin-bottom:15px; font-size:0.9rem; color:#666;">
                    <span>Mesa: <strong id="modal-mesa" style="color:#333;"></strong></span>
                    <span id="modal-fecha" style="color:#333;"></span>
                </div>
                <hr style="border:none; border-top:1px dashed #ccc; margin-bottom:15px;">

                <!-- Tabla de productos -->
                <table style="width:100%; border-collapse:collapse; margin-bottom:15px;">
                    <thead>
                        <tr style="border-bottom:2px solid #1a5c5e; font-size:0.85rem; color:#1a5c5e;">
                            <th style="text-align:left; padding:6px 0;">Cant.</th>
                            <th style="text-align:left; padding:6px 0;">Descripción</th>
                            <th style="text-align:right; padding:6px 0;">P/U</th>
                            <th style="text-align:right; padding:6px 0;">Total</th>
                        </tr>
                    </thead>
                    <tbody id="modal-items-body"></tbody>
                </table>

                <hr style="border:none; border-top:1px dashed #ccc; margin-bottom:10px;">

                <!-- Totales -->
                <div style="font-size:0.95rem; color:#333;">
                    <div style="display:flex; justify-content:space-between; padding:4px 0;"><span>Subtotal:</span> <span>$<span id="modal-subtotal">0.00</span></span></div>
                    <div style="display:flex; justify-content:space-between; padding:4px 0;"><span>IVA (16%):</span> <span>$<span id="modal-iva">0.00</span></span></div>
                    <div style="display:flex; justify-content:space-between; padding:8px 0; font-size:1.2rem; font-weight:bold; color:#1a5c5e; border-top:2px solid #1a5c5e; margin-top:5px;"><span>TOTAL:</span> <span>$<span id="modal-total">0.00</span></span></div>
                </div>

                <!-- Método de Pago -->
                <div style="margin-top:20px;">
                    <label style="font-weight:600; color:#1a5c5e; display:block; margin-bottom:8px;">Método de Pago:</label>
                    <select id="select-metodo-pago" style="width:100%; padding:12px; border-radius:10px; border:1px solid #ccc; font-family:'PlayfairDisplay', serif; font-size:1rem;">
                        <option value="Efectivo">💵 Efectivo</option>
                        <option value="Tarjeta">💳 Tarjeta de Débito/Crédito</option>
                        <option value="Pago Movil">📱 Pago Móvil / Transferencia</option>
                    </select>
                </div>
            </div>
            <!-- Botones -->
            <div style="padding:0 30px 25px 30px; display:flex; gap:10px;">
                <button type="button" onclick="cerrarModalFactura()" style="flex:1; background:#e0e0e0; color:#555; border:none; padding:14px; border-radius:12px; font-weight:600; cursor:pointer; font-family:'PlayfairDisplay', serif; font-size:1rem;">Cancelar</button>
                <button type="button" onclick="confirmarYFacturar()" style="flex:1; background:linear-gradient(135deg, #1a5c5e, #2a7c7e); color:white; border:none; padding:14px; border-radius:12px; font-weight:600; cursor:pointer; font-family:'PlayfairDisplay', serif; font-size:1rem;">Confirmar y Facturar</button>
            </div>
        </div>
    </div>

    <!-- MODAL FACTURA GENERADA (vista elegante) -->
    <div id="modal-factura-generada" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:1100; align-items:center; justify-content:center; overflow-y:auto; padding:20px;">
        <div style="background:#fff; padding:0; border-radius:16px; width:90%; max-width:480px; box-shadow:0 25px 60px rgba(0,0,0,0.3); margin:auto;">
            <!-- Encabezado verde -->
            <div style="background:linear-gradient(135deg, #27ae60, #2ecc71); color:white; padding:25px 30px; border-radius:16px 16px 0 0; text-align:center;">
                <img src="imagenes/logo%20milano.png" alt="Logo Milano" style="width:80px; margin-bottom:10px; filter:brightness(1.2) drop-shadow(0 2px 4px rgba(0,0,0,0.3));">
                <h2 style="margin:0 0 5px 0; font-family:'PlayfairDisplay', serif; font-size:1.5rem;">Factura Generada ✅</h2>
                <p style="margin:0; opacity:0.9; font-size:0.9rem;">Pedido enviado a cocina</p>
            </div>
            <!-- Cuerpo recibo -->
            <div id="factura-generada-body" style="padding:25px 30px;"></div>
            <!-- Botón -->
            <div style="padding:0 30px 25px 30px;">
                <button type="button" onclick="cerrarFacturaGenerada()" style="width:100%; background:linear-gradient(135deg, #1a5c5e, #2a7c7e); color:white; border:none; padding:14px; border-radius:12px; font-weight:600; cursor:pointer; font-family:'PlayfairDisplay', serif; font-size:1rem;">Cerrar y Nuevo Pedido</button>
            </div>
        </div>
    </div>

    <script>
        // ===========================
        // LÓGICA DE TOMA DE PEDIDOS
        // ===========================
        let menuPlatillos = [];
        let pedidoActual = [];
        let subtotalActual = 0;
        let ivaActual = 0;
        let totalActual = 0;

        async function cargarMenu() {
            try {
                const res = await fetch('api/get_platillos.php');
                const data = await res.json();
                const container = document.getElementById('menu-grid-mesero');
                container.innerHTML = '';
                
                if (data.success && data.platillos.length > 0) {
                    menuPlatillos = data.platillos;
                    
                    const categorias = {};
                    menuPlatillos.forEach(plat => {
                        if (!categorias[plat.tipo_plat]) categorias[plat.tipo_plat] = [];
                        categorias[plat.tipo_plat].push(plat);
                    });

                    for (const cat in categorias) {
                        let html = `
                        <div style="background:rgba(255,255,255,0.5); padding:15px; border-radius:12px; border:1px solid var(--border-color);">
                            <h3 style="color:var(--ocean); margin-bottom:10px; font-family:'PlayfairDisplay', serif;">${cat.toUpperCase()}</h3>
                            <div style="display:flex; flex-direction:column; gap:10px;">
                        `;
                        categorias[cat].forEach(p => {
                            const maxDisp = parseInt(p.max_disponible);
                            const isAgotado = maxDisp <= 0;
                            const btnHtml = isAgotado 
                                ? `<button type="button" style="background:#ccc; color:white; border:none; padding:5px 10px; border-radius:5px; cursor:not-allowed;" disabled>Agotado</button>`
                                : `<button type="button" onclick="agregarPlatillo('${p.cod_plat}', ${maxDisp})" style="background:var(--primary); color:white; border:none; padding:5px 10px; border-radius:5px; cursor:pointer;">Añadir</button>`;
                                
                            html += `
                                <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px dashed #ccc; padding-bottom:5px;">
                                    <div style="flex:2;">
                                        <strong>${p.nom_plat}</strong> <br>
                                        <small style="color:var(--primary); font-weight:bold;">$${p.precio_plat}</small>
                                    </div>
                                    <div style="flex:3; display:flex; gap:5px; align-items:center;">
                                        <input type="number" id="cant-${p.cod_plat}" value="1" min="1" max="${maxDisp}" style="width:50px; padding:5px; border-radius:5px; border:1px solid #ccc;" ${isAgotado ? 'disabled' : ''}>
                                        <input type="text" id="notas-${p.cod_plat}" placeholder="Notas..." style="flex:1; padding:5px; border-radius:5px; border:1px solid #ccc;" ${isAgotado ? 'disabled' : ''}>
                                        ${btnHtml}
                                    </div>
                                </div>
                            `;
                        });
                        html += `</div></div>`;
                        container.innerHTML += html;
                    }
                }
            } catch (e) {
                console.error(e);
            }
        }

        function agregarPlatillo(cod_plat, maxDisp) {
            const cantidad = parseInt(document.getElementById(`cant-${cod_plat}`).value);
            const notas = document.getElementById(`notas-${cod_plat}`).value.trim();

            if (cantidad < 1) return alert("Cantidad inválida.");
            if (maxDisp <= 0) return alert("Este platillo está agotado.");

            // Check how many we already have in the cart
            const existingItem = pedidoActual.find(p => p.cod_plat === cod_plat);
            const currentQty = existingItem ? existingItem.cantidad : 0;

            if ((currentQty + cantidad) > maxDisp) {
                return alert(`No puedes añadir más de ${maxDisp} unidades en total de este platillo. Tienes ${currentQty} en el pedido y estás intentando añadir ${cantidad} más.`);
            }

            if (existingItem) {
                existingItem.cantidad += cantidad;
                if(notas) {
                    existingItem.notas = existingItem.notas ? existingItem.notas + " | " + notas : notas;
                }
            } else {
                const platInfo = menuPlatillos.find(p => p.cod_plat === cod_plat);
                pedidoActual.push({
                    cod_plat: cod_plat,
                    nom_plat: platInfo.nom_plat,
                    precio_plat: parseFloat(platInfo.precio_plat),
                    cantidad: cantidad,
                    notas: notas
                });
            }

            renderListaPedido();
            
            // Reset fields
            document.getElementById(`cant-${cod_plat}`).value = 1;
            document.getElementById(`notas-${cod_plat}`).value = "";
        }

        function renderListaPedido() {
            const lista = document.getElementById('lista-pedido');
            subtotalActual = 0;

            if (pedidoActual.length === 0) {
                lista.innerHTML = '<li style="color:#666; font-style:italic;" id="empty-pedido-msg">No hay platillos añadidos.</li>';
                document.getElementById('lbl-subtotal').textContent = '0.00';
                document.getElementById('lbl-iva').textContent = '0.00';
                document.getElementById('lbl-total').textContent = '0.00';
                return;
            }

            lista.innerHTML = '';
            pedidoActual.forEach((item, index) => {
                const subItem = item.cantidad * item.precio_plat;
                subtotalActual += subItem;
                lista.innerHTML += `
                    <li style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px dashed var(--border-color); padding:8px 0;">
                        <div>
                            <strong>${item.cantidad}x</strong> ${item.nom_plat} ($${item.precio_plat})
                            ${item.notas ? `<br><small style="color:#e67e22;">Nota: ${item.notas}</small>` : ''}
                        </div>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <strong>$${subItem.toFixed(2)}</strong>
                            <button type="button" onclick="eliminarPlatillo(${index})" style="background:#ff6b6b; color:white; border:none; padding:5px 10px; border-radius:5px; cursor:pointer;">X</button>
                        </div>
                    </li>
                `;
            });

            ivaActual = subtotalActual * 0.16;
            totalActual = subtotalActual + ivaActual;

            document.getElementById('lbl-subtotal').textContent = subtotalActual.toFixed(2);
            document.getElementById('lbl-iva').textContent = ivaActual.toFixed(2);
            document.getElementById('lbl-total').textContent = totalActual.toFixed(2);
        }

        function eliminarPlatillo(index) {
            pedidoActual.splice(index, 1);
            renderListaPedido();
        }

        async function buscarCliente() {
            const cedula = document.getElementById('cliente_cedula').value.trim();
            if(!cedula) return alert("Ingrese una cédula para buscar.");
            try {
                // Consultaremos un endpoint para buscar al cliente
                const response = await fetch(`api/buscar_cliente.php?cedula=${cedula}`);
                const data = await response.json();
                
                if (data.success && data.cliente) {
                    document.getElementById('cliente-encontrado-msg').style.display = 'block';
                    document.getElementById('cliente-nombre-display').textContent = data.cliente.nom_cli + (data.cliente.ap_cli ? ' ' + data.cliente.ap_cli : '');
                    document.getElementById('cliente-no-encontrado-msg').style.display = 'none';
                } else {
                    document.getElementById('cliente-encontrado-msg').style.display = 'none';
                    document.getElementById('cliente-no-encontrado-msg').style.display = 'block';
                }
            } catch (error) {
                console.error("Error al buscar cliente:", error);
            }
        }

        async function enviarPedido(e) {
            e.preventDefault();
            const num_mesa = document.getElementById('num_mesa').value;
            const cedula_cli = document.getElementById('cliente_cedula').value.trim();

            if (pedidoActual.length === 0) {
                return alert("Debe añadir al menos un platillo al pedido.");
            }

            try {
                const response = await fetch('api/crear_pedido.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        num_mesa: num_mesa, 
                        platillos: pedidoActual,
                        cedula_cli: cedula_cli
                    })
                });
                const data = await response.json();
                if (data.success) {
                    alert(`✅ Pedido #${data.id_pedido} enviado a cocina.\n⏰ Tiempo estimado: ${data.minutos_estimados} min.\n${data.cedula_cli ? 'Asociado al cliente: ' + data.cedula_cli : 'No se asoció cliente.'}`);
                    pedidoActual = [];
                    renderListaPedido();
                    document.getElementById('form-nuevo-pedido').reset();
                    document.getElementById('cliente-encontrado-msg').style.display = 'none';
                    document.getElementById('cliente-no-encontrado-msg').style.display = 'none';
                } else {
                    if (data.error && data.error.includes("Sin stock suficiente")) {
                        mostrarModalErrorInventario(data.error);
                    } else {
                        alert("Error: " + data.error);
                    }
                }
            } catch (error) {
                console.error("Error al enviar pedido:", error);
                alert("Error de conexión al enviar el pedido.");
            }
        }

        // --- FACTURACIÓN DESDE ENTREGADOS ---
        let facturaPedidoId = null;

        async function abrirModalFacturaEntregado(id_pedido) {
            facturaPedidoId = id_pedido;
            try {
                const res = await fetch(`api/get_pedido_detalle.php?id=${id_pedido}`);
                const data = await res.json();
                if (!data.success) {
                    alert('Error: ' + data.error);
                    return;
                }
                const p = data.pedido;

                // Llenar la tabla de productos en el modal
                const tbody = document.getElementById('modal-items-body');
                tbody.innerHTML = '';
                p.detalles.forEach(item => {
                    tbody.innerHTML += `
                        <tr style="border-bottom:1px solid #eee; font-size:0.9rem;">
                            <td style="padding:6px 0;">${item.cantidad}</td>
                            <td style="padding:6px 0;">${item.nom_plat}</td>
                            <td style="text-align:right; padding:6px 0;">$${item.precio_unitario}</td>
                            <td style="text-align:right; padding:6px 0; font-weight:600;">$${item.subtotal_linea}</td>
                        </tr>
                    `;
                });

                document.getElementById('modal-mesa').textContent = p.num_mesa;
                document.getElementById('modal-fecha').textContent = new Date().toLocaleDateString('es-VE');
                document.getElementById('modal-subtotal').textContent = p.subtotal;
                document.getElementById('modal-iva').textContent = p.iva;
                document.getElementById('modal-total').textContent = p.total;
                document.getElementById('modal-factura').style.display = 'flex';
            } catch (e) {
                console.error(e);
                alert('Error al cargar los datos del pedido.');
            }
        }

        function cerrarModalFactura() {
            document.getElementById('modal-factura').style.display = 'none';
            facturaPedidoId = null;
        }

        async function cargarReservas() {
            try {
                const res = await fetch('api/get_todas_reservas.php');
                const data = await res.json();
                const grid = document.getElementById('grid-reservas');
                grid.innerHTML = '';
                
                if (data.success) {
                    if (data.reservas.length === 0) {
                        grid.innerHTML = '<div class="empty-state"><h2>No hay reservas</h2><p>Actualmente no hay reservas programadas.</p></div>';
                        return;
                    }
                    
                    data.reservas.forEach(r => {
                        let estadoColor = r.estado_reserva === 'Activa' ? '#4CAF50' : (r.estado_reserva === 'Cancelada' ? '#f44336' : '#2196F3');
                        
                        grid.innerHTML += `
                            <div class="order-card" style="border-left: 5px solid ${estadoColor};">
                                <div class="order-header">
                                    <div class="table-number">🍽️ Mesa ${r.num_mesa}</div>
                                    <div class="status-badge" style="background-color: ${estadoColor}22; color: ${estadoColor};">${r.estado_reserva}</div>
                                </div>
                                <div class="client-name">👤 ${r.nom_cli} ${r.ap_cli}</div>
                                <div style="color:#555; margin-bottom:10px;">
                                    <strong>Zona:</strong> ${r.zona_mesa}<br>
                                    <strong>Fecha:</strong> ${r.fecha_reserva} <br>
                                    <strong>Hora:</strong> ${r.hora_reserva} <br>
                                    <strong>Personas:</strong> 👥 ${r.cant_personas}
                                </div>
                            </div>
                        `;
                    });
                }
            } catch (error) {
                console.error("Error al cargar reservas:", error);
            }
        }

        function toggleFacturaItem(chk) {
            document.getElementById('modal-factura-generada').style.display = 'none';
            fetchEntregados();
            fetchFacturasMesero();
        }

        function cerrarFacturaGenerada() {
            document.getElementById('modal-factura-generada').style.display = 'none';
            fetchEntregados();
            fetchFacturasMesero();
        }

        function mostrarFacturaElegante(factura) {
            const body = document.getElementById('factura-generada-body');
            let filas = '';
            factura.detalles.forEach(d => {
                filas += `
                    <tr style="border-bottom:1px solid #eee; font-size:0.9rem;">
                        <td style="padding:6px 0;">${d.cantidad}</td>
                        <td style="padding:6px 0;">${d.nom_plat}</td>
                        <td style="text-align:right; padding:6px 0;">$${d.precio_unitario}</td>
                        <td style="text-align:right; padding:6px 0; font-weight:600;">$${d.subtotal_linea}</td>
                    </tr>
                `;
            });

            body.innerHTML = `
                <div style="display:flex; justify-content:space-between; margin-bottom:5px; font-size:0.85rem; color:#888;">
                    <span>Fecha: ${factura.fecha}</span>
                    <span>Hora: ${factura.hora}</span>
                </div>
                <div style="display:flex; justify-content:space-between; margin-bottom:15px; font-size:0.85rem; color:#888;">
                    <span>Mesa: <strong style="color:#333;">${factura.mesa}</strong></span>
                    <span>Mesero: <strong style="color:#333;">${factura.mesero}</strong></span>
                </div>
                <div style="margin-bottom:15px; font-size:0.85rem; color:#888;">
                    Cliente: <strong style="color:#333;">${factura.cliente}</strong>
                </div>
                <hr style="border:none; border-top:1px dashed #ccc; margin-bottom:15px;">
                <table style="width:100%; border-collapse:collapse; margin-bottom:15px;">
                    <thead>
                        <tr style="border-bottom:2px solid #27ae60; font-size:0.8rem; color:#27ae60; text-transform:uppercase;">
                            <th style="text-align:left; padding:6px 0;">Cant</th>
                            <th style="text-align:left; padding:6px 0;">Artículo</th>
                            <th style="text-align:right; padding:6px 0;">P/U</th>
                            <th style="text-align:right; padding:6px 0;">Total</th>
                        </tr>
                    </thead>
                    <tbody>${filas}</tbody>
                </table>
                <hr style="border:none; border-top:1px dashed #ccc; margin-bottom:15px;">
                <div style="font-size:0.95rem; color:#333;">
                    <div style="display:flex; justify-content:space-between; padding:3px 0;"><span>Subtotal:</span> <span>$${factura.subtotal}</span></div>
                    <div style="display:flex; justify-content:space-between; padding:3px 0;"><span>IVA (16%):</span> <span>$${factura.iva}</span></div>
                    <div style="display:flex; justify-content:space-between; padding:8px 0; font-size:1.3rem; font-weight:bold; color:#27ae60; border-top:2px solid #27ae60; margin-top:5px;"><span>TOTAL:</span> <span>$${factura.total}</span></div>
                </div>
                <div style="margin-top:15px; text-align:center; padding:10px; background:#f0faf0; border-radius:10px; font-size:0.9rem; color:#27ae60;">
                    <strong>💳 Pago: ${factura.metodo_pago}</strong>
                </div>
                <p style="text-align:center; margin-top:15px; font-size:0.8rem; color:#aaa;">Gracias por su preferencia • Restaurante Milano</p>
            `;
            document.getElementById('modal-factura-generada').style.display = 'flex';
        }

        async function confirmarYFacturar() {
            if (!facturaPedidoId) return alert('No se seleccionó un pedido.');
            const metodo_pago = document.getElementById('select-metodo-pago').value;

            try {
                const response = await fetch('api/generar_factura.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        id_pedido: facturaPedidoId,
                        metodo_pago: metodo_pago
                    })
                });
                const data = await response.json();
                if (data.success) {
                    cerrarModalFactura();
                    mostrarFacturaElegante(data.factura);
                } else {
                    alert("Error: " + data.error);
                }
            } catch (error) {
                console.error("Error al facturar:", error);
                alert("Error de conexión al generar la factura.");
            }
        }

        // ===========================
        // NAVEGACIÓN POR TABS
        // ===========================
        function switchTab(tab) {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            
            document.getElementById('tab-' + tab).classList.add('active');
            document.getElementById('content-' + tab).classList.add('active');
            
            if(tab === 'reservas') {
                cargarReservas();
            }
        }

        // ===========================
        // CARGAR PEDIDOS EN COCINA
        // ===========================
        async function fetchEnCocina() {
            try {
                // Podemos reutilizar api/get_pedidos.php ya que trae En espera y Recibido
                // Pero crearemos o modificaremos para filtrar solo los no listos
                const response = await fetch('api/get_pedidos.php');
                const data = await response.json();
                const container = document.getElementById('grid-cocina');
                const badge = document.getElementById('badge-cocina');

                if (data.success) {
                    // Filtrar solo los que están en cocina
                    const pedidosCocina = data.pedidos.filter(p => p.estado === 'En espera' || p.estado === 'Recibido');
                    badge.textContent = pedidosCocina.length;

                    if (pedidosCocina.length > 0) {
                        container.innerHTML = '';
                        pedidosCocina.forEach(pedido => {
                            let itemsHtml = '';
                            let totalPedido = 0;
                            pedido.detalles.forEach(item => {
                                const precio = parseFloat(item.precio_plat) * parseInt(item.cantidad);
                                totalPedido += precio;
                                itemsHtml += `
                                    <li>
                                        <span><span class="item-qty">${item.cantidad}x</span> ${item.nom_plat}</span>
                                        <span class="item-price">$${precio.toFixed(2)}</span>
                                    </li>
                                `;
                            });

                            const iva = totalPedido * 0.16;
                            const totalConIva = totalPedido + iva;
                            const hora = new Date(pedido.fecha_hora).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                            const clientName = pedido.nom_cli ? `${pedido.nom_cli} ${pedido.ap_cli}` : (pedido.cedula_cli ? `CI: ${pedido.cedula_cli}` : 'Cliente en local');

                            const card = document.createElement('div');
                            card.className = 'order-card';
                            card.style.borderLeft = '4px solid #e67e22';
                            card.innerHTML = `
                                <div class="order-header">
                                    <div class="table-number">🍽️ Mesa ${pedido.num_mesa}</div>
                                    <span class="status-badge" style="background:#fff3cd; color:#856404;">⏳ ${pedido.estado}</span>
                                </div>
                                <div class="client-name">👤 ${clientName} — ⏰ ${hora}</div>
                                <ul class="order-items">${itemsHtml}</ul>
                                <div style="border-top:2px dashed #ccc; padding-top:10px; margin-top:10px;">
                                    <div style="display:flex; justify-content:space-between; font-size:0.9rem; color:#555;">
                                        <span>Subtotal:</span><span>$${totalPedido.toFixed(2)}</span>
                                    </div>
                                    <div style="display:flex; justify-content:space-between; font-size:0.9rem; color:#555;">
                                        <span>IVA (16%):</span><span>$${iva.toFixed(2)}</span>
                                    </div>
                                    <div style="display:flex; justify-content:space-between; font-size:1.1rem; font-weight:bold; color:var(--ocean); margin-top:5px; padding-top:5px; border-top:2px solid var(--ocean);">
                                        <span>💰 Total a Cobrar:</span><span>$${totalConIva.toFixed(2)}</span>
                                    </div>
                                </div>
                                <div style="margin-top:12px; font-size:0.85rem; color:#e67e22; font-weight:600; text-align:center; background:rgba(230,126,34,0.1); padding:8px; border-radius:8px;">
                                    🔥 Preparándose en cocina...
                                </div>
                            `;
                            container.appendChild(card);
                        });
                    } else {
                        container.innerHTML = `
                            <div class="empty-state">
                                <span class="empty-icon">🔥</span>
                                <h2>Sin pedidos en cocina</h2>
                                <p>No hay órdenes en proceso en este momento.</p>
                            </div>
                        `;
                    }
                }
            } catch (error) {
                console.error("Error al obtener pedidos en cocina:", error);
            }
        }

        // ===========================
        // CARGAR PEDIDOS LISTOS
        // ===========================
        async function fetchListos() {
            try {
                const response = await fetch('api/get_pedidos_listos.php');
                const data = await response.json();
                const container = document.getElementById('grid-listos');
                const badge = document.getElementById('badge-listos');

                if (data.success && data.pedidos.length > 0) {
                    badge.textContent = data.pedidos.length;
                    container.innerHTML = '';

                    data.pedidos.forEach(pedido => {
                        let itemsHtml = '';
                        pedido.detalles.forEach(item => {
                            const precio = parseFloat(item.precio_plat) * parseInt(item.cantidad);
                            itemsHtml += `
                                <li>
                                    <span><span class="item-qty">${item.cantidad}x</span> ${item.nom_plat}</span>
                                    <span class="item-price">$${precio.toFixed(2)}</span>
                                </li>
                            `;
                        });

                        const hora = new Date(pedido.fecha_hora).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                        const clientName = pedido.nom_cli ? `${pedido.nom_cli} ${pedido.ap_cli}` : 'Cliente anónimo';

                        const card = document.createElement('div');
                        card.className = 'order-card listo';
                        card.innerHTML = `
                            <div class="order-header">
                                <div class="table-number">🍽️ Mesa ${pedido.num_mesa}</div>
                                <span class="status-badge listo">✅ Listo</span>
                            </div>
                            <div class="client-name">👤 ${clientName} — ⏰ ${hora}</div>
                            <ul class="order-items">${itemsHtml}</ul>
                            <div class="order-actions">
                                <button class="btn-deliver" onclick="entregarPedido(${pedido.id_pedido})">
                                    🚶 Entregar a Mesa
                                </button>
                            </div>
                        `;
                        container.appendChild(card);
                    });
                } else {
                    badge.textContent = '0';
                    container.innerHTML = `
                        <div class="empty-state">
                            <span class="empty-icon">👨‍🍳</span>
                            <h2>Esperando al Chef...</h2>
                            <p>Los pedidos listos para servir aparecerán aquí.</p>
                        </div>
                    `;
                }
            } catch (error) {
                console.error("Error al obtener pedidos listos:", error);
            }
        }

        // ===========================
        // CARGAR PEDIDOS ENTREGADOS
        // ===========================
        async function fetchEntregados() {
            try {
                const response = await fetch('api/get_entregados.php');
                const data = await response.json();
                const container = document.getElementById('grid-entregados');
                const badge = document.getElementById('badge-entregados');

                if (data.success && data.pedidos.length > 0) {
                    badge.textContent = data.pedidos.length;
                    container.innerHTML = '';

                    data.pedidos.forEach(pedido => {
                        let itemsHtml = '';
                        pedido.detalles.forEach(item => {
                            const precio = parseFloat(item.precio_plat) * parseInt(item.cantidad);
                            itemsHtml += `
                                <li>
                                    <span><span class="item-qty">${item.cantidad}x</span> ${item.nom_plat}</span>
                                    <span class="item-price">$${precio.toFixed(2)}</span>
                                </li>
                            `;
                        });

                        const hora = new Date(pedido.fecha_hora).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                        const clientName = pedido.nom_cli ? `${pedido.nom_cli} ${pedido.ap_cli}` : 'Cliente anónimo';

                        const card = document.createElement('div');
                        card.className = 'order-card entregado';
                        card.innerHTML = `
                            <div class="order-header">
                                <div class="table-number">🍽️ Mesa ${pedido.num_mesa}</div>
                                <span class="status-badge entregado">📦 Entregado</span>
                            </div>
                            <div class="client-name">👤 ${clientName} — ⏰ ${hora}</div>
                            <ul class="order-items">${itemsHtml}</ul>
                            <div class="order-total">
                                <span>Total:</span>
                                <span>$${pedido.subtotal}</span>
                            </div>
                            <div class="order-actions">
                                <button class="btn-factura" onclick="abrirModalFacturaEntregado(${pedido.id_pedido})">
                                    🧾 Generar Factura y Cobrar
                                </button>
                            </div>
                        `;
                        container.appendChild(card);
                    });
                } else {
                    badge.textContent = '0';
                    container.innerHTML = `
                        <div class="empty-state">
                            <span class="empty-icon">📋</span>
                            <h2>Sin entregas recientes</h2>
                            <p>Los pedidos entregados aparecerán aquí para generar la factura.</p>
                        </div>
                    `;
                }
            } catch (error) {
                console.error("Error al obtener entregados:", error);
            }
        }

        // ===========================
        // ENTREGAR PEDIDO
        // ===========================
        async function entregarPedido(id_pedido) {
            try {
                const formData = new FormData();
                formData.append('id_pedido', id_pedido);
                formData.append('estado', 'Entregado');

                const response = await fetch('api/update_pedido.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();
                if (data.success) {
                    // Refrescar ambas listas
                    fetchListos();
                    fetchEntregados();
                } else {
                    alert("Error: " + data.message);
                }
            } catch (error) {
                console.error("Error al entregar pedido:", error);
            }
        }

        // ===========================
        // CARGAR FACTURAS DESPACHADAS
        // ===========================
        async function fetchFacturasMesero() {
            try {
                const response = await fetch('api/get_facturas_mesero.php');
                const data = await response.json();
                const container = document.getElementById('grid-facturas');

                if (data.success && data.facturas.length > 0) {
                    container.innerHTML = '';
                    data.facturas.forEach(f => {
                        let itemsHtml = '';
                        if (f.detalles) {
                            f.detalles.forEach(d => {
                                itemsHtml += `<li><span><span class="item-qty">${d.cantidad}x</span> ${d.nom_plat}</span><span class="item-price">$${d.subtotal}</span></li>`;
                            });
                        }

                        const card = document.createElement('div');
                        card.className = 'order-card';
                        card.style.borderLeft = '4px solid #27ae60';
                        card.innerHTML = `
                            <div class="order-header">
                                <div class="table-number">🍽️ Mesa ${f.num_mesa || '-'}</div>
                                <span class="status-badge" style="background:#27ae60; color:white;">✅ Facturado</span>
                            </div>
                            <div class="client-name">👤 ${f.nombre_cliente || 'Cliente general'} — ${f.dia_fact}/${f.mes_fact}/${f.año_fact} ${f.hora_fact}</div>
                            <ul class="order-items">${itemsHtml}</ul>
                            <div class="order-total" style="display:flex; justify-content:space-between; font-size:0.95rem; padding-top:8px; border-top:1px dashed #ccc;">
                                <span>Sub: $${f.subtotal} | IVA: $${f.iva}</span>
                                <span style="color:#27ae60; font-weight:bold; font-size:1.1rem;">Total: $${f.total}</span>
                            </div>
                            <div style="margin-top:8px; text-align:center; padding:6px; background:#f0faf0; border-radius:8px; font-size:0.85rem; color:#27ae60; font-weight:600;">
                                💳 ${f.metodo_pago}
                            </div>
                        `;
                        container.appendChild(card);
                    });
                } else {
                    container.innerHTML = `
                        <div class="empty-state">
                            <span class="empty-icon">🧾</span>
                            <h2>Sin facturas aún</h2>
                            <p>Las facturas que generes aparecerán aquí.</p>
                        </div>
                    `;
                }
            } catch (error) {
                console.error("Error al obtener facturas:", error);
            }
        }

        // ===========================
        // CERRAR SESIÓN
        // ===========================
        function logout() {
            window.location.href = 'logout.php';
        }

        // ===========================
        // REGISTRO FLASH (CUENTA)
        // ===========================
        function abrirModalRegistroFlash() {
            document.getElementById('modal-registro-flash').style.display = 'flex';
            // Pre-llenar cédula si ya se escribió algo en el form principal
            const cedula = document.getElementById('cliente_cedula').value.trim();
            if(cedula) document.getElementById('flash_cedula').value = cedula;
        }

        function cerrarModalRegistroFlash() {
            document.getElementById('modal-registro-flash').style.display = 'none';
        }

        async function enviarRegistroFlash(e) {
            e.preventDefault();
            const cedula = document.getElementById('flash_cedula').value.trim();
            const nombre = document.getElementById('flash_nombre').value.trim();
            const telefono = document.getElementById('flash_telefono').value.trim();

            if (!cedula || !nombre || !telefono) {
                return alert("Todos los campos son obligatorios.");
            }

            try {
                const res = await fetch('api/registro_flash_mesero.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ cedula, nombre, telefono })
                });
                const data = await res.json();
                
                if (data.success) {
                    alert(`✅ Cliente registrado y cuenta creada exitosamente.\n\nEl cliente puede iniciar sesión usando su cédula como Usuario y Contraseña.`);
                    // Autocompletar en el formulario de pedido principal
                    document.getElementById('cliente_cedula').value = cedula;
                    document.getElementById('cliente-encontrado-msg').style.display = 'block';
                    document.getElementById('cliente-nombre-display').textContent = nombre;
                    document.getElementById('cliente-no-encontrado-msg').style.display = 'none';
                    
                    cerrarModalRegistroFlash();
                    document.getElementById('form-registro-flash').reset();
                } else {
                    alert("Error al registrar: " + data.error);
                }
            } catch (err) {
                console.error(err);
                alert("Error de conexión al registrar cliente.");
            }
        }

        // ===========================
        // INICIAR Y AUTO-REFRESCAR
        // Inicializar vistas
        cargarMenu();
        fetchEnCocina();
        fetchListos();
        fetchEntregados();
        fetchFacturasMesero();

        // Refrescar automáticamente (Polling)
        setInterval(() => {
            fetchEnCocina();
            fetchListos();
            fetchEntregados();
            fetchFacturasMesero();
        }, 5000); // 5 segundos
        // ===========================
        // MODAL ERROR DE INVENTARIO
        // ===========================
        function mostrarModalErrorInventario(mensaje) {
            const modal = document.getElementById('modal-error-inventario');
            const modalContent = document.getElementById('modal-error-inventario-content');
            document.getElementById('error-inventario-msg').innerText = mensaje;
            modal.style.display = 'flex';
            setTimeout(() => {
                modal.style.opacity = '1';
                modalContent.style.transform = 'translateY(0)';
            }, 10);
        }

        function cerrarModalErrorInventario() {
            const modal = document.getElementById('modal-error-inventario');
            const modalContent = document.getElementById('modal-error-inventario-content');
            modal.style.opacity = '0';
            modalContent.style.transform = 'translateY(-20px)';
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        }

    </script>
    
    <!-- MODAL ERROR INVENTARIO -->
    <div id="modal-error-inventario" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); align-items:center; justify-content:center; z-index:2000; padding:15px; opacity:0; transition: opacity 0.3s ease;">
        <div style="background:white; padding:30px; border-radius:20px; width:100%; max-width:450px; box-shadow:0 15px 35px rgba(0,0,0,0.3); text-align:center; transform:translateY(-20px); transition: transform 0.3s ease;" id="modal-error-inventario-content">
            <div style="font-size: 3rem; margin-bottom: 15px;">⚠️</div>
            <h2 style="color:#e74c3c; margin-bottom:15px; font-family:'PlayfairDisplay', serif;">Platillo no disponible</h2>
            <p id="error-inventario-msg" style="font-size:1rem; color:#444; line-height: 1.5; margin-bottom:25px;"></p>
            <button onclick="cerrarModalErrorInventario()" style="background:#e74c3c; color:white; border:none; padding:12px 25px; border-radius:12px; font-weight:bold; font-size:1rem; cursor:pointer; width:100%; transition: background 0.3s;" onmouseover="this.style.background='#c0392b'" onmouseout="this.style.background='#e74c3c'">Entendido</button>
        </div>
    </div>
    
    <!-- MODAL REGISTRO FLASH -->
    <div id="modal-registro-flash" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); align-items:center; justify-content:center; z-index:2000; padding:15px;">
        <div style="background:white; padding:25px; border-radius:15px; width:100%; max-width:400px; box-shadow:0 10px 30px rgba(0,0,0,0.3);">
            <h2 style="color:var(--ocean); margin-bottom:15px; font-family:'PlayfairDisplay', serif;">➕ Registro Rápido</h2>
            <p style="font-size:0.9rem; color:#666; margin-bottom:20px;">Registre al cliente para crearle una cuenta automáticamente. Su usuario y contraseña será su número de cédula.</p>
            
            <form id="form-registro-flash" onsubmit="enviarRegistroFlash(event)">
                <div style="margin-bottom:15px;">
                    <label style="display:block; margin-bottom:5px; font-weight:bold; color:var(--ocean);">Cédula</label>
                    <input type="text" id="flash_cedula" required style="width:100%; padding:10px; border-radius:8px; border:1px solid #ccc; font-family:'PlayfairDisplay', serif;">
                </div>
                <div style="margin-bottom:15px;">
                    <label style="display:block; margin-bottom:5px; font-weight:bold; color:var(--ocean);">Nombre Completo</label>
                    <input type="text" id="flash_nombre" required style="width:100%; padding:10px; border-radius:8px; border:1px solid #ccc; font-family:'PlayfairDisplay', serif;">
                </div>
                <div style="margin-bottom:20px;">
                    <label style="display:block; margin-bottom:5px; font-weight:bold; color:var(--ocean);">Teléfono</label>
                    <input type="text" id="flash_telefono" required style="width:100%; padding:10px; border-radius:8px; border:1px solid #ccc; font-family:'PlayfairDisplay', serif;">
                </div>
                
                <div style="display:flex; gap:10px;">
                    <button type="button" onclick="cerrarModalRegistroFlash()" style="flex:1; background:#95a5a6; color:white; border:none; padding:12px; border-radius:8px; cursor:pointer; font-weight:bold;">Cancelar</button>
                    <button type="submit" style="flex:1; background:#e67e22; color:white; border:none; padding:12px; border-radius:8px; cursor:pointer; font-weight:bold;">Registrar</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
