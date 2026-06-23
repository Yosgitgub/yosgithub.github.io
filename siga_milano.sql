CREATE DATABASE IF NOT EXISTS siga_milano;
USE siga_milano;

-- =========================================================================
-- 1. TABLAS BASE Y CONFIGURACIÓN GENERAL
-- =========================================================================

CREATE TABLE restaurante (
    rif_rest VARCHAR(20) PRIMARY KEY,
    nom_rest VARCHAR(100) NOT NULL,
    Zona_rest VARCHAR(100),
    calle_rest VARCHAR(100),
    estruc_rest VARCHAR(100),
    logo_rest VARCHAR(255),
    tlf_rest VARCHAR(20),
    correo_rest VARCHAR(100)
);

CREATE TABLE turno (
    cod_turno VARCHAR(10) PRIMARY KEY,
    desc_turno VARCHAR(50) NOT NULL
);

CREATE TABLE horario (
    cod_horario VARCHAR(10) PRIMARY KEY,
    h_entrada TIME NOT NULL,
    h_salida TIME NOT NULL
);

-- =========================================================================
-- 2. MODULO DE EMPLEADOS Y CONTROL DE ACCESO (Inicio de Sesión)
-- =========================================================================

-- Tabla Empleado con la restricción de roles incluida
CREATE TABLE empleado (
    cedula_emp VARCHAR(20) PRIMARY KEY,
    nom_emp VARCHAR(50) NOT NULL,
    ap_emp VARCHAR(50) NOT NULL,
    tlf_emp VARCHAR(20),
    correo_emp VARCHAR(100),
    Zona_emp VARCHAR(100),
    calle_emp VARCHAR(100),
    casa_emp VARCHAR(50),
    dia_ing DATE,
    hora_ing TIME,
    año_ing INT,
    carnet_emp VARCHAR(20) UNIQUE NOT NULL, 
    salario_emp DECIMAL(10,2),
    cargo_emp VARCHAR(50),
    cod_horario VARCHAR(10),
    cod_turno VARCHAR(10),
    rif_rest VARCHAR(20),
    FOREIGN KEY (cod_horario) REFERENCES horario(cod_horario),
    FOREIGN KEY (cod_turno) REFERENCES turno(cod_turno),
    FOREIGN KEY (rif_rest) REFERENCES restaurante(rif_rest),
    
    -- AQUÍ QUEDA PERFECTO: Restricción para que solo acepte estos tres cargos
    CONSTRAINT chk_cargo_permitido 
    CHECK (cargo_emp IN ('Mesero', 'Chef', 'Administrador'))
);

-- Tabla de Usuarios que se conecta con tu formulario HTML de "Crear Cuenta"
CREATE TABLE usuario_sistema (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL, 
    carnet_emp VARCHAR(20) NOT NULL,    
    estado_usuario INT DEFAULT 1,        
    FOREIGN KEY (carnet_emp) REFERENCES empleado(carnet_emp) ON DELETE CASCADE
);

-- =========================================================================
-- 3. MÓDULO DE CLIENTES Y MESAS
-- =========================================================================

CREATE TABLE cliente (
    cedula_cli VARCHAR(20) PRIMARY KEY,
    nom_cli VARCHAR(50) NOT NULL,
    ap_cli VARCHAR(50) NOT NULL,
    calle_cli VARCHAR(100),
    casa_cli VARCHAR(50),
    zona_cli VARCHAR(100),
    tlf_cli VARCHAR(20),
    correo_cli VARCHAR(100)
);

-- Tabla de Usuarios para Clientes que se conecta con el formulario HTML
CREATE TABLE usuario_cliente (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL, 
    cedula_cli VARCHAR(20) NOT NULL,    
    estado_usuario INT DEFAULT 1,        
    FOREIGN KEY (cedula_cli) REFERENCES cliente(cedula_cli) ON DELETE CASCADE
);

CREATE TABLE mesa (
    num_mesa INT PRIMARY KEY,
    cap_mesa INT NOT NULL,
    Pos_mesa VARCHAR(50),      
    zona_mesa VARCHAR(50),
    clase_mesa VARCHAR(50),
    estado_mesa VARCHAR(20) DEFAULT 'Disponible' 
);

CREATE TABLE reserva (
    id_reserva INT AUTO_INCREMENT PRIMARY KEY,
    cedula_cli VARCHAR(20) NOT NULL,
    num_mesa INT NOT NULL,
    fecha_reserva DATE NOT NULL,
    hora_reserva TIME NOT NULL,
    cant_personas INT DEFAULT 1,
    estado_reserva VARCHAR(20) DEFAULT 'Activa', -- Activa, Cancelada, Completada
    FOREIGN KEY (cedula_cli) REFERENCES cliente(cedula_cli),
    FOREIGN KEY (num_mesa) REFERENCES mesa(num_mesa)
);


CREATE TABLE provedoor (
    rif_prov VARCHAR(20) PRIMARY KEY,
    nom_prov VARCHAR(100) NOT NULL,
    logo_prov VARCHAR(255),
    zona_prov VARCHAR(100),
    calle_prov VARCHAR(100),
    estruct_prov VARCHAR(100),
    tlf_prov VARCHAR(20),
    correo_prov VARCHAR(100)
);

