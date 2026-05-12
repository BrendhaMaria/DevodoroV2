-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Tempo de geração: 07/05/2026 às 11:26
-- Versão do servidor: 8.0.43
-- Versão do PHP: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `devodoro`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `categoria`
--

DROP TABLE IF EXISTS `categoria`;
CREATE TABLE IF NOT EXISTS `categoria` (
  `id_categoria` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `id_empresa` int NOT NULL,
  PRIMARY KEY (`id_categoria`),
  KEY `id_empresa` (`id_empresa`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `empresa`
--

DROP TABLE IF EXISTS `empresa`;
CREATE TABLE IF NOT EXISTS `empresa` (
  `id_empresa` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `senha` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `data_cadastro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `codigo_acesso` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id_empresa`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `codigo_acesso` (`codigo_acesso`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `empresa`
--

INSERT INTO `empresa` (`id_empresa`, `nome`, `email`, `senha`, `data_cadastro`, `codigo_acesso`) VALUES
(1, 'João Tech', 'joao.tech@gmail.com', '$2y$10$OIxVzlMNmcT2gbELooztjOdtSFHbeu/SnGbfVefeKs6ytWQ.N1zaG', '2026-04-29 14:01:18', 'f81f9b4f'),
(2, 'Pernetinha tech', 'deyvison@gmail.com', '$2y$10$kju4AX50SgLD49w16nXuVeYV4lfWGC2oKpMQ463vv51fdfXakfbQC', '2026-04-29 14:05:06', '5a197b04');

-- --------------------------------------------------------

--
-- Estrutura para tabela `equipe`
--

DROP TABLE IF EXISTS `equipe`;
CREATE TABLE IF NOT EXISTS `equipe` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `id_empresa` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_empresa` (`id_empresa`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `equipe`
--

INSERT INTO `equipe` (`id`, `nome`, `id_empresa`) VALUES
(1, 'RH', 1),
(2, 'Financeiro', 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `equipe_funcionario`
--

DROP TABLE IF EXISTS `equipe_funcionario`;
CREATE TABLE IF NOT EXISTS `equipe_funcionario` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_equipe` int NOT NULL,
  `id_funcionario` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_equipe_funcionario` (`id_equipe`,`id_funcionario`),
  KEY `id_funcionario` (`id_funcionario`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `funcionario`
--

DROP TABLE IF EXISTS `funcionario`;
CREATE TABLE IF NOT EXISTS `funcionario` (
  `id_funcionario` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `senha` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `tipo_usuario` varchar(20) COLLATE utf8mb4_general_ci DEFAULT 'FUNCIONARIO',
  `ativo` tinyint(1) DEFAULT '1',
  `id_empresa` int DEFAULT NULL,
  PRIMARY KEY (`id_funcionario`),
  UNIQUE KEY `email` (`email`),
  KEY `id_empresa` (`id_empresa`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `funcionario`
--

INSERT INTO `funcionario` (`id_funcionario`, `nome`, `email`, `senha`, `tipo_usuario`, `ativo`, `id_empresa`) VALUES
(1, 'Lucas Brasil', 'lucasbr@gmail.com', '$2y$10$PfeTF3ORREk90i9gyJ561eFKDvSlxGePVMTcLuJNHNmgG0LoIX7ca', 'FUNCIONARIO', 1, 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `tarefas`
--

CREATE TABLE `tarefas` (
  `id_tarefa` int(11) NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `prioridade` varchar(20) DEFAULT 'MEDIA',
  `estado` varchar(20) DEFAULT 'PENDENTE',
  `prazo_entrega` DATE NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_empresa` int(11) NOT NULL
  FOREIGN KEY (id_empresa) REFERENCES empresa(id_empresa);
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tarefas`
--

INSERT INTO `tarefas` (`id_tarefa`, `titulo`, `prioridade`, `estado`, `created_at`, `id_empresa`) VALUES
(1, 'tarefa', NULL, 'PENDENTE', '2026-04-29 14:56:05', 2),
(3, 'gswdgsd', NULL, 'EM_ANDAMENTO', '2026-04-30 11:00:04', 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `tarefa_categoria`
--

DROP TABLE IF EXISTS `tarefa_categoria`;
CREATE TABLE IF NOT EXISTS `tarefa_categoria` (
  `id_tarefa` int NOT NULL,
  `id_categoria` int NOT NULL,
  PRIMARY KEY (`id_tarefa`,`id_categoria`),
  KEY `id_categoria` (`id_categoria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tarefa_equipe`
--

DROP TABLE IF EXISTS `tarefa_equipe`;
CREATE TABLE IF NOT EXISTS `tarefa_equipe` (
  `id_tarefa` int NOT NULL,
  `id_equipe` int NOT NULL,
  PRIMARY KEY (`id_tarefa`,`id_equipe`),
  KEY `id_equipe` (`id_equipe`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tarefa_funcionario`
--

DROP TABLE IF EXISTS `tarefa_funcionario`;
CREATE TABLE IF NOT EXISTS `tarefa_funcionario` (
  `id_tarefa` int NOT NULL,
  `id_funcionario` int NOT NULL,
  PRIMARY KEY (`id_tarefa`,`id_funcionario`),
  KEY `id_funcionario` (`id_funcionario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `categoria`
--
ALTER TABLE `categoria`
  ADD CONSTRAINT `categoria_ibfk_1` FOREIGN KEY (`id_empresa`) REFERENCES `empresa` (`id_empresa`);

--
-- Restrições para tabelas `equipe`
--
ALTER TABLE `equipe`
  ADD CONSTRAINT `equipe_ibfk_1` FOREIGN KEY (`id_empresa`) REFERENCES `empresa` (`id_empresa`);

--
-- Restrições para tabelas `equipe_funcionario`
--
ALTER TABLE `equipe_funcionario`
  ADD CONSTRAINT `equipe_funcionario_ibfk_1` FOREIGN KEY (`id_equipe`) REFERENCES `equipe` (`id`),
  ADD CONSTRAINT `equipe_funcionario_ibfk_2` FOREIGN KEY (`id_funcionario`) REFERENCES `funcionario` (`id_funcionario`);

--
-- Restrições para tabelas `funcionario`
--
ALTER TABLE `funcionario`
  ADD CONSTRAINT `funcionario_ibfk_1` FOREIGN KEY (`id_empresa`) REFERENCES `empresa` (`id_empresa`);

--
-- Restrições para tabelas `tarefas`
--
ALTER TABLE `tarefas`
  ADD CONSTRAINT `fk_tarefa_empresa` FOREIGN KEY (`id_empresa`) REFERENCES `empresa` (`id_empresa`);

--
-- Restrições para tabelas `tarefa_categoria`
--
ALTER TABLE `tarefa_categoria`
  ADD CONSTRAINT `tarefa_categoria_ibfk_1` FOREIGN KEY (`id_tarefa`) REFERENCES `tarefas` (`id_tarefa`) ON DELETE CASCADE,
  ADD CONSTRAINT `tarefa_categoria_ibfk_2` FOREIGN KEY (`id_categoria`) REFERENCES `categoria` (`id_categoria`) ON DELETE CASCADE;

--
-- Restrições para tabelas `tarefa_equipe`
--
ALTER TABLE `tarefa_equipe`
  ADD CONSTRAINT `tarefa_equipe_ibfk_1` FOREIGN KEY (`id_tarefa`) REFERENCES `tarefas` (`id_tarefa`) ON DELETE CASCADE,
  ADD CONSTRAINT `tarefa_equipe_ibfk_2` FOREIGN KEY (`id_equipe`) REFERENCES `equipe` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `tarefa_funcionario`
--
ALTER TABLE `tarefa_funcionario`
  ADD CONSTRAINT `tarefa_funcionario_ibfk_1` FOREIGN KEY (`id_tarefa`) REFERENCES `tarefas` (`id_tarefa`) ON DELETE CASCADE,
  ADD CONSTRAINT `tarefa_funcionario_ibfk_2` FOREIGN KEY (`id_funcionario`) REFERENCES `funcionario` (`id_funcionario`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
