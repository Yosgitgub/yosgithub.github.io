<?php
session_start();

// Validar que el usuario haya iniciado sesión y sea Administrador
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['cargo']) !== 'administrador') {
    header('Location: login.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Restaurante Milano</title>
    <link rel="icon" type="image/png" href="logo%20milano.png">

    <link rel="stylesheet" href="style.css">
    <style>
        body {
            align-items: flex-start;
            padding: 20px;
            height: 100vh;
            overflow: hidden; /* Prevent body scrolling, use inner scrolls */
        }

        .dashboard-container {
            width: 100%;
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 20px;
            height: calc(100vh - 40px);
            z-index: 10;
        }

        .header-bar {
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 20px;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 10px 30px -10px rgba(0, 40, 60, 0.2);
            flex-shrink: 0;
        }

        .header-info h1 {
            color: var(--ocean);
            font-size: 1.6rem;
            margin-bottom: 2px;
        }

        .header-info p {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .logout-btn {
            background: #ff6b6b;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 10px;
            font-family: 'PlayfairDisplay', serif;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .logout-btn:hover { background: #ff5252; }

        /* ===========================
           LAYOUT PRINCIPAL ADMIN
           =========================== */
        .admin-layout {
            display: flex;
            gap: 20px;
            flex: 1;
            min-height: 0; /* Important for flex child scrolling */
        }

        /* PANEL IZQUIERDO: FORMULARIOS */
        .sidebar-forms {
            width: 380px;
            display: flex;
            flex-direction: column;
            gap: 20px;
            overflow-y: auto;
            padding-right: 5px;
        }

        /* SCROLLBAR CUSTOM */
        .sidebar-forms::-webkit-scrollbar,
        .main-content::-webkit-scrollbar {
            width: 8px;
        }
        .sidebar-forms::-webkit-scrollbar-track,
        .main-content::-webkit-scrollbar-track {
            background: rgba(255,255,255,0.2);
            border-radius: 10px;
        }
        .sidebar-forms::-webkit-scrollbar-thumb,
        .main-content::-webkit-scrollbar-thumb {
            background: var(--primary);
            border-radius: 10px;
        }

        .form-card {
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 10px 30px -10px rgba(0, 40, 60, 0.2);
        }

        .form-card h3 {
            color: var(--ocean);
            margin-bottom: 15px;
            font-size: 1.2rem;
            border-bottom: 2px solid var(--border-color);
            padding-bottom: 8px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--ocean);
            margin-bottom: 5px;
        }

        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 10px 12px;
            background: var(--input-bg);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            color: var(--ocean);
            font-family: 'PlayfairDisplay', serif;
            font-size: 0.95rem;
            outline: none;
            resize: vertical;
        }

        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(73, 190, 170, 0.2);
        }

        /* Lista de Ingredientes en el Formulario */
        .ingredient-selector {
            background: var(--input-bg);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            max-height: 200px;
            overflow-y: auto;
            padding: 10px;
        }

        .ing-item {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
            padding-bottom: 8px;
            border-bottom: 1px dashed rgba(0,0,0,0.1);
        }

        .ing-item:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .ing-item input[type="number"] {
            width: 70px;
            padding: 4px 8px;
        }

        .btn-submit {
            width: 100%;
            padding: 12px;
            background: var(--ocean);
            color: var(--cream);
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .btn-submit:hover {
            background: var(--primary);
            transform: translateY(-2px);
        }

        /* PANEL DERECHO: VISTAS */
        .main-content {
            flex: 1;
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 10px 30px -10px rgba(0, 40, 60, 0.2);
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }

        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 2px solid var(--border-color);
            padding-bottom: 10px;
        }

        .tab {
            padding: 8px 20px;
            background: transparent;
            border: none;
            border-radius: 20px;
            color: var(--text-muted);
            font-weight: 600;
            font-family: 'PlayfairDisplay', serif;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .tab.active {
            background: var(--primary);
            color: white;
        }

        /* Grid de Inventario */
        .inventory-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
        }

        .ing-card {
            background: rgba(255,255,255,0.6);
            border: 1px solid rgba(0,0,0,0.05);
            border-radius: 12px;
            padding: 15px;
            text-align: center;
            transition: transform 0.2s;
        }

        .ing-card:hover { transform: translateY(-3px); }

        .ing-name {
            font-weight: 700;
            color: var(--ocean);
            font-size: 1.1rem;
            margin-bottom: 5px;
        }

        .ing-type {
            font-size: 0.8rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }

        .ing-stock {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
        }

        .ing-stock.low {
            color: #ff6b6b;
        }

        @media (max-width: 900px) {
            .admin-layout {
                flex-direction: column;
            }
            .sidebar-forms {
                width: 100%;
                max-height: 50vh;
            }
        }

        /* Search Bars */
        .search-bar {
            width: 100%;
            padding: 10px 15px;
            margin-bottom: 15px;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            font-family: 'PlayfairDisplay', serif;
            background: rgba(255, 255, 255, 0.8);
            outline: none;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        .search-bar:focus {
            border-color: var(--ocean);
            box-shadow: 0 0 0 3px rgba(33, 158, 188, 0.2);
        }

        /* Modal Edit */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5);
            backdrop-filter: blur(5px);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background: var(--card-bg);
            padding: 25px;
            border-radius: 20px;
            width: 320px;
            box-shadow: 0 10px 30px -10px rgba(0,0,0,0.3);
            border: 1px solid rgba(255,255,255,0.5);
        }
        .modal-content h3 { color: var(--ocean); margin-bottom: 15px; font-size: 1.2rem; border-bottom: 2px solid var(--border-color); padding-bottom: 8px;}
        .modal-buttons { display: flex; gap: 10px; margin-top: 15px; }
        .btn-cancel { background: #ff6b6b; color: white; padding: 10px; border-radius: 10px; border: none; cursor: pointer; flex: 1; font-weight: 600; font-family: 'PlayfairDisplay', serif;}
        .btn-save { background: var(--ocean); color: white; padding: 10px; border-radius: 10px; border: none; cursor: pointer; flex: 1; font-weight: 600; font-family: 'PlayfairDisplay', serif;}
        .ing-card { cursor: pointer; }
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
                <h1>⚙️ Panel de Administración</h1>
                <p>Gestión de Inventario y Menú - Milano</p>
            </div>
            <button class="logout-btn" onclick="logout()">Cerrar Sesión</button>
        </div>

        <div class="admin-layout">
            <!-- PANEL IZQUIERDO -->
            <div class="sidebar-forms">
                <!-- FORMULARIO AÑADIR INGREDIENTE -->
                <div class="form-card">
                    <h3>Añadir Ingrediente (Stock)</h3>
                    <form id="form-ingrediente">
                        <div class="form-group">
                            <label>Nombre del Ingrediente</label>
                            <input type="text" id="ing_nombre" required placeholder="Ej: Tomate, Ron, Harina">
                        </div>
                        <div class="form-group">
                            <label>Unidad de Medida</label>
                            <select id="ing_tipo" required>
                                <option value="Unidades">Unidades (uds)</option>
                                <option value="Kilos (Kg)">Kilos (Kg)</option>
                                <option value="Gramos (g)">Gramos (g)</option>
                                <option value="Litros (L)">Litros (L)</option>
                                <option value="Mililitros (ml)">Mililitros (ml)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Stock Inicial</label>
                            <input type="number" id="ing_stock" name="stock_actual" min="0" step="0.01" style="padding:10px; border-radius:8px; border:1px solid #ccc;">
                        </div>
                        <button type="submit" class="btn-submit">Guardar Ingrediente</button>
                    </form>
                </div>

                <!-- FORMULARIO AÑADIR PLATILLO -->
                <div class="form-card">
                    <h3>Crear Nuevo Platillo</h3>
                    <form id="form-platillo">
                        <div class="form-group">
                            <label>Nombre del Platillo</label>
                            <input type="text" id="plat_nombre" required placeholder="Ej: Pizza Margarita">
                        </div>
                        <div class="form-group">
                            <label>Descripción</label>
                            <textarea id="plat_desc" rows="2" placeholder="Breve descripción del plato..."></textarea>
                        </div>
                        <div style="display:flex; gap:10px;">
                            <div class="form-group" style="flex:1;">
                                <label>Categoría</label>
                                <select id="plat_tipo" required>
                                    <option value="Entradas">Entradas</option>
                                    <option value="Platos Fuertes">Platos Fuertes</option>
                                    <option value="Sopas">Sopas</option>
                                    <option value="Bebidas">Bebidas</option>
                                </select>
                            </div>
                            <div class="form-group" style="flex:1;">
                                <label>Precio ($)</label>
                                <input type="number" id="plat_precio" step="0.01" min="0" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Receta (Seleccionar Ingredientes requeridos)</label>
                            <input type="text" id="search-ingredientes" placeholder="Buscar ingrediente..." onkeyup="filterIngredientes()" class="search-bar" style="margin-bottom: 10px;">
                            <div class="ingredient-selector" id="ingredient-list">
                                <!-- Llenado dinámicamente por JS -->
                                <p style="font-size:0.85rem; color:#666;">Cargando ingredientes...</p>
                            </div>
                        </div>

                        <button type="submit" class="btn-submit">Crear Platillo y Receta</button>
                    </form>
                </div>

                <!-- FORMULARIO AÑADIR MESA -->
                <div class="form-card">
                    <h3>Añadir Nueva Mesa</h3>
                    <form id="form-mesa">
                        <div class="form-group">
                            <label>Número de Mesa</label>
                            <input type="number" id="mesa_num" min="1" required placeholder="Ej: 15">
                        </div>
                        <div class="form-group">
                            <label>Capacidad (Personas)</label>
                            <input type="number" id="mesa_cap" min="1" required placeholder="Ej: 4">
                        </div>
                        <div class="form-group">
                            <label>Ubicación (Zona)</label>
                            <select id="mesa_zona" required>
                                <option value="Terraza">Eventos Especiales (Terraza)</option>
                                <option value="Salón Principal">Salón Principal</option>
                                <option value="VIP">VIP</option>
                            </select>
                        </div>
                        <button type="submit" class="btn-submit">Añadir Mesa</button>
                    </form>
                </div>
            </div>

            <!-- PANEL DERECHO -->
            <div class="main-content">
                <div class="tabs" style="flex-wrap: wrap;">
                    <button class="tab active" onclick="switchAdminTab('inventario')" id="tab-inventario">📦 Inventario</button>
                    <button class="tab" onclick="switchAdminTab('platillos')" id="tab-platillos">🍔 Menú</button>
                    <button class="tab" onclick="switchAdminTab('reservas')" id="tab-reservas">📅 Reservas</button>
                    <button class="tab" onclick="switchAdminTab('facturas')" id="tab-facturas">🧾 Facturación</button>
                    <button class="tab" onclick="switchAdminTab('clientes')" id="tab-clientes">👥 Clientes</button>
                    <button class="tab" onclick="switchAdminTab('empleados')" id="tab-empleados">👨‍🍳 Personal</button>
                    <a href="generar_qrs.php" target="_blank" class="tab" style="text-decoration:none; background:#2980b9; color:white;">📱 Generar QRs</a>
                </div>

                <!-- VISTA INVENTARIO -->
                <div id="view-inventario" style="display: block;">
                    <input type="text" id="search-inventario" placeholder="Buscar en el inventario..." onkeyup="filterInventario()" class="search-bar">
                    <div class="inventory-grid" id="grid-inventario"></div>
                </div>

                <!-- VISTA PLATILLOS -->
                <div id="view-platillos" style="display: none;">
                    <p style="color:var(--text-muted); margin-bottom: 15px;">Listado de todos los platillos creados y disponibles en el menú.</p>
                    <div class="inventory-grid" id="grid-platillos"></div>
                </div>

                <!-- VISTA RESERVAS -->
                <div id="view-reservas" style="display: none;">
                    <h3 style="color:var(--ocean); margin-bottom:15px; font-family:'PlayfairDisplay', serif;">Gestión de Reservas</h3>
                    <div class="inventory-grid" id="grid-reservas"></div>
                </div>

                <!-- VISTA FACTURAS -->
                <div id="view-facturas" style="display: none;">
                    <h3 style="color:var(--ocean); margin-bottom:15px; font-family:'PlayfairDisplay', serif;">Historial de Facturación</h3>
                    <div id="grid-facturas" style="display:flex; flex-direction:column; gap:15px;"></div>
                </div>

                <!-- VISTA CLIENTES -->
                <div id="view-clientes" style="display: none;">
                    <h3 style="color:var(--ocean); margin-bottom:15px; font-family:'PlayfairDisplay', serif;">Directorio de Clientes</h3>
                    <div class="inventory-grid" id="grid-clientes"></div>
                </div>

                <!-- VISTA EMPLEADOS -->
                <div id="view-empleados" style="display: none;">
                    <h3 style="color:var(--ocean); margin-bottom:15px; font-family:'PlayfairDisplay', serif;">Personal Registrado</h3>
                    <div class="inventory-grid" id="grid-empleados"></div>
                </div>
            </div>
        </div>
        </div>
    </div>

    <!-- MODAL EDITAR INGREDIENTE -->
    <div class="modal-overlay" id="editModal">
        <div class="modal-content">
            <h3>Editar Ingrediente</h3>
            <form id="form-edit-ing">
                <input type="hidden" id="edit_cod">
                <div class="form-group">
                    <label>Nombre</label>
                    <input type="text" id="edit_nombre" required>
                </div>
                <div class="form-group">
                    <label>Unidad de Medida</label>
                    <select id="edit_tipo" required>
                        <option value="Unidades">Unidades (uds)</option>
                        <option value="Kilos (Kg)">Kilos (Kg)</option>
                        <option value="Gramos (g)">Gramos (g)</option>
                        <option value="Litros (L)">Litros (L)</option>
                        <option value="Mililitros (ml)">Mililitros (ml)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Cantidad (Stock)</label>
                    <input type="number" id="edit_stock" step="0.001" min="0" required>
                </div>
                <div class="modal-buttons">
                    <button type="button" class="btn-cancel" onclick="closeEditModal()">Cancelar</button>
                    <button type="button" class="btn-delete" onclick="deleteIngrediente()" id="btn-delete-ing" style="background: #e74c3c; color: white; padding: 10px; border-radius: 10px; border: none; cursor: pointer; font-weight: 600; font-family: 'PlayfairDisplay', serif;">Eliminar</button>
                    <button type="submit" class="btn-save">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Cambiar pestañas derecha
        function switchAdminTab(tab) {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            if(document.getElementById('tab-' + tab)) {
                document.getElementById('tab-' + tab).classList.add('active');
            }
            
            const views = ['inventario', 'platillos', 'reservas', 'facturas', 'clientes', 'empleados'];
            views.forEach(v => {
                if(document.getElementById('view-' + v)) {
                    document.getElementById('view-' + v).style.display = 'none';
                }
            });
            
            document.getElementById('view-' + tab).style.display = 'block';

            // Auto-cargar si es necesario
            if(tab === 'reservas') fetchReservasAdmin();
            if(tab === 'facturas') fetchFacturasAdmin();
            if(tab === 'clientes') fetchClientes();
            if(tab === 'empleados') fetchEmpleados();
        }

        async function fetchReservasAdmin() {
            try {
                const res = await fetch('api/get_todas_reservas.php');
                const data = await res.json();
                const grid = document.getElementById('grid-reservas');
                grid.innerHTML = '';

                if(data.success && data.reservas.length > 0) {
                    data.reservas.forEach(r => {
                        grid.innerHTML += `
                            <div class="ing-card" style="border-left: 5px solid ${r.estado_reserva === 'Activa' ? '#4CAF50' : '#2196F3'}">
                                <div class="ing-type">Mesa ${r.num_mesa} - ${r.zona_mesa}</div>
                                <div class="ing-name">👤 ${r.nom_cli} ${r.ap_cli}</div>
                                <div style="font-size:0.9rem; color:#555; margin-top:10px;">
                                    📅 ${r.fecha_reserva} <br>
                                    ⏰ ${r.hora_reserva} <br>
                                    👥 ${r.cant_personas} Personas
                                </div>
                                <div style="margin-top:10px; font-weight:bold; color:var(--primary);">${r.estado_reserva}</div>
                            </div>
                        `;
                    });
                } else {
                    grid.innerHTML = '<p>No hay reservas registradas.</p>';
                }
            } catch(e) { console.error(e); }
        }

        // Obtener Ingredientes
        async function fetchIngredientes() {
            try {
                const res = await fetch('api/get_inventario.php');
                const data = await res.json();
                
                const gridInv = document.getElementById('grid-inventario');
                const listForm = document.getElementById('ingredient-list');
                
                gridInv.innerHTML = '';
                listForm.innerHTML = '';

                if(data.success && data.ingredientes.length > 0) {
                    data.ingredientes.forEach(ing => {
                        // Llenar Grid
                        const stockClass = parseFloat(ing.stock_actual) <= parseFloat(ing.stock_minimo) ? 'low' : '';
                        gridInv.innerHTML += `
                            <div class="ing-card" onclick="openEditModal('${ing.cod_ingre}', '${ing.desc_ingre.replace(/'/g, "\\'")}', '${ing.stock_actual}', '${ing.tipo_ingre}')" title="Click para editar">
                                <div class="ing-type">${ing.tipo_ingre}</div>
                                <div class="ing-name">${ing.desc_ingre}</div>
                                <div class="ing-stock ${stockClass}">${ing.stock_actual} <span style="font-size:0.5em; color:var(--text-muted);">${ing.tipo_ingre}</span></div>
                            </div>
                        `;

                        // Llenar Formulario de Receta
                        listForm.innerHTML += `
                            <div class="ing-item">
                                <input type="checkbox" id="chk_${ing.cod_ingre}" value="${ing.cod_ingre}" class="ing-chk">
                                <label for="chk_${ing.cod_ingre}" style="flex:1; margin:0; cursor:pointer;">${ing.desc_ingre} <small>(${ing.tipo_ingre})</small></label>
                                <input type="number" id="qty_${ing.cod_ingre}" step="0.001" min="0" placeholder="Cant. en ${ing.tipo_ingre}" disabled>
                            </div>
                        `;
                    });

                    // Habilitar inputs de cantidad cuando se hace click en el checkbox
                    document.querySelectorAll('.ing-chk').forEach(chk => {
                        chk.addEventListener('change', function() {
                            const inputQty = document.getElementById('qty_' + this.value);
                            inputQty.disabled = !this.checked;
                            if(this.checked) inputQty.focus();
                            else inputQty.value = '';
                        });
                    });

                } else {
                    gridInv.innerHTML = '<p>No hay ingredientes en el inventario.</p>';
                    listForm.innerHTML = '<p>No hay ingredientes disponibles.</p>';
                }
            } catch(e) { console.error(e); }
        }

        // Obtener Platillos
        async function fetchPlatillos() {
            try {
                const res = await fetch('api/get_platillos.php');
                const data = await res.json();
                const gridPlat = document.getElementById('grid-platillos');
                gridPlat.innerHTML = '';

                if(data.success && data.platillos.length > 0) {
                    data.platillos.forEach(plat => {
                        let recetaHtml = '';
                        if (plat.receta && plat.receta.length > 0) {
                            recetaHtml = '<ul style="margin-top:10px; padding-left:20px; font-size:0.85rem; color:#555;">';
                            plat.receta.forEach(ing => {
                                recetaHtml += `<li>${ing.cant_ingre} ${ing.tipo_ingre} de ${ing.desc_ingre}</li>`;
                            });
                            recetaHtml += '</ul>';
                        } else {
                            recetaHtml = '<p style="margin-top:10px; font-size:0.85rem; color:#e74c3c; font-style:italic;">Sin ingredientes asignados.</p>';
                        }

                        gridPlat.innerHTML += `
                            <div class="ing-card" style="position:relative;">
                                <button onclick="deletePlatillo('${plat.cod_plat}')" style="position:absolute; top:10px; right:10px; background:#ff6b6b; color:white; border:none; border-radius:50%; width:25px; height:25px; cursor:pointer; display:flex; align-items:center; justify-content:center; font-weight:bold; transition: 0.3s;" title="Eliminar Platillo" onmouseover="this.style.background='#ff5252'" onmouseout="this.style.background='#ff6b6b'">✕</button>
                                <div class="ing-type">${plat.tipo_plat}</div>
                                <div class="ing-name">${plat.nom_plat}</div>
                                <div class="ing-stock" style="font-size: 1.2rem; color: var(--ocean);">$${plat.precio_plat}</div>
                                ${recetaHtml}
                            </div>
                        `;
                    });
                } else {
                    gridPlat.innerHTML = '<p>No hay platillos registrados.</p>';
                }
            } catch(e) { console.error(e); }
        }

        // Eliminar Platillo
        async function deletePlatillo(cod_plat) {
            if(!confirm('¿Estás seguro de que deseas eliminar este platillo? Esta acción no se puede deshacer.')) return;
            
            const fd = new FormData();
            fd.append('cod_plat', cod_plat);
            
            try {
                const res = await fetch('api/delete_platillo.php', { method: 'POST', body: fd });
                const data = await res.json();
                alert(data.message);
                if(data.success) {
                    fetchPlatillos(); // Recargar la lista
                }
            } catch(err) {
                alert('Error de conexión al intentar eliminar.');
            }
        }

        // Guardar Ingrediente
        document.getElementById('form-ingrediente').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = e.target.querySelector('button');
            btn.textContent = 'Guardando...';
            btn.disabled = true;

            const fd = new FormData();
            fd.append('desc_ingre', document.getElementById('ing_nombre').value);
            fd.append('tipo_ingre', document.getElementById('ing_tipo').value);
            fd.append('stock_actual', document.getElementById('ing_stock').value);

            try {
                const res = await fetch('api/add_ingrediente.php', { method: 'POST', body: fd });
                const data = await res.json();
                alert(data.message);
                if(data.success) {
                    e.target.reset();
                    fetchIngredientes();
                }
            } catch(err) { alert('Error de conexión'); }
            btn.textContent = 'Guardar Ingrediente';
            btn.disabled = false;
        });

        // Crear Platillo y Receta
        document.getElementById('form-platillo').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            // Recolectar ingredientes seleccionados
            const seleccionados = [];
            document.querySelectorAll('.ing-chk:checked').forEach(chk => {
                const qty = document.getElementById('qty_' + chk.value).value;
                if(qty && parseFloat(qty) > 0) {
                    seleccionados.push({
                        cod_ingre: chk.value,
                        cant_ingre: parseFloat(qty)
                    });
                }
            });

            if(seleccionados.length === 0) {
                alert('Debes seleccionar al menos un ingrediente y especificar su cantidad para la receta.');
                return;
            }

            const btn = e.target.querySelector('button');
            btn.textContent = 'Creando...';
            btn.disabled = true;

            const fd = new FormData();
            fd.append('nom_plat', document.getElementById('plat_nombre').value);
            fd.append('desc_plat', document.getElementById('plat_desc').value);
            fd.append('tipo_plat', document.getElementById('plat_tipo').value);
            fd.append('precio_plat', document.getElementById('plat_precio').value);
            fd.append('ingredientes', JSON.stringify(seleccionados));

            try {
                const res = await fetch('api/add_platillo.php', { method: 'POST', body: fd });
                const data = await res.json();
                alert(data.message);
                if(data.success) {
                    e.target.reset();
                    fetchPlatillos();
                    // Resetear lista de ingredientes desactivando inputs
                    document.querySelectorAll('.ing-chk').forEach(chk => {
                        document.getElementById('qty_' + chk.value).disabled = true;
                    });
                }
            } catch(err) { alert('Error de conexión'); console.error(err); }
            
            btn.textContent = 'Crear Platillo y Receta';
            btn.disabled = false;
        });

        // Añadir Mesa
        document.getElementById('form-mesa')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = e.target.querySelector('.btn-submit');
            btn.textContent = 'Añadiendo...';
            btn.disabled = true;

            const fd = new FormData();
            fd.append('num_mesa', document.getElementById('mesa_num').value);
            fd.append('cap_mesa', document.getElementById('mesa_cap').value);
            fd.append('zona_mesa', document.getElementById('mesa_zona').value);

            try {
                const res = await fetch('api/add_mesa.php', { method: 'POST', body: fd });
                const data = await res.json();
                alert(data.message);
                if(data.success) {
                    e.target.reset();
                }
            } catch(err) { alert('Error de conexión'); }
            
            btn.textContent = 'Añadir Mesa';
            btn.disabled = false;
        });

        function openEditModal(cod, nombre, stock, tipo) {
            document.getElementById('edit_cod').value = cod;
            document.getElementById('edit_nombre').value = nombre;
            document.getElementById('edit_stock').value = stock;
            
            const tipoSelect = document.getElementById('edit_tipo');
            if ([...tipoSelect.options].some(opt => opt.value === tipo)) {
                tipoSelect.value = tipo;
            } else {
                tipoSelect.value = 'Unidades'; // Fallback
            }
            
            document.getElementById('editModal').style.display = 'flex';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        // Handle edit form submit
        document.getElementById('form-edit-ing').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = e.target.querySelector('.btn-save');
            btn.textContent = 'Guardando...';
            btn.disabled = true;

            const fd = new FormData();
            fd.append('cod_ingre', document.getElementById('edit_cod').value);
            fd.append('desc_ingre', document.getElementById('edit_nombre').value);
            fd.append('tipo_ingre', document.getElementById('edit_tipo').value);
            fd.append('stock_actual', document.getElementById('edit_stock').value);

            try {
                const res = await fetch('api/update_ingrediente.php', { method: 'POST', body: fd });
                const data = await res.json();
                if(data.success) {
                    closeEditModal();
                    fetchIngredientes();
                } else {
                    alert(data.message);
                }
            } catch(err) { alert('Error de conexión'); }
            
            btn.textContent = 'Guardar';
            btn.disabled = false;
        });

        // Eliminar Ingrediente
        async function deleteIngrediente() {
            const cod = document.getElementById('edit_cod').value;
            if(!cod) return;
            
            if(!confirm('¿Estás seguro de que deseas eliminar este ingrediente? Esta acción no se puede deshacer.')) return;
            
            const btn = document.getElementById('btn-delete-ing');
            btn.textContent = 'Eliminando...';
            btn.disabled = true;

            const fd = new FormData();
            fd.append('cod_ingre', cod);
            
            try {
                const res = await fetch('api/delete_ingrediente.php', { method: 'POST', body: fd });
                const data = await res.json();
                alert(data.message);
                if(data.success) {
                    closeEditModal();
                    fetchIngredientes();
                }
            } catch(err) {
                alert('Error de conexión');
            }
            
            btn.textContent = 'Eliminar';
            btn.disabled = false;
        }

        // =======================
        // NUEVAS APIS ADMIN
        // =======================
        async function fetchFacturasAdmin() {
            try {
                const res = await fetch('api/get_facturas_admin.php');
                const data = await res.json();
                const container = document.getElementById('grid-facturas');
                
                if (data.success && data.facturas.length > 0) {
                    container.innerHTML = '';
                    data.facturas.forEach(f => {
                        let itemsHtml = '';
                        if (f.detalles) {
                            f.detalles.forEach(d => {
                                itemsHtml += `<li><span>${d.cantidad}x ${d.nom_plat}</span> <span>$${d.subtotal}</span></li>`;
                            });
                        }
                        
                        container.innerHTML += `
                            <div style="background:var(--card-bg); border:1px solid rgba(0,0,0,0.1); border-radius:15px; padding:20px; box-shadow:0 4px 6px rgba(0,0,0,0.05); border-left:5px solid var(--ocean);">
                                <div style="display:flex; justify-content:space-between; margin-bottom:10px;">
                                    <strong>🧾 Factura Mesa ${f.num_mesa || '-'}</strong>
                                    <span style="color:#666;">${f.dia_fact}/${f.mes_fact}/${f.año_fact} ${f.hora_fact}</span>
                                </div>
                                <p style="margin:5px 0;"><strong>Cliente:</strong> ${f.nombre_cliente || 'General'}</p>
                                <p style="margin:5px 0; color:#666;"><strong>Mesero:</strong> ${f.nombre_mesero || 'No asig.'}</p>
                                <ul style="list-style:none; padding:10px 0; border-top:1px dashed #ccc; border-bottom:1px dashed #ccc; margin:10px 0;">
                                    ${itemsHtml}
                                </ul>
                                <div style="display:flex; justify-content:space-between; font-size:1.1rem;">
                                    <span style="color:var(--text-muted);">Pago: ${f.metodo_pago}</span>
                                    <strong style="color:var(--ocean);">Total: $${f.total}</strong>
                                </div>
                            </div>
                        `;
                    });
                } else {
                    container.innerHTML = '<p>No hay facturas registradas.</p>';
                }
            } catch (e) { console.error(e); }
        }

        async function fetchClientes() {
            try {
                const res = await fetch('api/get_clientes.php');
                const data = await res.json();
                const container = document.getElementById('grid-clientes');
                
                if (data.success && data.clientes.length > 0) {
                    container.innerHTML = '';
                    data.clientes.forEach(c => {
                        const cuentaStr = c.username ? `<span style="color:#27ae60; font-size:0.8rem;">✅ Con Cuenta</span>` : `<span style="color:#e74c3c; font-size:0.8rem;">❌ Sin Cuenta</span>`;
                        container.innerHTML += `
                            <div class="ing-card" style="text-align:left;">
                                <h4 style="color:var(--ocean); margin-bottom:5px;">${c.nom_cli} ${c.ap_cli}</h4>
                                <p style="margin:0; font-size:0.9rem; color:#555;"><strong>C.I:</strong> ${c.cedula_cli}</p>
                                <p style="margin:0; font-size:0.9rem; color:#555;"><strong>Tlf:</strong> ${c.tlf_cli || 'N/A'}</p>
                                <div style="margin-top:10px;">${cuentaStr}</div>
                            </div>
                        `;
                    });
                } else {
                    container.innerHTML = '<p>No hay clientes registrados.</p>';
                }
            } catch (e) { console.error(e); }
        }

        async function fetchEmpleados() {
            try {
                const res = await fetch('api/get_empleados.php');
                const data = await res.json();
                const container = document.getElementById('grid-empleados');
                
                if (data.success && data.empleados.length > 0) {
                    container.innerHTML = '';
                    data.empleados.forEach(e => {
                        const badgeColor = e.cargo_emp === 'Administrador' ? '#e74c3c' : (e.cargo_emp === 'Mesero' ? '#f39c12' : '#3498db');
                        container.innerHTML += `
                            <div class="ing-card" style="text-align:left; border-top: 4px solid ${badgeColor};">
                                <h4 style="color:var(--ocean); margin-bottom:5px;">${e.nom_emp} ${e.ap_emp}</h4>
                                <span style="background:${badgeColor}; color:white; padding:2px 8px; border-radius:10px; font-size:0.8rem;">${e.cargo_emp}</span>
                                <p style="margin:5px 0 0 0; font-size:0.9rem; color:#555;"><strong>Carnet:</strong> ${e.carnet_emp}</p>
                                <p style="margin:0; font-size:0.9rem; color:#555;"><strong>Ingreso:</strong> ${e.dia_ing}/${e.año_ing}</p>
                            </div>
                        `;
                    });
                } else {
                    container.innerHTML = '<p>No hay empleados registrados.</p>';
                }
            } catch (e) { console.error(e); }
        }

        function filterInventario() {
            let input = document.getElementById('search-inventario').value.toLowerCase();
            let cards = document.querySelectorAll('#grid-inventario .ing-card');
            cards.forEach(card => {
                let name = card.querySelector('.ing-name').innerText.toLowerCase();
                card.style.display = name.includes(input) ? '' : 'none';
            });
        }

        function filterIngredientes() {
            let input = document.getElementById('search-ingredientes').value.toLowerCase();
            let items = document.querySelectorAll('#ingredient-list .ing-item');
            items.forEach(item => {
                let label = item.querySelector('label').innerText.toLowerCase();
                item.style.display = label.includes(input) ? 'flex' : 'none';
            });
        }

        // ===========================
        // NOTIFICACIONES INVENTARIO
        // ===========================
        async function checkNotificaciones() {
            try {
                const res = await fetch('api/get_alertas_inventario.php');
                const data = await res.json();
                const container = document.getElementById('notificaciones-container');
                container.innerHTML = '';

                if (data.success && data.alertas.length > 0) {
                    data.alertas.forEach(alerta => {
                        const div = document.createElement('div');
                        div.style.cssText = `
                            background: rgba(231, 76, 60, 0.95);
                            color: white;
                            padding: 15px 20px;
                            border-radius: 10px;
                            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
                            display: flex;
                            align-items: center;
                            justify-content: space-between;
                            gap: 20px;
                            animation: slideIn 0.3s ease-out;
                        `;
                        div.innerHTML = `
                            <div>
                                <strong>⚠️ Alerta de Reabastecimiento</strong><br>
                                <span style="font-size:0.9rem; line-height: 1.4;">
                                    El ingrediente <strong>${alerta.desc_ingre}</strong> está crítico (Queda: ${alerta.stock_actual}).<br>
                                    <strong>Acción Requerida:</strong> Debes ingresar <strong>${(alerta.stock_maximo - alerta.stock_actual).toFixed(2)}</strong> para llegar a su capacidad.
                                </span>
                            </div>
                            <button onclick="dismissAlerta('${alerta.cod_ingre}')" style="background: white; color: #e74c3c; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; font-weight: bold; transition: all 0.2s;">
                                Aceptar
                            </button>
                        `;
                        container.appendChild(div);
                    });
                }
            } catch (e) {
                console.error("Error obteniendo alertas", e);
            }
        }

        async function dismissAlerta(cod_ingre) {
            try {
                const formData = new FormData();
                formData.append('cod_ingre', cod_ingre);
                const res = await fetch('api/marcar_alerta.php', { method: 'POST', body: formData });
                const data = await res.json();
                if (data.success) {
                    checkNotificaciones();
                }
            } catch (e) {
                console.error(e);
            }
        }

        function logout() { window.location.href = 'logout.php'; }

        // Inicializar
        fetchIngredientes();
        fetchPlatillos();
        checkNotificaciones();
        setInterval(checkNotificaciones, 10000); // Revisar cada 10 seg
    </script>
    <div id="notificaciones-container" style="position: fixed; bottom: 20px; right: 20px; display: flex; flex-direction: column; gap: 10px; z-index: 9999;"></div>
    <style>
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    </style>
</body>
</html>
