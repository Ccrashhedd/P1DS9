DROP DATABASE IF EXISTS tienditaDB;
CREATE DATABASE tienditaDB;
USE tienditaDB;

CREATE TABLE IF NOT EXISTS Empleado (
    usuario VARCHAR(20) PRIMARY KEY,
    nombre VARCHAR(25) NULL,
    apellido VARCHAR(25) NULL,
    rol INT NOT NULL,
    contrasen VARCHAR(25) UNIQUE NOT NULL
);

CREATE TABLE IF NOT EXISTS CATEGORIA (
    idCategoria INT AUTO_INCREMENT PRIMARY KEY,
    nombreCat VARCHAR(25) NOT NULL
);

CREATE TABLE IF NOT EXISTS MARCA (
    idMarca INT AUTO_INCREMENT PRIMARY KEY,
    nombreMarc VARCHAR(25) NOT NULL
);

CREATE TABLE IF NOT EXISTS PRODUCTOS (
    idProducto INT PRIMARY KEY, -- El maximo de caracteres para el codigo de barras es 13
    nombre VARCHAR(50) NOT NULL,
    unidad VARCHAR(20) NOT NULL,
    descripcion VARCHAR(250) NOT NULL,
    stock INT NOT NULL,
    precioCosto DECIMAL(10,2) NOT NULL,
    precioVenta DECIMAL(10,2) NOT NULL,
    imagen VARCHAR(500),
    idCategoria INT NOT NULL,
    idMarca INT NOT NULL,
    CONSTRAINT fk_producto_categoria FOREIGN KEY (idCategoria) REFERENCES CATEGORIA(idCategoria),
    CONSTRAINT fk_producto_marca FOREIGN KEY (idMarca) REFERENCES MARCA(idMarca)
);

CREATE TABLE IF NOT EXISTS TARJETA (
    idTarjeta INT AUTO_INCREMENT PRIMARY KEY,
    digit6 VARCHAR(6) NOT NULL,
    digit4 VARCHAR(4) NOT NULL,
    fechaVence DATE NOT NULL,
    codSeguridad VARCHAR(4) NOT NULL,
    saldo DECIMAL(10,2) NOT NULL,
    saldoMaximo DECIMAL(10,2) NULL
);