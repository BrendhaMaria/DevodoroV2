-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Tempo de geração: 22/04/2026 às 15:35
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
-- Estrutura para tabela `cargo`
--

CREATE TABLE `cargo` (
  `id_cargo` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `id_setor` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `cnpj` varchar(20) NOT NULL,
  `email` varchar(150) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `data_cadastro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `funcionario`
--

CREATE TABLE `funcionario` (
  `id_funcionario` int(11) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `cpf` varchar(14) NOT NULL,
  `email` varchar(150) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `id_setor` int(11) NOT NULL,
  `id_cargo` int(11) NOT NULL,
  `tipo_usuario` varchar(20) DEFAULT 'FUNCIONARIO',
  `ativo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `setor`
--

CREATE TABLE `setor` (
  `id_setor` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `id_empresa` int(11) NOT NULL,
  `id_gerente` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tarefa`
--

CREATE TABLE `tarefa` (
  `id_tarefa` int(11) NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `descricao` text NOT NULL,
  `prazo` date NOT NULL,
  `prioridade` varchar(20) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'PENDENTE',
  `id_setor` int(11) NOT NULL,
  `id_criador` int(11) NOT NULL
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
-- Índices de tabela `cargo`
--
ALTER TABLE `cargo`
  ADD PRIMARY KEY (`id_cargo`),
  ADD KEY `id_setor` (`id_setor`);

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
  ADD UNIQUE KEY `cnpj` (`cnpj`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Índices de tabela `funcionario`
--
ALTER TABLE `funcionario`
  ADD PRIMARY KEY (`id_funcionario`),
  ADD UNIQUE KEY `cpf` (`cpf`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `id_setor` (`id_setor`),
  ADD KEY `id_cargo` (`id_cargo`);

--
-- Índices de tabela `setor`
--
ALTER TABLE `setor`
  ADD PRIMARY KEY (`id_setor`),
  ADD KEY `id_empresa` (`id_empresa`),
  ADD KEY `fk_gerente` (`id_gerente`);

--
-- Índices de tabela `tarefa`
--
ALTER TABLE `tarefa`
  ADD PRIMARY KEY (`id_tarefa`),
  ADD KEY `id_setor` (`id_setor`),
  ADD KEY `id_criador` (`id_criador`);

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
-- AUTO_INCREMENT de tabela `cargo`
--
ALTER TABLE `cargo`
  MODIFY `id_cargo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `categoria`
--
ALTER TABLE `categoria`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `empresa`
--
ALTER TABLE `empresa`
  MODIFY `id_empresa` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `funcionario`
--
ALTER TABLE `funcionario`
  MODIFY `id_funcionario` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `setor`
--
ALTER TABLE `setor`
  MODIFY `id_setor` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tarefa`
--
ALTER TABLE `tarefa`
  MODIFY `id_tarefa` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `cargo`
--
ALTER TABLE `cargo`
  ADD CONSTRAINT `cargo_ibfk_1` FOREIGN KEY (`id_setor`) REFERENCES `setor` (`id_setor`);

--
-- Restrições para tabelas `categoria`
--
ALTER TABLE `categoria`
  ADD CONSTRAINT `categoria_ibfk_1` FOREIGN KEY (`id_empresa`) REFERENCES `empresa` (`id_empresa`);

--
-- Restrições para tabelas `funcionario`
--
ALTER TABLE `funcionario`
  ADD CONSTRAINT `funcionario_ibfk_1` FOREIGN KEY (`id_setor`) REFERENCES `setor` (`id_setor`),
  ADD CONSTRAINT `funcionario_ibfk_2` FOREIGN KEY (`id_cargo`) REFERENCES `cargo` (`id_cargo`);

--
-- Restrições para tabelas `setor`
--
ALTER TABLE `setor`
  ADD CONSTRAINT `fk_gerente` FOREIGN KEY (`id_gerente`) REFERENCES `funcionario` (`id_funcionario`),
  ADD CONSTRAINT `setor_ibfk_1` FOREIGN KEY (`id_empresa`) REFERENCES `empresa` (`id_empresa`);

--
-- Restrições para tabelas `tarefa`
--
ALTER TABLE `tarefa`
  ADD CONSTRAINT `tarefa_ibfk_1` FOREIGN KEY (`id_setor`) REFERENCES `setor` (`id_setor`),
  ADD CONSTRAINT `tarefa_ibfk_2` FOREIGN KEY (`id_criador`) REFERENCES `funcionario` (`id_funcionario`);

--
-- Restrições para tabelas `tarefa_categoria`
--
ALTER TABLE `tarefa_categoria`
  ADD CONSTRAINT `tarefa_categoria_ibfk_1` FOREIGN KEY (`id_tarefa`) REFERENCES `tarefa` (`id_tarefa`) ON DELETE CASCADE,
  ADD CONSTRAINT `tarefa_categoria_ibfk_2` FOREIGN KEY (`id_categoria`) REFERENCES `categoria` (`id_categoria`) ON DELETE CASCADE;

--
-- Restrições para tabelas `tarefa_funcionario`
--
ALTER TABLE `tarefa_funcionario`
  ADD CONSTRAINT `tarefa_funcionario_ibfk_1` FOREIGN KEY (`id_tarefa`) REFERENCES `tarefa` (`id_tarefa`) ON DELETE CASCADE,
  ADD CONSTRAINT `tarefa_funcionario_ibfk_2` FOREIGN KEY (`id_funcionario`) REFERENCES `funcionario` (`id_funcionario`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
