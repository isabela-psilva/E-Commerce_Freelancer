-- phpMyAdmin SQL Dump
-- version 4.0.4.2
-- http://www.phpmyadmin.net
--
-- Máquina: localhost
-- Data de Criação: 22-Out-2026 às 18:13
-- Versão do servidor: 5.6.13
-- versão do PHP: 5.4.17

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de Dados: `freelancer`
--
CREATE DATABASE IF NOT EXISTS `freelancer` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `freelancer`;

-- --------------------------------------------------------

--
-- Estrutura da tabela `categorias`
--

CREATE TABLE IF NOT EXISTS `categorias` (
  `id_categoria` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(50) NOT NULL,
  `imagem` varchar(300) NOT NULL,
  PRIMARY KEY (`id_categoria`),
  UNIQUE KEY `nome` (`nome`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

--
-- Extraindo dados da tabela `categorias`
--

INSERT INTO `categorias` (`id_categoria`, `nome`, `imagem`) VALUES
(1, 'Ilustração', 'imagens/ilustracao.jpg'),
(2, 'Escrita', 'imagens/escrita.jpg'),
(5, 'Programação', 'imagens/programacao.jpg');

-- --------------------------------------------------------

--
-- Estrutura da tabela `itens_pedidos`
--

CREATE TABLE IF NOT EXISTS `itens_pedidos` (
  `id_item_pedido` int(11) NOT NULL AUTO_INCREMENT,
  `id_pedido` int(11) NOT NULL,
  `id_servico` int(11) NOT NULL,
  `quantidade` int(11) NOT NULL,
  `preco_unitario` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id_item_pedido`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Extraindo dados da tabela `itens_pedidos`
--

INSERT INTO `itens_pedidos` (`id_item_pedido`, `id_pedido`, `id_servico`, `quantidade`, `preco_unitario`) VALUES
(1, 7, 1, 1, '200.00');

-- --------------------------------------------------------

--
-- Estrutura da tabela `pedidos`
--

CREATE TABLE IF NOT EXISTS `pedidos` (
  `id_pedido` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `data_pedido` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `total` decimal(10,2) NOT NULL,
  `status` varchar(30) NOT NULL,
  PRIMARY KEY (`id_pedido`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=8 ;

--
-- Extraindo dados da tabela `pedidos`
--

INSERT INTO `pedidos` (`id_pedido`, `id_usuario`, `data_pedido`, `total`, `status`) VALUES
(7, 1, '2026-10-22 15:07:50', '200.00', 'Pendente');

-- --------------------------------------------------------

--
-- Estrutura da tabela `servicos`
--

CREATE TABLE IF NOT EXISTS `servicos` (
  `id_servico` int(11) NOT NULL AUTO_INCREMENT,
  `id_tipo_servico` int(11) NOT NULL,
  `descricao` text NOT NULL,
  `preco` decimal(10,2) NOT NULL,
  `quantidade` int(11) NOT NULL,
  `ativo` tinyint(1) NOT NULL,
  PRIMARY KEY (`id_servico`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Extraindo dados da tabela `servicos`
--

INSERT INTO `servicos` (`id_servico`, `id_tipo_servico`, `descricao`, `preco`, `quantidade`, `ativo`) VALUES
(1, 2, 'Arte personalizada a escolha do cliente.', '200.00', 4, 1),
(2, 5, 'Criação do seu site com um especialista.', '250.00', 2, 1),
(3, 4, 'Revise seu texto com um especialista.', '85.00', 10, 1);

-- --------------------------------------------------------

--
-- Estrutura da tabela `tipos_servicos`
--

CREATE TABLE IF NOT EXISTS `tipos_servicos` (
  `id_tipo_servico` int(11) NOT NULL AUTO_INCREMENT,
  `id_categoria` int(11) NOT NULL,
  `nome` varchar(60) NOT NULL,
  PRIMARY KEY (`id_tipo_servico`),
  UNIQUE KEY `nome` (`nome`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

--
-- Extraindo dados da tabela `tipos_servicos`
--

INSERT INTO `tipos_servicos` (`id_tipo_servico`, `id_categoria`, `nome`) VALUES
(1, 1, 'Criação de Logotipo'),
(2, 1, 'Arte Personalizada'),
(3, 2, 'Criação de Texto'),
(4, 2, 'Revisão de Texto'),
(5, 5, 'Criação de Site'),
(6, 5, 'Criação de Programa');

-- --------------------------------------------------------

--
-- Estrutura da tabela `usuarios`
--

CREATE TABLE IF NOT EXISTS `usuarios` (
  `id_usuario` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `email` varchar(200) NOT NULL,
  `senha` varchar(60) NOT NULL,
  `data_cadastro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Extraindo dados da tabela `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nome`, `email`, `senha`, `data_cadastro`) VALUES
(1, 'Isabela Pereira da Silva', 'isabelap@gmail.com', '$2y$10$twRkCh.RXJX2UZGt9KquRe4PBNG3J9RPQZW8lVzITu6nSYI8Tf0Xa', '2026-10-22 13:51:19');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