CREATE TABLE ingredientes (
    cod_ingre VARCHAR(10) PRIMARY KEY,
    tipo_ingre VARCHAR(50),
    desc_ingre VARCHAR(100) NOT NULL,
    stock_actual DECIMAL(10,2) DEFAULT 0.00, -- Control de stock en tiempo real
    stock_minimo DECIMAL(10,2) DEFAULT 5.00, -- Para alertas de stock crítico
    stock_maximo DECIMAL(10,2) DEFAULT 100.00,
    alerta_vista TINYINT(1) DEFAULT 0,
    dia_f_ing DATE,
    mes_f_ingre VARCHAR(20),
    hora_f_ingre TIME,
    dia_v_ingre DATE,
    mes_v_ingre VARCHAR(20),
    año_v_ing INT
);



CREATE TABLE receta (
    cod_receta VARCHAR(10) PRIMARY KEY,
    desc_receta VARCHAR(255)
);

CREATE TABLE platillo (
    cod_plat VARCHAR(10) PRIMARY KEY,
    nom_plat VARCHAR(100) NOT NULL,
    desc_plat TEXT,
    precio_plat DECIMAL(10,2) NOT NULL,
    tipo_plat VARCHAR(50), -- Ej: Entrada, Sopa, Plato Fuerte, Bebida
    img_platillo VARCHAR(255),
    tiempo_preparacion INT DEFAULT 15,
    cant_plat INT DEFAULT 0,
    cod_receta VARCHAR(10),
    FOREIGN KEY (cod_receta) REFERENCES receta(cod_receta)
);

-- Relación muchos a muchos entre Recetas e Ingredientes (Matriz de Insumos)
CREATE TABLE detalle_receta (
    cod_receta VARCHAR(10),
    cod_ingre VARCHAR(10),
    cod_plat VARCHAR(10),
    rif_prov VARCHAR(20),
    cant_ingre DECIMAL(10,2) NOT NULL, -- Cantidad exacta que consume el plato
    PRIMARY KEY (cod_receta, cod_ingre),
    FOREIGN KEY (cod_receta) REFERENCES receta(cod_receta),
    FOREIGN KEY (cod_ingre) REFERENCES ingredientes(cod_ingre),
    FOREIGN KEY (cod_plat) REFERENCES platillo(cod_plat),
    FOREIGN KEY (rif_prov) REFERENCES provedoor(rif_prov)
);


CREATE TABLE pedido (
    id_pedido INT AUTO_INCREMENT PRIMARY KEY,
    num_mesa INT,
    estado VARCHAR(20) DEFAULT 'Pendiente', -- Pendiente, Listo, Entregado
    fecha_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
    hora_entrega DATETIME,
    cedula_cli VARCHAR(20),
    cedula_emp VARCHAR(20),
    FOREIGN KEY (num_mesa) REFERENCES mesa(num_mesa),
    FOREIGN KEY (cedula_cli) REFERENCES cliente(cedula_cli),
    FOREIGN KEY (cedula_emp) REFERENCES empleado(cedula_emp)
);

CREATE TABLE detalle_pedido (
    id_detalle INT AUTO_INCREMENT PRIMARY KEY,
    id_pedido INT NOT NULL,
    cod_plat VARCHAR(10) NOT NULL,
    cantidad INT DEFAULT 1,
    notas TEXT,
    FOREIGN KEY (id_pedido) REFERENCES pedido(id_pedido) ON DELETE CASCADE,
    FOREIGN KEY (cod_plat) REFERENCES platillo(cod_plat)
);

-- =========================================================================
-- 7. MÓDULO DE FACTURACIÓN
-- =========================================================================

CREATE TABLE factura (
    cedula_emp VARCHAR(20),
    dia_fact INT,
    mes_fact INT,
    año_fact INT,
    hora_fact TIME,
    cedula_cli VARCHAR(20),
    rif_rest VARCHAR(20),
    descuento DECIMAL(10,2) DEFAULT 0.00,
    iva DECIMAL(10,2) NOT NULL,
    precio_fact DECIMAL(10,2) NOT NULL, 
    costo_fact DECIMAL(10,2),
    total DECIMAL(10,2) NOT NULL,       
    metodo_pago VARCHAR(50),
    id_pedido INT,
    PRIMARY KEY (cedula_emp, dia_fact, mes_fact, año_fact, hora_fact), -- Clave primaria compuesta
    FOREIGN KEY (cedula_emp) REFERENCES empleado(cedula_emp),
    FOREIGN KEY (cedula_cli) REFERENCES cliente(cedula_cli),
    FOREIGN KEY (rif_rest) REFERENCES restaurante(rif_rest),
    FOREIGN KEY (id_pedido) REFERENCES pedido(id_pedido)
);

CREATE TABLE detalle_fact (
    cedula_emp VARCHAR(20), -- Agregamos esto para que XAMPP pueda hacer el puente exacto
    dia_fact INT,
    mes_fact INT,
    año_fact INT,
    hora_fact TIME,
    num_mesa INT,
    cod_plat VARCHAR(10),
    subtotal DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (cedula_emp, dia_fact, mes_fact, año_fact, hora_fact, num_mesa, cod_plat),
    
   
    FOREIGN KEY (cedula_emp, dia_fact, mes_fact, año_fact, hora_fact) 
        REFERENCES factura(cedula_emp, dia_fact, mes_fact, año_fact, hora_fact) ON DELETE CASCADE,
        
    FOREIGN KEY (num_mesa) REFERENCES mesa(num_mesa),
    FOREIGN KEY (cod_plat) REFERENCES platillo(cod_plat)
);