-- phpMyAdmin SQL Dump corrigido

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS devodoro;
USE devodoro;

-- --------------------------------------------------------
-- TABELA EMPRESA
-- --------------------------------------------------------

DROP TABLE IF EXISTS `empresa`;

CREATE TABLE `empresa` (
  `id_empresa` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `foto_perfil` varchar(255) DEFAULT NULL,
  `data_cadastro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `codigo_acesso` varchar(20) DEFAULT NULL,

  PRIMARY KEY (`id_empresa`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `codigo_acesso` (`codigo_acesso`)

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci;

INSERT INTO `empresa`
(`id_empresa`, `nome`, `email`, `senha`, `data_cadastro`, `codigo_acesso`)
VALUES
(1, 'João Tech', 'joao.tech@gmail.com', '$2y$10$OIxVzlMNmcT2gbELooztjOdtSFHbeu/SnGbfVefeKs6ytWQ.N1zaG', '2026-04-29 14:01:18', 'f81f9b4f'),
(2, 'Pernetinha tech', 'deyvison@gmail.com', '$2y$10$kju4AX50SgLD49w16nXuVeYV4lfWGC2oKpMQ463vv51fdfXakfbQC', '2026-04-29 14:05:06', '5a197b04');

-- --------------------------------------------------------
-- TABELA CATEGORIA
-- --------------------------------------------------------

DROP TABLE IF EXISTS `categoria`;

CREATE TABLE `categoria` (
  `id_categoria` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `id_empresa` int NOT NULL,

  PRIMARY KEY (`id_categoria`),
  KEY `id_empresa` (`id_empresa`),

  CONSTRAINT `fk_categoria_empresa`
    FOREIGN KEY (`id_empresa`)
    REFERENCES `empresa` (`id_empresa`)
    ON DELETE CASCADE

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- TABELA EQUIPE
-- --------------------------------------------------------

DROP TABLE IF EXISTS `equipe`;

CREATE TABLE `equipe` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `id_empresa` int NOT NULL,

  PRIMARY KEY (`id`),
  KEY `id_empresa` (`id_empresa`),

  CONSTRAINT `fk_equipe_empresa`
    FOREIGN KEY (`id_empresa`)
    REFERENCES `empresa` (`id_empresa`)
    ON DELETE CASCADE

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci;

INSERT INTO `equipe`
(`id`, `nome`, `id_empresa`)
VALUES
(1, 'RH', 1),
(2, 'Financeiro', 1);

-- --------------------------------------------------------
-- TABELA FUNCIONARIO
-- --------------------------------------------------------

DROP TABLE IF EXISTS `funcionario`;

CREATE TABLE `funcionario` (
  `id_funcionario` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `foto_perfil` varchar(255) DEFAULT NULL,
  `tipo_usuario` varchar(20) DEFAULT 'FUNCIONARIO',
  `ativo` tinyint(1) DEFAULT 1,
  `id_empresa` int DEFAULT NULL,

  PRIMARY KEY (`id_funcionario`),
  UNIQUE KEY `email` (`email`),
  KEY `id_empresa` (`id_empresa`),

  CONSTRAINT `fk_funcionario_empresa`
    FOREIGN KEY (`id_empresa`)
    REFERENCES `empresa` (`id_empresa`)
    ON DELETE SET NULL

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci;

INSERT INTO `funcionario`
(`id_funcionario`, `nome`, `email`, `senha`, `tipo_usuario`, `ativo`, `id_empresa`)
VALUES
(1, 'Lucas Brasil', 'lucasbr@gmail.com', '$2y$10$PfeTF3ORREk90i9gyJ561eFKDvSlxGePVMTcLuJNHNmgG0LoIX7ca', 'FUNCIONARIO', 1, 1);

-- --------------------------------------------------------
-- TABELA EQUIPE_FUNCIONARIO
-- --------------------------------------------------------

DROP TABLE IF EXISTS `equipe_funcionario`;

CREATE TABLE `equipe_funcionario` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_equipe` int NOT NULL,
  `id_funcionario` int NOT NULL,

  PRIMARY KEY (`id`),

  UNIQUE KEY `unique_equipe_funcionario`
  (`id_equipe`, `id_funcionario`),

  KEY `id_funcionario` (`id_funcionario`),

  CONSTRAINT `fk_eqfunc_equipe`
    FOREIGN KEY (`id_equipe`)
    REFERENCES `equipe` (`id`)
    ON DELETE CASCADE,

  CONSTRAINT `fk_eqfunc_funcionario`
    FOREIGN KEY (`id_funcionario`)
    REFERENCES `funcionario` (`id_funcionario`)
    ON DELETE CASCADE

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- TABELA TAREFAS
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tarefas`;

CREATE TABLE `tarefas` (
  `id_tarefa` int NOT NULL AUTO_INCREMENT,
  `titulo` varchar(200) NOT NULL,
  `prioridade` varchar(20) DEFAULT 'MEDIA',
  `estado` varchar(20) DEFAULT 'PENDENTE',
  `prazo_entrega` DATE NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_empresa` int NOT NULL,

  PRIMARY KEY (`id_tarefa`),

  KEY `id_empresa` (`id_empresa`),

  CONSTRAINT `fk_tarefa_empresa`
    FOREIGN KEY (`id_empresa`)
    REFERENCES `empresa` (`id_empresa`)
    ON DELETE CASCADE

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci;

INSERT INTO `tarefas`
(`id_tarefa`, `titulo`, `prioridade`, `estado`, `created_at`, `id_empresa`)
VALUES
(1, 'tarefa', 'MEDIA', 'PENDENTE', '2026-04-29 14:56:05', 2),
(3, 'gswdgsd', 'MEDIA', 'EM_ANDAMENTO', '2026-04-30 11:00:04', 1);

-- --------------------------------------------------------
-- TABELA TAREFA_CATEGORIA
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tarefa_categoria`;

CREATE TABLE `tarefa_categoria` (
  `id_tarefa` int NOT NULL,
  `id_categoria` int NOT NULL,

  PRIMARY KEY (`id_tarefa`, `id_categoria`),

  KEY `id_categoria` (`id_categoria`),

  CONSTRAINT `fk_tarefa_categoria_tarefa`
    FOREIGN KEY (`id_tarefa`)
    REFERENCES `tarefas` (`id_tarefa`)
    ON DELETE CASCADE,

  CONSTRAINT `fk_tarefa_categoria_categoria`
    FOREIGN KEY (`id_categoria`)
    REFERENCES `categoria` (`id_categoria`)
    ON DELETE CASCADE

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- TABELA TAREFA_EQUIPE
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tarefa_equipe`;

CREATE TABLE `tarefa_equipe` (
  `id_tarefa` int NOT NULL,
  `id_equipe` int NOT NULL,

  PRIMARY KEY (`id_tarefa`, `id_equipe`),

  KEY `id_equipe` (`id_equipe`),

  CONSTRAINT `fk_tarefa_equipe_tarefa`
    FOREIGN KEY (`id_tarefa`)
    REFERENCES `tarefas` (`id_tarefa`)
    ON DELETE CASCADE,

  CONSTRAINT `fk_tarefa_equipe_equipe`
    FOREIGN KEY (`id_equipe`)
    REFERENCES `equipe` (`id`)
    ON DELETE CASCADE

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- TABELA TAREFA_FUNCIONARIO
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tarefa_funcionario`;

CREATE TABLE `tarefa_funcionario` (
  `id_tarefa` int NOT NULL,
  `id_funcionario` int NOT NULL,

  PRIMARY KEY (`id_tarefa`, `id_funcionario`),

  KEY `id_funcionario` (`id_funcionario`),

  CONSTRAINT `fk_tarefa_funcionario_tarefa`
    FOREIGN KEY (`id_tarefa`)
    REFERENCES `tarefas` (`id_tarefa`)
    ON DELETE CASCADE,

  CONSTRAINT `fk_tarefa_funcionario_funcionario`
    FOREIGN KEY (`id_funcionario`)
    REFERENCES `funcionario` (`id_funcionario`)
    ON DELETE CASCADE

) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci;

COMMIT;