-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Tempo de geração: 29/04/2026 às 16:07
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

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

CREATE TABLE `categoria` (
  `id_categoria` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `id_empresa` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `empresa`
--

CREATE TABLE `empresa` (
  `id_empresa` int(11) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `data_cadastro` timestamp NOT NULL DEFAULT current_timestamp(),
  `codigo_acesso` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `empresa`
--

INSERT INTO `empresa` (`id_empresa`, `nome`, `email`, `senha`, `data_cadastro`, `codigo_acesso`) VALUES
(1, 'João Tech', 'joao.tech@gmail.com', '$2y$10$OIxVzlMNmcT2gbELooztjOdtSFHbeu/SnGbfVefeKs6ytWQ.N1zaG', '2026-04-29 14:01:18', 'f81f9b4f'),
(2, 'Pernetinha tech', 'deyvison@gmail.com', '$2y$10$kju4AX50SgLD49w16nXuVeYV4lfWGC2oKpMQ463vv51fdfXakfbQC', '2026-04-29 14:05:06', '5a197b04');

-- --------------------------------------------------------

--
-- Estrutura para tabela `funcionario`
--

CREATE TABLE `funcionario` (
  `id_funcionario` int(11) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `cpf` varchar(14) DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `tipo_usuario` varchar(20) DEFAULT 'FUNCIONARIO',
  `ativo` tinyint(1) DEFAULT 1,
  `id_empresa` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `funcionario`
--

INSERT INTO `funcionario` (`id_funcionario`, `nome`, `cpf`, `email`, `senha`, `tipo_usuario`, `ativo`, `id_empresa`) VALUES
(1, 'Lucas Brasil', NULL, 'lucasbr@gmail.com', '$2y$10$PfeTF3ORREk90i9gyJ561eFKDvSlxGePVMTcLuJNHNmgG0LoIX7ca', 'FUNCIONARIO', 1, 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `tarefas`
--

CREATE TABLE `tarefas` (
  `id_tarefa` int(11) NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `prioridade` varchar(20) DEFAULT 'MEDIA',
  `status` varchar(20) DEFAULT 'PENDENTE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tarefa_categoria`
--

CREATE TABLE `tarefa_categoria` (
  `id_tarefa` int(11) NOT NULL,
  `id_categoria` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tarefa_funcionario`
--

CREATE TABLE `tarefa_funcionario` (
  `id_tarefa` int(11) NOT NULL,
  `id_funcionario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`id_categoria`),
  ADD KEY `id_empresa` (`id_empresa`);

--
-- Índices de tabela `empresa`
--
ALTER TABLE `empresa`
  ADD PRIMARY KEY (`id_empresa`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `codigo_acesso` (`codigo_acesso`);

--
-- Índices de tabela `funcionario`
--
ALTER TABLE `funcionario`
  ADD PRIMARY KEY (`id_funcionario`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `id_empresa` (`id_empresa`);

--
-- Índices de tabela `tarefas`
--
ALTER TABLE `tarefas`
  ADD PRIMARY KEY (`id_tarefa`);

--
-- Índices de tabela `tarefa_categoria`
--
ALTER TABLE `tarefa_categoria`
  ADD PRIMARY KEY (`id_tarefa`,`id_categoria`),
  ADD KEY `id_categoria` (`id_categoria`);

--
-- Índices de tabela `tarefa_funcionario`
--
ALTER TABLE `tarefa_funcionario`
  ADD PRIMARY KEY (`id_tarefa`,`id_funcionario`),
  ADD KEY `id_funcionario` (`id_funcionario`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `categoria`
--
ALTER TABLE `categoria`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `empresa`
--
ALTER TABLE `empresa`
  MODIFY `id_empresa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `funcionario`
--
ALTER TABLE `funcionario`
  MODIFY `id_funcionario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `tarefas`
--
ALTER TABLE `tarefas`
  MODIFY `id_tarefa` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `categoria`
--
ALTER TABLE `categoria`
  ADD CONSTRAINT `categoria_ibfk_1` FOREIGN KEY (`id_empresa`) REFERENCES `empresa` (`id_empresa`);

--
-- Restrições para tabelas `funcionario`
--
ALTER TABLE `funcionario`
  ADD CONSTRAINT `funcionario_ibfk_1` FOREIGN KEY (`id_empresa`) REFERENCES `empresa` (`id_empresa`);

--
-- Restrições para tabelas `tarefa_categoria`
--
ALTER TABLE `tarefa_categoria`
  ADD CONSTRAINT `tarefa_categoria_ibfk_1` FOREIGN KEY (`id_tarefa`) REFERENCES `tarefas` (`id_tarefa`) ON DELETE CASCADE,
  ADD CONSTRAINT `tarefa_categoria_ibfk_2` FOREIGN KEY (`id_categoria`) REFERENCES `categoria` (`id_categoria`) ON DELETE CASCADE;

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
