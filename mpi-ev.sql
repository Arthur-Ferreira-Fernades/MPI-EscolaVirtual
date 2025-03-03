-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 03/03/2025 às 07:37
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
-- Banco de dados: `mpi-ev`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `alunos`
--

CREATE TABLE `alunos` (
  `AluId` int(11) NOT NULL,
  `AluNome` varchar(100) NOT NULL,
  `AluEmail` varchar(100) NOT NULL,
  `AluFoto` varchar(255) DEFAULT 'padrao.png',
  `TurmaId` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `alunos`
--

INSERT INTO `alunos` (`AluId`, `AluNome`, `AluEmail`, `AluFoto`, `TurmaId`) VALUES
(1, 'Lucas Ferreira', 'lucas@email.com', 'lucas.png', NULL),
(2, 'Mariana Lima', 'mariana@email.com', 'mariana.png', NULL),
(4, 'Arthur', 'arthuraluno@132.com', 'padrao.png', NULL),
(5, 'Rafael T', 'rafatomizawa@gmail.com', 'padrao.png', 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `aulas`
--

CREATE TABLE `aulas` (
  `AulaId` int(11) NOT NULL,
  `TurmaId` int(11) NOT NULL,
  `ProfId` int(11) NOT NULL,
  `DataAula` date NOT NULL,
  `Conteudo` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `aulas`
--

INSERT INTO `aulas` (`AulaId`, `TurmaId`, `ProfId`, `DataAula`, `Conteudo`) VALUES
(3, 1, 7, '2025-02-26', 'SOLO VIMOS UNA HERRAMIENTA DE TRADUCCIÓN CON IA (ALSTOM).');

-- --------------------------------------------------------

--
-- Estrutura para tabela `presencas`
--

CREATE TABLE `presencas` (
  `PresencaId` int(11) NOT NULL,
  `AulaId` int(11) NOT NULL,
  `AluId` int(11) NOT NULL,
  `Presente` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `presencas`
--

INSERT INTO `presencas` (`PresencaId`, `AulaId`, `AluId`, `Presente`) VALUES
(2, 3, 5, 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `professores`
--

CREATE TABLE `professores` (
  `ProfId` int(11) NOT NULL,
  `ProNome` varchar(255) NOT NULL,
  `ProEmail` varchar(255) NOT NULL,
  `ProSenha` varchar(255) NOT NULL,
  `ProFoto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `professores`
--

INSERT INTO `professores` (`ProfId`, `ProNome`, `ProEmail`, `ProSenha`, `ProFoto`) VALUES
(7, 'Juve', 'juve@123.com', '123', 'IMG-20221008-WA0022.jpg'),
(8, 'arthur', 'arthur@123.com', '123', 'padrao.png');

-- --------------------------------------------------------

--
-- Estrutura para tabela `turmas`
--

CREATE TABLE `turmas` (
  `TurmaId` int(11) NOT NULL,
  `TurmaNome` varchar(100) NOT NULL,
  `ProfId` int(11) NOT NULL,
  `DiasSemana` varchar(255) NOT NULL,
  `Horarios` text NOT NULL,
  `TurmaReuniao` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `turmas`
--

INSERT INTO `turmas` (`TurmaId`, `TurmaNome`, `ProfId`, `DiasSemana`, `Horarios`, `TurmaReuniao`) VALUES
(1, 'Rafael T', 7, 'Quarta-feira', '{\"Quarta-feira\":\"13:00\"}', 'meet.google.com/xxv-ajik-vrm');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `alunos`
--
ALTER TABLE `alunos`
  ADD PRIMARY KEY (`AluId`),
  ADD UNIQUE KEY `AluEmail` (`AluEmail`),
  ADD KEY `TurmaId` (`TurmaId`);

--
-- Índices de tabela `aulas`
--
ALTER TABLE `aulas`
  ADD PRIMARY KEY (`AulaId`),
  ADD KEY `TurmaId` (`TurmaId`);

--
-- Índices de tabela `presencas`
--
ALTER TABLE `presencas`
  ADD PRIMARY KEY (`PresencaId`),
  ADD KEY `AulaId` (`AulaId`),
  ADD KEY `AluId` (`AluId`);

--
-- Índices de tabela `professores`
--
ALTER TABLE `professores`
  ADD PRIMARY KEY (`ProfId`);

--
-- Índices de tabela `turmas`
--
ALTER TABLE `turmas`
  ADD PRIMARY KEY (`TurmaId`),
  ADD KEY `ProfId` (`ProfId`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `alunos`
--
ALTER TABLE `alunos`
  MODIFY `AluId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `aulas`
--
ALTER TABLE `aulas`
  MODIFY `AulaId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `presencas`
--
ALTER TABLE `presencas`
  MODIFY `PresencaId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `professores`
--
ALTER TABLE `professores`
  MODIFY `ProfId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `turmas`
--
ALTER TABLE `turmas`
  MODIFY `TurmaId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `alunos`
--
ALTER TABLE `alunos`
  ADD CONSTRAINT `alunos_ibfk_1` FOREIGN KEY (`TurmaId`) REFERENCES `turmas` (`TurmaId`) ON DELETE SET NULL;

--
-- Restrições para tabelas `aulas`
--
ALTER TABLE `aulas`
  ADD CONSTRAINT `aulas_ibfk_1` FOREIGN KEY (`TurmaId`) REFERENCES `turmas` (`TurmaId`) ON DELETE CASCADE;

--
-- Restrições para tabelas `presencas`
--
ALTER TABLE `presencas`
  ADD CONSTRAINT `presencas_ibfk_1` FOREIGN KEY (`AulaId`) REFERENCES `aulas` (`AulaId`) ON DELETE CASCADE,
  ADD CONSTRAINT `presencas_ibfk_2` FOREIGN KEY (`AluId`) REFERENCES `alunos` (`AluId`) ON DELETE CASCADE;

--
-- Restrições para tabelas `turmas`
--
ALTER TABLE `turmas`
  ADD CONSTRAINT `turmas_ibfk_1` FOREIGN KEY (`ProfId`) REFERENCES `professores` (`ProfId`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
