<?php
session_start();

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['rol']) !== 'cliente') {
    header('Location: login.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Milano - Portal del Cliente</title>
    <link rel="icon" type="image/png" href="logo%20milano.png">

    <link rel="stylesheet" href="style.css">
    <style>
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
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 20px;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 10px 30px -10px rgba(0, 40, 60, 0.2);
        }

        .header-info h1 { color: var(--ocean); font-size: 1.8rem; margin-bottom: 5px; }
        .header-info p { color: var(--text-muted); opacity: 0.8; }
        
        .logout-btn {
            background: #ff6b6b; color: white; border: none; padding: 10px 20px;
            border-radius: 12px; font-family: 'PlayfairDisplay', serif; font-weight: 600;
            cursor: pointer; transition: all 0.3s ease;
        }
        .logout-btn:hover { background: #ff5252; transform: translateY(-2px); }

        .tabs-container {
            display: flex; gap: 10px; margin-bottom: 5px;
        }
        .tab-btn {
            flex: 1; background: var(--card-bg); border: 1px solid rgba(255, 255, 255, 0.5);
            color: var(--text-muted); padding: 15px; border-radius: 15px;
            font-weight: 600; font-size: 1.1rem; cursor: pointer; transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        .tab-btn.active { background: var(--primary); color: white; border-color: var(--primary); }

        .tab-content { display: none; animation: fadeIn 0.4s ease forwards; }
        .tab-content.active { display: block; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        /* ===========================
           MAPA DE MESAS
           =========================== */
        .salon-container {
            background: var(--card-bg); backdrop-filter: blur(20px);
            border-radius: 20px; padding: 30px; border: 1px solid rgba(255, 255, 255, 0.5);
        }

        .salon-zone { margin-bottom: 40px; }
        .salon-zone h3 {
            color: var(--ocean); font-size: 1.4rem; margin-bottom: 15px;
            border-bottom: 2px solid var(--border-color); padding-bottom: 5px;
        }

        .tables-grid {
            display: flex; flex-wrap: wrap; gap: 15px; justify-content: center;
        }

        .mesa-card {
            width: 100px; height: 100px; border-radius: 15px;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;
            position: relative; box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            color: white; font-weight: 600; text-align: center;
        }

        .mesa-card:hover { transform: scale(1.05); box-shadow: 0 8px 15px rgba(0,0,0,0.2); }
        .mesa-num { font-size: 1.5rem; margin-bottom: 5px; }
        .mesa-cap { font-size: 0.8rem; opacity: 0.9; }

        /* Estados de la mesa */
        .mesa-disponible { background: linear-gradient(135deg, #4CAF50, #2E7D32); }
        .mesa-reservada { background: linear-gradient(135deg, #FFC107, #FF8F00); cursor: not-allowed; }
        .mesa-ocupada { background: linear-gradient(135deg, #F44336, #C62828); cursor: not-allowed; }

        /* ===========================
           MODAL DE RESERVA
           =========================== */
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 40, 60, 0.7); backdrop-filter: blur(5px);
            display: none; justify-content: center; align-items: center; z-index: 100;
        }

        .modal-box {
            background: var(--cream); border-radius: 20px; padding: 30px;
            width: 90%; max-width: 400px; text-align: center;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
        }

        .modal-box h2 { color: var(--ocean); margin-bottom: 10px; }
        .modal-box p { color: var(--text-muted); margin-bottom: 20px; }

        .modal-form .input-group { text-align: left; margin-bottom: 15px; }
        .modal-form label { display: block; font-weight: 600; color: var(--ocean); margin-bottom: 5px; }
        .modal-form input {
            width: 100%; padding: 12px; border-radius: 10px; border: 1px solid var(--border-color);
            background: white; font-family: 'PlayfairDisplay', serif; outline: none;
        }

        .modal-actions { display: flex; gap: 10px; margin-top: 20px; }
        .btn-cancel, .btn-confirm { flex: 1; padding: 12px; border-radius: 10px; font-weight: 600; cursor: pointer; border: none; }
        .btn-cancel { background: #ddd; color: #555; }
        .btn-confirm { background: var(--primary); color: white; }

        /* ===========================
           LISTA DE PEDIDOS Y RESERVAS
           =========================== */
        .history-card {
            background: var(--card-bg); backdrop-filter: blur(20px); border-radius: 20px;
            padding: 25px; border: 1px solid rgba(255, 255, 255, 0.5); margin-bottom: 15px;
        }

        .history-card h3 { color: var(--ocean); border-bottom: 1px solid var(--border-color); padding-bottom: 10px; margin-bottom: 15px; }
        .history-item {
            background: rgba(255,255,255,0.6); padding: 15px; border-radius: 12px; margin-bottom: 10px;
            display: flex; justify-content: space-between; align-items: center; border: 1px solid rgba(0,0,0,0.05);
        }
        .history-detail { display: flex; flex-direction: column; gap: 5px; }
        .badge { padding: 4px 10px; border-radius: 15px; font-size: 0.8rem; font-weight: bold; }
        .badge.Activa { background: #E8F5E9; color: #2E7D32; }
        .badge.Cancelada { background: #FFEBEE; color: #C62828; }
        .badge.Completada { background: #E3F2FD; color: #1565C0; }
        
        .badge.Pendiente { background: #FFF3E0; color: #E65100; }
        .badge.Listo { background: #E8F5E9; color: #2E7D32; }
        .badge.Entregado { background: #E3F2FD; color: #1565C0; }
        .badge.Facturado { background: #F3E5F5; color: #6A1B9A; }

        .pedido-items { font-size: 0.85rem; color: #555; margin-top: 5px; }

        /* ===========================
           ESTILOS DEL MENÚ DIGITAL
           =========================== */
        @font-face{
            font-family: PlayfairDisplay;
            src: url("fonts/PlayfairDisplay.ttf") format('opentype');
        }

        #content-menu {
            display: none; justify-content: center;
        }
        #content-menu.active {
            display: flex;
        }

        #content-menu .page-wrapper {
            display: flex; flex-direction: row; max-width: 1250px; width: 100%; height: 880px;
            background-color: #fdf0cd; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            border-radius: 8px; overflow: hidden; align-items: stretch;
            font-family: 'PlayfairDisplay', serif;
        }

        #content-menu .menu-images {
            display: flex; flex-direction: column; width: 350px; flex-shrink: 0; gap: 1px; height: 100%;
        }

        #content-menu .image-box {
            flex: 1; overflow: hidden; position: relative; border-radius: 0;
        }

        #content-menu .image-box img {
            width: 100%; height: 100%; object-fit: cover; display: block;
            transition: transform 0.6s cubic-bezier(0.25, 1, 0.5, 1);
        }
        #content-menu .image-box:hover img { transform: scale(1.04); }

        #content-menu .menu-container {
            padding: 45px 50px 30px 50px; background-color: #fdf0cd; display: flex;
            flex-direction: column; flex: 1; height: 100%; justify-content: flex-start;
        }

        #content-menu .menu-header { text-align: center; margin-bottom: 35px; }
        #content-menu .menu-header .subtitle {
            font-size: 0.8rem; letter-spacing: 3px; color: #8c8275; font-weight: 600; font-family: 'PlayfairDisplay', serif;
        }
        #content-menu .menu-header .main-title {
            font-family: 'PlayfairDisplay', serif; font-size: 3.2rem; font-weight: 600; margin: 5px 0; color: #1a1a1a;
        }
        #content-menu .menu-header .tagline {
            font-size: 1rem; letter-spacing: 4px; color: #5c554c; text-transform: uppercase; font-family: 'PlayfairDisplay', serif;
        }

        #content-menu .menu-grid {
            display: grid; grid-template-columns: repeat(2, 1fr); column-gap: 60px; row-gap: 40px; flex-grow: 0;
        }

        #content-menu .menu-section h2 {
            font-family: 'PlayfairDisplay', serif; font-size: 1.35rem; letter-spacing: 2px;
            border-bottom: 1px solid #1a1a1a; padding-bottom: 6px; margin-bottom: 20px; color: #1a1a1a;
        }

        #content-menu .menu-item {
            display: flex; justify-content: space-between; align-items: baseline;
            margin-bottom: 10px; font-size: 1rem; font-weight: 300; font-family: 'PlayfairDisplay', serif;
        }
        #content-menu .menu-item::after {
            content: ""; flex-grow: 1; border-bottom: 1px dotted #b3b3b3;
            margin: 0 12px; position: relative; top: -4px; order: 2;
        }

        #content-menu .item-name { order: 1; color: #2b2b2b; font-weight: bold; font-family: 'PlayfairDisplay', serif; }
        #content-menu .item-price { font-weight: 600; color: #1a1a1a; order: 3; font-family: 'PlayfairDisplay', serif; }

        #content-menu .menu-footer {
            text-align: center; margin-top: auto; padding-top: 15px; border-top: 1px solid #f2ede4;
            font-size: 1rem; letter-spacing: 1px; color: #1a1a1a; font-family: 'PlayfairDisplay', serif;
        }

        #content-menu .item-add-btn {
            background: var(--primary); color: white; border: none; padding: 5px 10px; border-radius: 5px;
            cursor: pointer; font-size: 0.8rem; margin-left: 10px; font-weight: bold; order: 4; transition: 0.2s;
        }
        #content-menu .item-add-btn:hover { background: #d35400; transform: scale(1.05); }

        /* Carrito Flotante */
        #carrito-flotante {
            position: fixed; bottom: 20px; right: 20px; width: 320px; background: white;
            border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); z-index: 1000;
            display: none; flex-direction: column; overflow: hidden; border: 2px solid var(--primary);
        }
        #carrito-header {
            background: var(--primary); color: white; padding: 15px; font-weight: bold;
            display: flex; justify-content: space-between; align-items: center; cursor: pointer;
        }
        #carrito-body { padding: 15px; max-height: 300px; overflow-y: auto; }
        .carrito-item { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 0.9rem; border-bottom: 1px solid #eee; padding-bottom: 5px;}
        #carrito-footer { padding: 15px; border-top: 1px solid #eee; display: flex; flex-direction: column; gap: 10px; }
        .btn-enviar-pedido { background: var(--ocean); color: white; border: none; padding: 10px; border-radius: 8px; font-weight: bold; cursor: pointer; }

        /* Diseño Reservas Elegante */
        #content-mesas.active { display: block; }
        
        #mesas-zones-view { display: flex; flex-wrap: wrap; gap: 20px; justify-content: center; }
        #mesas-reservation-view { display: none; gap: 20px; align-items: flex-start; }
        .salon-col-left { flex: 1; width: 100%; }
        .salon-col-right { flex: 1; min-width: 300px; background: var(--cream); border-radius: 20px; padding: 30px; box-shadow: 0 20px 40px rgba(0,0,0,0.2); }
        
        .zone-card {
            background: var(--card-bg); backdrop-filter: blur(20px); border-radius: 20px;
            overflow: hidden; border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1); transition: transform 0.3s;
            cursor: pointer; width: 300px;
        }
        .zone-card:hover { transform: translateY(-5px); box-shadow: 0 15px 35px rgba(0,0,0,0.2); }
        .zone-card img { width: 100%; height: 180px; object-fit: cover; display: block; }
        .zone-info { padding: 15px; }
        .zone-info h4 { color: var(--ocean); margin-bottom: 5px; font-family: 'PlayfairDisplay', serif; font-size: 1.2rem; }
        .zone-info p { font-size: 0.9rem; color: #555; line-height: 1.4; }
        .zone-price { margin-top: 10px; font-weight: bold; color: var(--primary); font-size: 1.1rem; }

        @media (max-width: 1024px) {
            #content-menu .page-wrapper { flex-direction: column; height: auto; }
            #content-menu .menu-images { flex-direction: row; height: 260px; width: 100%; }
            #content-menu .menu-container { padding: 50px 40px; }
            #mesas-reservation-view { flex-direction: column; }
        }
        @media (max-width: 768px) {
            #content-menu .menu-images { height: 180px; }
            #content-menu .menu-grid { grid-template-columns: 1fr; row-gap: 30px; }
            #content-menu .menu-header .main-title { font-size: 2.5rem; }
        }

    </style>
</head>
<body>
    <div class="waves">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="#49beaa" fill-opacity="0.3" d="M0,160L48,154.7C96,149,192,139,288,149.3C384,160,480,192,576,202.7C672,213,768,203,864,176C960,149,1056,107,1152,96C1248,85,1344,107,1392,117.3L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>
    </div>

    <div class="dashboard-container">
        <div class="header-bar">
            <div class="header-info">
                <h1>🍽️ Portal del Cliente - Milano</h1>
                <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?></p>
            </div>
            <button class="logout-btn" onclick="window.location.href='logout.php'">Cerrar Sesión</button>
        </div>

        <!-- Pestañas (Submenú) -->
        <div class="tabs-container" style="flex-wrap: wrap;">
            <button class="tab-btn active" onclick="switchTab('menu')" id="tab-menu">🍽️ Menú</button>
            <button class="tab-btn" onclick="switchTab('acerca')" id="tab-acerca">ℹ️ Acerca de</button>
            <button class="tab-btn" onclick="switchTab('mesas')" id="tab-mesas">🏨 Reservas</button>
            <button class="tab-btn" onclick="switchTab('historial')" id="tab-historial">📜 Pedidos</button>
            <button class="tab-btn" onclick="switchTab('facturas')" id="tab-facturas">🧾 Facturas</button>
        </div>

        <!-- TAB MENU -->
        <div class="tab-content active" id="content-menu">
            <div class="page-wrapper">
                <aside class="menu-images">
                    <div class="image-box"><img src="imagenes/categorias/entrada.jpg" onerror="this.src='imagenes/comida1.jpg';"></div>
                    <div class="image-box"><img src="imagenes/categorias/plato_fuerte.jpg" onerror="this.src='imagenes/comida2.jpg';"></div>
                    <div class="image-box"><img src="imagenes/categorias/sopa.jpg" onerror="this.src='imagenes/comida3.jpg';"></div>
                    <div class="image-box"><img src="imagenes/categorias/bebida.jpg" onerror="this.src='imagenes/comida4.jpg';"></div>
                </aside>
                <div class="menu-container">
                    <header class="menu-header">
                        <p class="subtitle">DESDE 1987</p>
                        <h1 class="main-title">Restaurante Milano</h1>
                        <p class="tagline">Marisquería</p>
                    </header>
                    <main class="menu-grid" id="menu-grid">
                        <p style="text-align:center; width:100%; grid-column:span 2;">Cargando menú...</p>
                    </main>
                    <footer class="menu-footer">
                        <p>www.restaurantemilano.com</p>
                    </footer>
                </div>
            </div>
        </div>

        <!-- TAB ACERCA DE -->
        <div class="tab-content" id="content-acerca">
            <div class="salon-container">
                <h3 style="color:var(--ocean); margin-bottom:15px; border-bottom:2px solid var(--border-color); padding-bottom:5px;">Acerca de Milano</h3>
                <p style="color:var(--text-muted); line-height:1.8; font-size:1.1rem;">
                    Bienvenidos a <strong>Restaurante Milano</strong>, el lugar donde la tradición culinaria se encuentra con la innovación. 
                    Nuestro compromiso es ofrecerte platillos elaborados con los ingredientes más frescos, un ambiente inigualable y un servicio de excelencia.<br><br>
                    <strong>📍 Ubicación:</strong> Calle Principal, Centro Ciudad<br>
                    <strong>📞 Teléfono:</strong> +1 234 567 8900<br>
                    <strong>🕒 Horario:</strong> Lunes a Domingo, 12:00 PM - 11:00 PM
                </p>
            </div>
        </div>

        <!-- TAB MESAS -->
        <div class="tab-content" id="content-mesas">
            <!-- VISTA DE ZONAS (INICIAL) -->
            <div id="mesas-zones-view">
                <div class="zone-card" onclick="selectZona('Salón Principal', 10)">
                    <img src="imagenes/salon1.jpg" alt="Zona A">
                    <div class="zone-info">
                        <h4>Zona A - Salón Principal</h4>
                        <p>Ambiente elegante y acogedor ideal para el disfrute en familia o amigos con vistas al restaurante.</p>
                        <div class="zone-price">Reserva: $10.00</div>
                    </div>
                </div>
                <div class="zone-card" onclick="selectZona('VIP', 50)">
                    <img src="imagenes/salon2.jpg" alt="Zona VIP">
                    <div class="zone-info">
                        <h4>Zona B - Área VIP</h4>
                        <p>Reservas con mayor privacidad. Un espacio exclusivo para cenas íntimas o reuniones de negocios.</p>
                        <div class="zone-price">Reserva: $50.00</div>
                    </div>
                </div>
                <div class="zone-card" onclick="selectZona('Terraza', 100)">
                    <img src="imagenes/salon3.jpg" alt="Zona Eventos">
                    <div class="zone-info">
                        <h4>Zona C - Eventos Especiales</h4>
                        <p>Espacios amplios con mesas adaptables y más sillas, diseñados para grandes grupos y celebraciones memorables.</p>
                        <div class="zone-price">Reserva: $100.00</div>
                    </div>
                </div>
            </div>

            <!-- VISTA DE RESERVA (DESPUÉS DE SELECCIONAR ZONA) -->
            <div id="mesas-reservation-view">
                <div class="salon-col-left">
                    <button class="logout-btn" style="margin-bottom:15px; background:#ccc; color:#333;" onclick="backToZonas()">⬅ Volver a Zonas</button>
                    <div class="salon-container" id="salon-map">
                        <p style="text-align:center;">Selecciona una mesa disponible en el mapa...</p>
                    </div>
                </div>
                <div class="salon-col-right" id="reserva-form-container" style="display:none;">
                    <h2 style="color:var(--ocean); margin-bottom:10px;">Reservar Mesa <span id="lbl-mesa-num"></span></h2>
                    <p style="color:var(--text-muted); margin-bottom:20px;">Capacidad: <span id="lbl-mesa-cap"></span> personas</p>
                    
                    <form id="form-reserva" class="modal-form">
                        <input type="hidden" id="input-mesa-num">
                        
                        <div class="input-group">
                            <label>Fecha de la Reserva</label>
                            <input type="date" id="input-fecha" required>
                        </div>
                        
                        <div class="input-group">
                            <label>Hora de la Reserva</label>
                            <input type="time" id="input-hora" required>
                        </div>

                        <div class="input-group">
                            <label>Cantidad de Personas</label>
                            <input type="number" id="input-personas" min="1" value="1" required>
                        </div>

                        <div class="input-group">
                            <label>Duración de la Reserva (Horas)</label>
                            <input type="number" id="input-duracion" min="1" max="4" value="2" required title="Máximo 4 horas">
                            <small style="color:var(--text-muted); font-size:0.8rem;">La mesa se liberará automáticamente al terminar este tiempo.</small>
                        </div>
                        
                        <hr style="margin:20px 0; border:none; border-top:1px dashed #ccc;">
                        
                        <h3 style="color:var(--ocean); margin-bottom:10px; font-size:1.1rem;">Simulación de Pago Garantía</h3>
                        <div class="input-group">
                            <label>Tarjeta de Crédito / Débito</label>
                            <input type="text" placeholder="XXXX-XXXX-XXXX-XXXX" required pattern="[0-9\-]+" title="Ingrese números de tarjeta">
                            <div style="display:flex; gap:10px; margin-top:5px;">
                                <input type="text" placeholder="MM/AA" style="width:50%;" required>
                                <input type="text" placeholder="CVV" style="width:50%;" required>
                            </div>
                        </div>
                        
                        <p style="text-align:right; font-weight:bold; font-size:1.1rem; color:var(--primary); margin:15px 0;">Total Reserva: $<span id="lbl-precio-reserva">0.00</span></p>

                        <div class="modal-actions">
                            <button type="submit" class="btn-confirm" style="width:100%;">Pagar y Confirmar Reserva</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- TAB HISTORIAL -->
        <div class="tab-content" id="content-historial">
            <div class="history-card">
                <h3>Tus Reservas</h3>
                <div id="list-reservas"><p>Cargando reservas...</p></div>
            </div>
            <div class="history-card">
                <h3>Tus Pedidos en el Restaurante</h3>
                <div id="list-pedidos"><p>Cargando pedidos...</p></div>
            </div>
        </div>

        <!-- TAB FACTURAS -->
        <div class="tab-content" id="content-facturas">
            <div class="history-card">
                <h3>Tus Facturas Pagadas</h3>
                <div id="list-facturas"><p>Cargando facturas...</p></div>
            </div>
        </div>
    </div>

    <!-- Carrito Flotante (Global - visible en todas las pestañas) -->
    <div id="carrito-flotante">
        <div id="carrito-header" onclick="toggleCarrito()">
            <span>🛒 Mi Pedido (<span id="carrito-count">0</span>)</span>
            <span id="carrito-toggle-icon">▼</span>
        </div>
        <div id="carrito-body"></div>
        <div id="carrito-footer">
            <div style="display:flex; justify-content:space-between; font-weight:bold;">
                <span>Total:</span>
                <span id="carrito-total">$0.00</span>
            </div>
            <button class="btn-enviar-pedido" onclick="enviarPedidoCliente()">Enviar a Cocina 👨‍🍳</button>
        </div>
    </div>



    <script>
        function switchTab(tab) {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            document.getElementById('tab-' + tab).classList.add('active');
            document.getElementById('content-' + tab).classList.add('active');
        }

        let currentSelectedZone = null;
        let currentZonePrice = 0;
        let todasLasMesas = [];

        async function fetchMesas() {
            try {
                const res = await fetch('api/get_mesas.php');
                const data = await res.json();
                
                if (data.success) {
                    todasLasMesas = data.mesas;
                    if(currentSelectedZone) renderSalon();
                } else {
                    alert('Error cargando mesas: ' + data.message);
                }
            } catch (e) {
                console.error(e);
            }
        }

        async function selectZona(zonaKeyword, price) {
            currentSelectedZone = zonaKeyword;
            currentZonePrice = price;
            
            document.getElementById('mesas-zones-view').style.display = 'none';
            document.getElementById('mesas-reservation-view').style.display = 'flex';
            document.getElementById('reserva-form-container').style.display = 'none';
            
            // Siempre recargar mesas frescas de la BD al seleccionar zona
            document.getElementById('salon-map').innerHTML = '<p style="text-align:center;">Cargando mesas...</p>';
            try {
                const res = await fetch('api/get_mesas.php');
                const data = await res.json();
                if (data.success) {
                    todasLasMesas = data.mesas;
                }
            } catch(e) { console.error(e); }
            
            renderSalon();
        }

        function backToZonas() {
            currentSelectedZone = null;
            document.getElementById('mesas-reservation-view').style.display = 'none';
            document.getElementById('mesas-zones-view').style.display = 'flex';
        }

        function renderSalon() {
            const map = document.getElementById('salon-map');
            map.innerHTML = '';
            
            if(!currentSelectedZone) return;

            // Filtrar mesas por la zona seleccionada.
            // Mapa de keywords a posibles nombres en BD
            const zonaMap = {
                'Salón Principal': ['salón principal', 'salon principal', 'regular'],
                'VIP': ['vip', 'salón vip', 'salon vip'],
                'Terraza': ['terraza', 'aire libre', 'exterior', 'evento']
            };
            
            const keywords = zonaMap[currentSelectedZone] || [currentSelectedZone.toLowerCase()];
            
            let mesasZona = todasLasMesas.filter(m => {
                const zona = m.zona_mesa.toLowerCase();
                const clase = (m.clase_mesa || '').toLowerCase();
                return keywords.some(kw => zona.includes(kw) || clase.includes(kw));
            });

            if(mesasZona.length === 0) {
                map.innerHTML = '<div style="text-align:center; padding:40px; color:var(--ocean);"><h3>No hay mesas en esta zona</h3><p style="color:var(--text-muted);">El administrador aún no ha registrado mesas para esta zona.</p></div>';
                return;
            }

            let html = `<div class="salon-zone">
                            <h3>Mesas Disponibles</h3>
                            <div class="tables-grid">`;
            
            mesasZona.forEach(m => {
                const statusClass = 'mesa-' + m.estado_mesa.toLowerCase();
                const title = `Mesa ${m.num_mesa} | Capacidad: ${m.cap_mesa} | Estado: ${m.estado_mesa}`;
                const onClick = m.estado_mesa === 'Disponible' 
                    ? `onclick="openModal(${m.num_mesa}, ${m.cap_mesa})"` 
                    : `onclick="alert('Esta mesa está ${m.estado_mesa} y no puede ser reservada.')"`;
                
                html += `
                    <div class="mesa-card ${statusClass}" title="${title}" ${onClick}>
                        <div class="mesa-num">${m.num_mesa}</div>
                        <div class="mesa-cap">👤 ${m.cap_mesa}</div>
                    </div>
                `;
            });

            html += `</div></div>`;
            map.innerHTML = html;
        }

        /* MODAL LOGIC (Ahora Formulario Integrado) */
        function openModal(num, cap) {
            document.getElementById('lbl-mesa-num').textContent = num;
            document.getElementById('lbl-mesa-cap').textContent = cap;
            document.getElementById('input-mesa-num').value = num;
            document.getElementById('lbl-precio-reserva').textContent = currentZonePrice.toFixed(2);
            
            // Set input personas max
            document.getElementById('input-personas').max = cap;
            document.getElementById('input-personas').value = 1;
            
            // Set min date to today
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('input-fecha').min = today;
            
            document.getElementById('reserva-form-container').style.display = 'block';
            document.getElementById('form-reserva').reset();
            // Restablecer valores default después de reset()
            document.getElementById('input-personas').value = 1;
            document.getElementById('input-mesa-num').value = num;
        }

        document.getElementById('form-reserva').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = e.target.querySelector('.btn-confirm');
            btn.textContent = 'Procesando Pago...';
            btn.disabled = true;

            const fd = new FormData();
            fd.append('num_mesa', document.getElementById('input-mesa-num').value);
            fd.append('fecha_reserva', document.getElementById('input-fecha').value);
            fd.append('hora_reserva', document.getElementById('input-hora').value);
            fd.append('cant_personas', document.getElementById('input-personas').value);
            fd.append('duracion_horas', document.getElementById('input-duracion').value);
            fd.append('pago_simulado', currentZonePrice); // Simulamos mandar el pago

            try {
                // Simular un retraso bancario
                await new Promise(r => setTimeout(r, 1500));
                
                const res = await fetch('api/reservar_mesa.php', { method: 'POST', body: fd });
                const data = await res.json();
                
                if (data.success) {
                    alert(`✅ ¡Pago exitoso y reserva confirmada!\nMonto pagado: $${currentZonePrice.toFixed(2)}`);
                    document.getElementById('reserva-form-container').style.display = 'none';
                    fetchMesas(); // Actualiza el mapa para mostrarla reservada
                    fetchHistorial(); // Actualiza el historial
                } else {
                    alert('Error en reserva: ' + data.message);
                }
            } catch (err) {
                alert('Error de conexión');
            }
            btn.textContent = 'Pagar y Confirmar Reserva';
            btn.disabled = false;
        });

        /* HISTORIAL LOGIC */
        async function fetchHistorial() {
            try {
                const res = await fetch('api/get_mis_pedidos.php');
                const data = await res.json();
                
                const boxRes = document.getElementById('list-reservas');
                const boxPed = document.getElementById('list-pedidos');
                boxRes.innerHTML = '';
                boxPed.innerHTML = '';

                if (data.success) {
                    // Render Reservas
                    if(data.reservas.length === 0) boxRes.innerHTML = '<p>No has hecho ninguna reserva aún.</p>';
                    data.reservas.forEach(r => {
                        boxRes.innerHTML += `
                            <div class="history-item">
                                <div class="history-detail">
                                    <strong>Reserva Mesa #${r.num_mesa}</strong>
                                    <span style="font-size:0.85rem; color:#666;">📅 ${r.fecha_reserva} a las ⏰ ${r.hora_reserva} | 👥 ${r.cant_personas} Personas</span>
                                </div>
                                <span class="badge ${r.estado_reserva}">${r.estado_reserva}</span>
                            </div>
                        `;
                    });

                    // Render Pedidos
                    if(data.pedidos.length === 0) boxPed.innerHTML = '<p>No has realizado pedidos.</p>';
                    data.pedidos.forEach(p => {
                        let platillos = p.detalles.map(d => `${d.cantidad}x ${d.nom_plat}`).join(', ');
                        
                        let timerHtml = '';
                        if (p.estado === 'En espera' || !p.hora_entrega) {
                            timerHtml = `<div style="font-weight:bold; color:#e67e22; font-size:1.1rem; margin-top:5px;">👨‍🍳 Esperando al chef...</div>`;
                        } else if (p.estado !== 'Entregado' && p.hora_entrega) {
                            // Asumimos que hora_entrega viene de MySQL en formato YYYY-MM-DD HH:MM:SS
                            // Para compatibilidad en JS (Safari/iOS), cambiamos '-' por '/'
                            const horaFormat = p.hora_entrega.replace(/-/g, '/');
                            timerHtml = `<div class="timer-countdown" data-entrega="${horaFormat}" id="timer-${p.id_pedido}" style="font-weight:bold; color:#e74c3c; font-size:1.1rem; margin-top:5px;">⏳ Calculando...</div>`;
                        }

                        boxPed.innerHTML += `
                            <div class="history-item" style="flex-direction:column; align-items:flex-start;">
                                <div style="display:flex; justify-content:space-between; width:100%; margin-bottom:5px;">
                                    <strong>Pedido en Mesa #${p.num_mesa}</strong>
                                    <span class="badge ${p.estado}">${p.estado}</span>
                                </div>
                                <div class="pedido-items">🍽️ ${platillos}</div>
                                <div style="display:flex; justify-content:space-between; width:100%; align-items:center;">
                                    <div style="font-weight:bold; color:var(--ocean); margin-top:5px;">Total: $${p.subtotal}</div>
                                    ${timerHtml}
                                </div>
                            </div>
                        `;
                    });
                }
            } catch (e) {
                console.error(e);
            }
        }

        async function fetchMenu() {
            try {
                const res = await fetch('api/get_platillos.php');
                const data = await res.json();
                const grid = document.getElementById('menu-grid');
                grid.innerHTML = '';

                if (data.success && data.platillos.length > 0) {
                    // Agrupar por tipo_plat
                    const menu = {};
                    data.platillos.forEach(plat => {
                        if (!menu[plat.tipo_plat]) menu[plat.tipo_plat] = [];
                        menu[plat.tipo_plat].push(plat);
                    });

                    // Tipos en el orden deseado o alfabético
                    const orden = ['Entradas', 'Platos Fuertes', 'Sopas', 'Bebidas'];
                    const keys = Object.keys(menu).sort((a,b) => orden.indexOf(a) - orden.indexOf(b));

                    keys.forEach(categoria => {
                        let html = `
                            <section class="menu-section">
                                <h2>${categoria.toUpperCase()}</h2>
                        `;
                        menu[categoria].forEach(plat => {
                            const maxDisp = parseInt(plat.max_disponible);
                            const isAgotado = maxDisp <= 0;
                            const btnHtml = isAgotado 
                                ? `<button class="item-add-btn" style="background:#ccc; cursor:not-allowed;" disabled>Agotado</button>`
                                : `<button class="item-add-btn" onclick="agregarAlPedido('${plat.cod_plat}', '${plat.nom_plat.replace(/'/g, "\\'")}', ${plat.precio_plat}, ${maxDisp})">Añadir</button>`;
                                
                            html += `
                                <div class="menu-item" title="${plat.desc_plat}">
                                    <span class="item-name">${plat.nom_plat}</span>
                                    <span class="item-price">$${parseFloat(plat.precio_plat).toFixed(2)}</span>
                                    ${btnHtml}
                                </div>
                            `;
                        });
                        html += `</section>`;
                        grid.innerHTML += html;
                    });
                } else {
                    grid.innerHTML = '<p style="text-align:center; width:100%; grid-column:span 2;">El menú no está disponible en este momento.</p>';
                }
            } catch (e) {
                console.error(e);
            }
        }

        async function fetchFacturas() {
            try {
                const res = await fetch('api/get_mis_facturas.php');
                const data = await res.json();
                const box = document.getElementById('list-facturas');
                box.innerHTML = '';

                if (data.success) {
                    if(data.facturas.length === 0) {
                        box.innerHTML = '<p>No tienes facturas emitidas.</p>';
                        return;
                    }
                    data.facturas.forEach(f => {
                        let platillos = f.detalles.map(d => `${d.cantidad}x ${d.nom_plat} ($${d.subtotal})`).join('<br>');
                        box.innerHTML += `
                            <div class="history-item" style="flex-direction:column; align-items:flex-start; border-left:4px solid var(--ocean);">
                                <div style="display:flex; justify-content:space-between; width:100%; margin-bottom:10px;">
                                    <strong>🧾 Factura del ${f.dia_fact}/${f.mes_fact}/${f.año_fact} ${f.hora_fact}</strong>
                                    <span class="badge" style="background:#2ecc71; color:white;">Pagado: ${f.metodo_pago}</span>
                                </div>
                                <div style="font-size:0.9rem; color:#555; margin-bottom:10px; width:100%;">
                                    ${platillos}
                                </div>
                                <div style="display:flex; justify-content:space-between; width:100%; font-size:0.95rem; border-top:1px dashed #ccc; padding-top:10px;">
                                    <div>Subtotal: $${f.subtotal} | IVA: $${f.iva}</div>
                                    <strong style="color:var(--primary); font-size:1.1rem;">Total: $${f.total}</strong>
                                </div>
                            </div>
                        `;
                    });
                } else {
                    box.innerHTML = '<p>Error cargando facturas.</p>';
                }
            } catch (e) {
                console.error(e);
            }
        }

        // Lógica del Carrito del Cliente
        let pedidoCliente = [];
        
        function toggleCarrito() {
            const body = document.getElementById('carrito-body');
            const footer = document.getElementById('carrito-footer');
            const icon = document.getElementById('carrito-toggle-icon');
            if(body.style.display === 'none') {
                body.style.display = 'block';
                footer.style.display = 'flex';
                icon.textContent = '▼';
            } else {
                body.style.display = 'none';
                footer.style.display = 'none';
                icon.textContent = '▲';
            }
        }

        function agregarAlPedido(cod, nom, precio, maxDisp = 999) {
            let item = pedidoCliente.find(i => i.cod_plat === cod);
            if (item) {
                if (item.cantidad >= maxDisp) {
                    alert(`No puedes añadir más de ${maxDisp} unidades de ${nom}. (Ingredientes insuficientes)`);
                    return;
                }
                item.cantidad++;
            } else {
                if (maxDisp <= 0) {
                    alert(`El platillo ${nom} está agotado.`);
                    return;
                }
                pedidoCliente.push({ cod_plat: cod, nom_plat: nom, precio: parseFloat(precio), cantidad: 1, notas: '', maxDisp: maxDisp });
            }
            renderCarritoCliente();
        }

        function quitarDelPedido(index) {
            pedidoCliente.splice(index, 1);
            renderCarritoCliente();
        }

        function renderCarritoCliente() {
            const container = document.getElementById('carrito-flotante');
            const body = document.getElementById('carrito-body');
            const count = document.getElementById('carrito-count');
            const total = document.getElementById('carrito-total');

            if (pedidoCliente.length === 0) {
                container.style.display = 'none';
                return;
            }

            container.style.display = 'flex';
            let t = 0;
            let c = 0;
            body.innerHTML = '';
            
            pedidoCliente.forEach((p, idx) => {
                let sub = p.precio * p.cantidad;
                t += sub;
                c += p.cantidad;
                body.innerHTML += `
                    <div class="carrito-item">
                        <div>
                            <strong>${p.cantidad}x</strong> ${p.nom_plat}<br>
                            <span style="color:#888; font-size:0.8rem;">$${p.precio.toFixed(2)} c/u</span>
                        </div>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <strong>$${sub.toFixed(2)}</strong>
                            <button onclick="quitarDelPedido(${idx})" style="background:#e74c3c; color:white; border:none; border-radius:50%; width:20px; height:20px; cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:0.8rem;">X</button>
                        </div>
                    </div>
                `;
            });
            count.textContent = c;
            total.textContent = '$' + t.toFixed(2);

        }

        async function enviarPedidoCliente() {
            if (pedidoCliente.length === 0) return;
            
            const btn = document.querySelector('.btn-enviar-pedido');
            btn.textContent = 'Enviando...';
            btn.disabled = true;

            try {
                const response = await fetch('api/crear_pedido.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        cedula_cli: '<?php echo $_SESSION["cedula_cli"] ?? ""; ?>',
                        platillos: pedidoCliente
                    })
                });
                const data = await response.json();
                
                if (data.success) {
                    alert(`✅ Pedido enviado a cocina exitosamente.`);
                    pedidoCliente = [];
                    renderCarritoCliente();
                    fetchHistorial();
                    switchTab('historial');
                } else {
                    alert("Error: " + data.error);
                }
            } catch (error) {
                console.error(error);
                alert("Ocurrió un error al enviar el pedido.");
            } finally {
                btn.textContent = 'Enviar a Cocina 👨‍🍳';
                btn.disabled = false;
            }
        }

        // Init
        fetchMesas();
        fetchHistorial();
        fetchMenu();
        fetchFacturas();
        
        // Temporizador en vivo
        setInterval(() => {
            document.querySelectorAll('.timer-countdown').forEach(el => {
                const targetStr = el.getAttribute('data-entrega');
                const targetTime = new Date(targetStr).getTime();
                const now = new Date().getTime();
                const distance = targetTime - now;

                if (distance < 0) {
                    el.innerHTML = "🏁 Tiempo cumplido";
                    el.style.color = "#27ae60";
                } else {
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                    // Padding cero para segundos
                    const sStr = seconds < 10 ? '0' + seconds : seconds;
                    el.innerHTML = `⏳ ${minutes}:${sStr}`;
                }
            });
        }, 1000);
        // Recargar mapas cada 10 segundos
        setInterval(fetchMesas, 10000);
    </script>
</body>
</html>
