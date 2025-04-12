-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 21, 2025 at 04:31 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pcldb`
--

-- --------------------------------------------------------

--
-- Table structure for table `ar`
--

CREATE TABLE `ar` (
  `ar_id` int(11) NOT NULL,
  `invoice_number` int(11) NOT NULL,
  `date_received` date NOT NULL,
  `remarks` enum('Waiting for approval (client)','Missing Docs','','') NOT NULL,
  `cs_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `budget`
--

CREATE TABLE `budget` (
  `budget_id` int(11) NOT NULL,
  `fuel` int(11) NOT NULL,
  `toll` int(11) NOT NULL,
  `parking` int(11) NOT NULL,
  `allowance` int(11) NOT NULL,
  `cs_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customerservice`
--

CREATE TABLE `customerservice` (
  `cs_id` int(11) NOT NULL,
  `waybill` int(11) NOT NULL,
  `date` date NOT NULL,
  `status` enum('Freight Order','Purchase Order','Stock Transfer Order','') NOT NULL,
  `delivery_type` varchar(255) NOT NULL,
  `source` varchar(255) NOT NULL,
  `pickup` varchar(255) NOT NULL,
  `dropoff` varchar(255) NOT NULL,
  `rate` varchar(255) NOT NULL,
  `call_time` time NOT NULL,
  `odo_in` int(11) NOT NULL,
  `odo_out` int(11) NOT NULL,
  `odo_total` int(11) NOT NULL,
  `driver` varchar(255) NOT NULL,
  `helper1` varchar(255) NOT NULL,
  `helper2` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `driver`
--

CREATE TABLE `driver` (
  `driver_id` int(11) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `contact` int(11) NOT NULL,
  `status` enum('ACTIVE','INACTIVE','','') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `driver`
--

INSERT INTO `driver` (`driver_id`, `fullname`, `contact`, `status`) VALUES
(1, 'Alex Ballester', 0, 'ACTIVE'),
(2, 'Mike Floresca', 0, 'ACTIVE'),
(3, 'Marv Tandang', 0, 'ACTIVE'),
(4, 'Alberto Delos Reyes', 0, 'ACTIVE'),
(5, 'Nelson Cabangbang', 0, 'ACTIVE'),
(6, 'Alexander Detecio', 0, 'ACTIVE'),
(7, 'Ryan Beran', 0, 'ACTIVE'),
(8, 'Armando Alisna', 0, 'ACTIVE'),
(9, 'Arlon Luna', 0, 'ACTIVE'),
(10, 'Jorge Estaquio', 0, 'ACTIVE'),
(11, 'Yasser del Castillo', 0, 'ACTIVE'),
(12, 'Leonard Bayobay', 0, 'ACTIVE'),
(13, 'Reyman Cutin', 0, 'ACTIVE'),
(14, 'Alejandro Oril', 0, 'ACTIVE'),
(15, 'Edgardo Ibeas', 0, 'ACTIVE'),
(16, 'Danny Alatan', 0, 'ACTIVE'),
(17, 'Harison Upod', 0, 'ACTIVE'),
(18, 'Michael Gabral', 0, 'ACTIVE'),
(19, 'John Paul Gumere', 0, 'ACTIVE'),
(20, 'Dave Requiron', 0, 'ACTIVE'),
(21, 'Angelo Orot', 0, 'ACTIVE'),
(22, 'Crisanto Chicote', 0, 'ACTIVE'),
(23, 'Marvin Malaque', 0, 'ACTIVE'),
(24, 'Beltran Fabella', 0, 'ACTIVE'),
(25, 'Nick Beronio', 0, 'ACTIVE'),
(26, 'Leo Salazar', 0, 'ACTIVE'),
(27, 'Natalio Sibayan', 0, 'ACTIVE'),
(28, 'Sandy Ludovico', 0, 'ACTIVE'),
(29, 'Cornelio Lumucho', 0, 'ACTIVE'),
(30, 'Agnio Poja', 0, 'ACTIVE'),
(31, 'Brando Crisostomo', 0, 'ACTIVE'),
(32, 'Ranul Huet', 0, 'ACTIVE'),
(33, 'Eusebio Espinosa', 0, 'ACTIVE'),
(34, 'Bernard Garcia', 0, 'ACTIVE'),
(35, 'Reynaldo Ramirez', 0, 'ACTIVE'),
(36, 'Ramil Baltazar', 0, 'ACTIVE'),
(37, 'Civane Nunez', 0, 'ACTIVE'),
(38, 'Rolando Doydora', 0, 'ACTIVE'),
(39, 'Teody Tapia', 0, 'ACTIVE'),
(40, 'Ulysses Versola', 0, 'ACTIVE'),
(41, 'Jory Gubaton', 0, 'ACTIVE'),
(42, 'Jeffrey Pasion', 0, 'ACTIVE'),
(43, 'Elvin Delos Santos', 0, 'ACTIVE'),
(44, 'Renante Macalisang', 0, 'ACTIVE'),
(45, 'Ricarte, Arjen', 0, 'ACTIVE'),
(46, 'Marvin Reyes', 0, 'ACTIVE'),
(47, 'Florenmar Guanzon', 0, 'ACTIVE'),
(48, 'Antonio Morada', 0, 'ACTIVE'),
(49, 'Jemar Inducal', 0, 'ACTIVE'),
(50, 'Keneth Jamier', 0, 'ACTIVE'),
(51, 'James Gahira', 0, 'ACTIVE'),
(52, 'Cesar Madali', 0, 'ACTIVE'),
(53, 'Jeffrey Dimla', 0, 'ACTIVE'),
(54, 'Archie Infeliz', 0, 'ACTIVE'),
(55, 'John Michael Dalucanog', 0, 'ACTIVE'),
(56, 'Jun Culanag', 0, 'ACTIVE'),
(57, 'Rogelio Aquino', 0, 'ACTIVE'),
(58, 'Rogelio Canales', 0, 'ACTIVE'),
(59, 'Herbert Cabale', 0, 'ACTIVE'),
(60, 'Arnel Tabuada', 0, 'ACTIVE'),
(61, 'Ariel Santos', 0, 'ACTIVE'),
(62, 'Michael Billiones', 0, 'ACTIVE'),
(63, 'Renerio Caliso', 0, 'ACTIVE'),
(64, 'Ronald Miranda', 0, 'ACTIVE'),
(65, 'Romelito Canete', 0, 'ACTIVE'),
(66, 'Paulino Ferrera', 0, 'ACTIVE'),
(67, 'Manco, Marjun', 0, 'ACTIVE'),
(68, 'James Lee Rodriguez', 0, 'ACTIVE'),
(69, 'Joenalo Crucillo', 0, 'ACTIVE'),
(70, 'Joselito John Mcnab', 0, 'ACTIVE'),
(71, 'Joselito Limsec', 0, 'ACTIVE'),
(72, 'Norman Abila', 0, 'ACTIVE'),
(73, 'Jason Napagal', 0, 'ACTIVE'),
(74, 'Joseph Montariana', 0, 'ACTIVE'),
(75, 'Arjay Dela Pisa', 0, 'ACTIVE'),
(76, 'Armando Malaay', 0, 'ACTIVE'),
(77, 'Arturo Ancheta', 0, 'ACTIVE'),
(78, 'Maranan, Nikko', 0, 'ACTIVE'),
(79, 'Castillo, Gary', 0, 'ACTIVE'),
(80, 'Paul Albert Bernardo', 0, 'ACTIVE'),
(81, 'Cariel Divinagracia', 0, 'ACTIVE'),
(82, 'Virgilio Ventura', 0, 'ACTIVE');

-- --------------------------------------------------------

--
-- Table structure for table `employee`
--

CREATE TABLE `employee` (
  `emp_id` int(11) NOT NULL,
  `emp_num` int(11) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `u_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee`
--

INSERT INTO `employee` (`emp_id`, `emp_num`, `fullname`, `email`, `password`, `u_id`) VALUES
(1, 8033, 'DOTE, ARIEL C.', 'ariel.dote@pcl.com', 'admin', 1),
(2, 120065, 'JINTALAN, SHARIE ANN R.', 'sharie.jintalan@pcl.com', 'admin', 1),
(3, 235, 'SANTIAGO, MARLON R.', 'marlon.santiago@pcl.com', 'admin', 2),
(4, 8849, 'CATACUTAN, PRINCESS A.', 'princess.catacutan@pcl.com', 'admin', 2),
(5, 1914, 'MONDEJAR, NARLYN F.', 'narlyn.mondejar@pcl.com', 'admin', 3),
(6, 2747, 'AGDEPPA, GABRIEL M.', 'gabriel.agdeppa@pcl.com', 'admin', 4),
(7, 3146, 'DE ASIS, GRACE G.', 'grace.deasis@pcl.com', 'admin', 5),
(8, 2756, 'MELENDRES, REYCELL ANNE D.', 'reycell.melendres@pcl.com', 'admin', 5),
(9, 2786, 'ALFANTE, VABBEE NEL S.', 'vabbee.alfante@pcl.com', 'admin', 5),
(10, 1239, 'ERCILLA, ERADEL P.', 'eradel.ercilla@pcl.com', 'admin', 6),
(11, 3124, 'ARANDA, DARLENE JOY I.', 'darlene.aranda@pcl.com', 'admin', 6),
(12, 2848, 'DULAY, JANN PAOLO P.', 'jann.dulay@pcl.com', 'admin', 7),
(13, 3160, 'ILASIN, EMMANUEL JR. R.', 'emmanuel.ilasin@pcl.com', 'admin', 7),
(14, 2631, 'ABROGAR, VIRGINIA D.', 'virginia.abrogar@pcl.com', 'admin', 7),
(15, 2857, 'DEL ROSARIO, JOSE JR L.', 'jose.delrosario@pcl.com', 'admin', 8),
(16, 3174, 'DELA CRUZ, ANNA MAE', 'anna.dela.cruz@pcl.com', 'admin', 8),
(17, 1129, 'DALANGIN, BIEN DEO I.', 'bien.dalangin@pcl.com', 'admin', 9),
(18, 3235, 'BERMIL, CHERRY MAE B.', 'cherry.bermil@pcl.com', 'admin', 9),
(19, 2334, 'ANCHETA, ESTELA F.', 'estela.ancheta@pcl.com', 'admin', 9),
(20, 3248, 'FLORES, JULIETA Y.', 'julieta.flores@pcl.com', 'admin', 9),
(21, 3184, 'BUHAY III, GREGORIO', 'gregorio.buhay@pcl.com', 'admin', 9);

-- --------------------------------------------------------

--
-- Table structure for table `helper1`
--

CREATE TABLE `helper1` (
  `helper1_id` int(11) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `contact` int(11) NOT NULL,
  `status` enum('ACTIVE','INACTIVE','','') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `helper1`
--

INSERT INTO `helper1` (`helper1_id`, `fullname`, `contact`, `status`) VALUES
(1, 'Jeam Benis', 0, 'ACTIVE'),
(2, 'Erven Charls Calica', 0, 'ACTIVE'),
(3, 'Noli Velasco', 0, 'ACTIVE'),
(4, 'Dave Estrellado', 0, 'ACTIVE'),
(5, 'Reymark Escol', 0, 'ACTIVE'),
(6, 'John Mark Asunsion', 0, 'ACTIVE'),
(7, 'Felix Demecillo', 0, 'ACTIVE'),
(8, 'Jeffrey Permejo', 0, 'ACTIVE'),
(9, 'Justine Babsaay', 0, 'ACTIVE'),
(10, 'Freben Calica', 0, 'ACTIVE'),
(11, 'Remon Acos', 0, 'ACTIVE'),
(12, 'John Paul Conese', 0, 'ACTIVE'),
(13, 'Orlando Gallego', 0, 'ACTIVE'),
(14, 'Karyll John Cabural', 0, 'ACTIVE'),
(15, 'Levi Gebuilaguin', 0, 'ACTIVE'),
(16, 'Arnel Reyes', 0, 'ACTIVE'),
(17, 'Lester Castillon', 0, 'ACTIVE'),
(18, 'Dy Otero', 0, 'ACTIVE'),
(19, 'Rodel Abalos', 0, 'ACTIVE'),
(20, 'Lengie Beltran', 0, 'ACTIVE'),
(21, 'Ritchel Galay', 0, 'ACTIVE'),
(22, 'John Mark Ralla', 0, 'ACTIVE'),
(23, 'John Capili', 0, 'ACTIVE'),
(24, 'Marlon Grepaldeo', 0, 'ACTIVE'),
(25, 'Jayjay Umandal', 0, 'ACTIVE'),
(26, 'Nicole Estorco', 0, 'ACTIVE'),
(27, 'Romie Pabunan', 0, 'ACTIVE'),
(28, 'Renier Fuentes', 0, 'ACTIVE'),
(29, 'Gerglan Beltran', 0, 'ACTIVE'),
(30, 'Gerald Irenea', 0, 'ACTIVE'),
(31, 'Kim Amado', 0, 'ACTIVE'),
(32, 'Allan Llaneta', 0, 'ACTIVE'),
(33, 'Frederick Sabidal', 0, 'ACTIVE'),
(34, 'Alvin Labay', 0, 'ACTIVE'),
(35, 'Bernardo Ravelo', 0, 'ACTIVE'),
(36, 'Bryan Dulzo', 0, 'ACTIVE'),
(37, 'Arbe Turreda', 0, 'ACTIVE'),
(38, 'Civer Nunez', 0, 'ACTIVE'),
(39, 'Rolando Gabriel', 0, 'ACTIVE'),
(40, 'Legion Avidnigo', 0, 'ACTIVE'),
(41, 'Elmir Almario', 0, 'ACTIVE'),
(42, 'Jay Capellan', 0, 'ACTIVE'),
(43, 'Marlon Grepaldeo', 0, 'ACTIVE'),
(44, 'Jay Capellan', 0, 'ACTIVE'),
(45, 'Jofredo Alumbro', 0, 'ACTIVE'),
(46, 'Rex Labra', 0, 'ACTIVE'),
(47, 'Jay Enriquez', 0, 'ACTIVE'),
(48, 'Al Pedragoza', 0, 'ACTIVE'),
(49, 'Stanlie Gallano', 0, 'ACTIVE'),
(50, 'Marvin Gonzales', 0, 'ACTIVE'),
(51, 'Jonathan Moratilla', 0, 'ACTIVE'),
(52, 'Albert Gabin', 0, 'ACTIVE'),
(53, 'Jayson Judilla', 0, 'ACTIVE'),
(54, 'Gerry Raagas', 0, 'ACTIVE'),
(55, 'Baudillo Parker', 0, 'ACTIVE'),
(56, 'Roldan Albarico', 0, 'ACTIVE'),
(57, 'Robert Rebadulla', 0, 'ACTIVE'),
(58, 'Deejay Berou', 0, 'ACTIVE'),
(59, 'Robert Napa', 0, 'ACTIVE'),
(60, 'Albert Moster', 0, 'ACTIVE'),
(61, 'Vincent Cruz', 0, 'ACTIVE'),
(62, 'Nelson Lagunsin', 0, 'ACTIVE'),
(63, 'Hilario Magno', 0, 'ACTIVE'),
(64, 'Bryan Mansanido', 0, 'ACTIVE'),
(65, 'Dulritz Del Rosario', 0, 'ACTIVE'),
(66, 'Christian Babsaay', 0, 'ACTIVE'),
(67, 'Larry Grutas', 0, 'ACTIVE'),
(68, 'Rogelito Atchuela', 0, 'ACTIVE'),
(69, 'Ken Intano', 0, 'ACTIVE'),
(70, 'Marcel Riparip', 0, 'ACTIVE'),
(71, 'Rico Alba', 0, 'ACTIVE'),
(72, 'Jian Jed Abila', 0, 'ACTIVE'),
(73, 'Michael Doña', 0, 'ACTIVE'),
(74, 'Jepson Simene', 0, 'ACTIVE'),
(75, 'Angelito Delos Santos', 0, 'ACTIVE'),
(76, 'Jonathan Abucejo', 0, 'ACTIVE'),
(77, 'Jose Manco', 0, 'ACTIVE'),
(78, 'James Gimunta', 0, 'ACTIVE'),
(79, 'Baldos, Noriel', 0, 'ACTIVE'),
(80, 'Jhunbert Leopardas', 0, 'ACTIVE'),
(81, 'Guilmar Nadura', 0, 'ACTIVE'),
(82, 'Sherwin Manzon', 0, 'ACTIVE');

-- --------------------------------------------------------

--
-- Table structure for table `helper2`
--

CREATE TABLE `helper2` (
  `helper2_id` int(11) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `contact` int(11) NOT NULL,
  `status` enum('ACTIVE','INACTIVE','','') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `helper2`
--

INSERT INTO `helper2` (`helper2_id`, `fullname`, `contact`, `status`) VALUES
(1, 'Rowel Empoy', 0, 'ACTIVE'),
(2, 'Randy Sarabello', 0, 'ACTIVE'),
(3, 'Jhubert Leopardas', 0, 'ACTIVE'),
(4, 'Arnel Dela Rama', 0, 'ACTIVE'),
(5, 'Henry Detecio', 0, 'ACTIVE'),
(6, 'Neil Lim', 0, 'ACTIVE'),
(7, 'Ed Doberano', 0, 'ACTIVE'),
(8, 'Carlito Lerios', 0, 'ACTIVE'),
(9, 'Daniel Frias', 0, 'ACTIVE'),
(10, 'Arvy Namion', 0, 'ACTIVE'),
(11, 'Anthony Escol', 0, 'ACTIVE'),
(12, 'Jonel Saguman', 0, 'ACTIVE'),
(13, 'Noel Limbana', 0, 'ACTIVE'),
(14, 'Alvin Gabin', 0, 'ACTIVE'),
(15, 'Raffy Jay Balbarino', 0, 'ACTIVE'),
(16, 'Edwin Bautista', 0, 'ACTIVE'),
(17, 'Dominic Mariquina', 0, 'ACTIVE'),
(18, 'Samantha Biay', 0, 'ACTIVE'),
(19, 'Mark Anthiny Gordo', 0, 'ACTIVE'),
(20, 'Romel Pace', 0, 'ACTIVE'),
(21, 'Gerald Pujeda', 0, 'ACTIVE'),
(22, 'John Mark Asuncion', 0, 'ACTIVE'),
(23, 'Adrian Chavez', 0, 'ACTIVE'),
(24, 'Jomar Codera', 0, 'ACTIVE'),
(25, 'Edryin Detecio', 0, 'ACTIVE'),
(26, 'Emanuel Coralde', 0, 'ACTIVE'),
(27, 'Mark Anthony Gordon', 0, 'ACTIVE'),
(28, 'Aldemar Dapat', 0, 'ACTIVE'),
(29, 'Jeric Baldera', 0, 'ACTIVE'),
(30, 'Richard Abadines', 0, 'ACTIVE'),
(31, 'John John Martillos', 0, 'ACTIVE'),
(32, 'Harwin Marquez', 0, 'ACTIVE'),
(33, 'Danicko Zuniega', 0, 'ACTIVE'),
(34, 'Niel Bornilla', 0, 'ACTIVE'),
(35, 'Romualdo Robelles', 0, 'ACTIVE'),
(36, 'Jeremy Dela Pisa', 0, 'ACTIVE');

-- --------------------------------------------------------

--
-- Table structure for table `mileage`
--

CREATE TABLE `mileage` (
  `mileage_id` int(11) NOT NULL,
  `odo_in` int(11) NOT NULL,
  `odo_out` int(11) NOT NULL,
  `odo_total` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pod`
--

CREATE TABLE `pod` (
  `pod_id` int(11) NOT NULL,
  `cs_id` int(11) NOT NULL,
  `pod_status` enum('Complete','Incomplete','','') NOT NULL,
  `date_received` date NOT NULL,
  `Remarks` enum('No Stamp','No Sign','No Counter','Missing Docs') NOT NULL,
  `pod_transmittal` varchar(255) NOT NULL,
  `date_transmitted` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `truck`
--

CREATE TABLE `truck` (
  `truck_id` int(11) NOT NULL,
  `model` varchar(255) NOT NULL,
  `truck_plate` int(11) NOT NULL,
  `status` enum('ACTIVE','INACTIVE','','') NOT NULL,
  `truck_type` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `truck`
--

INSERT INTO `truck` (`truck_id`, `model`, `truck_plate`, `status`, `truck_type`) VALUES
(1, 'FUSO', 0, 'ACTIVE', '10W'),
(2, 'FUSO', 0, 'ACTIVE', '10W'),
(3, 'FUSO', 0, 'ACTIVE', '10W'),
(4, 'FUSO', 0, 'ACTIVE', '10W'),
(5, 'FUSO', 0, 'INACTIVE', '10W'),
(6, 'FUSO', 0, 'ACTIVE', '10W'),
(7, 'FUSO', 0, 'ACTIVE', '10W'),
(8, 'FUSO', 0, 'ACTIVE', '10W'),
(9, 'FUSO', 0, 'ACTIVE', '10W'),
(10, 'FUSO', 0, 'ACTIVE', '10W'),
(11, 'FUSO', 0, 'ACTIVE', '10W'),
(12, 'FUSO', 0, 'ACTIVE', '10W'),
(13, 'FUSO', 0, 'ACTIVE', '10W Cold'),
(14, 'FUSO', 0, 'ACTIVE', '10W'),
(15, 'FUSO', 0, 'ACTIVE', '10W'),
(16, 'FUSO', 0, 'ACTIVE', '10W'),
(17, 'FUSO', 0, 'ACTIVE', '10W'),
(18, 'FUSO', 0, 'ACTIVE', '10W'),
(19, 'FUSO', 0, 'ACTIVE', '10W'),
(20, 'FUSO', 0, 'ACTIVE', '10W'),
(21, 'FUSO', 0, 'ACTIVE', '10W'),
(22, 'FUSO', 0, 'INACTIVE', '10W'),
(23, 'FUSO', 0, 'ACTIVE', '10W'),
(24, 'FUSO', 0, 'ACTIVE', '10W'),
(25, 'FUSO', 0, 'ACTIVE', '10W'),
(26, 'FUSO', 0, 'ACTIVE', '10W'),
(27, 'FUSO', 0, 'ACTIVE', '10W'),
(28, 'FUSO', 0, 'ACTIVE', '10W'),
(29, 'FUSO', 0, 'ACTIVE', '10W'),
(30, 'FUSO', 0, 'ACTIVE', '10W'),
(31, 'FUSO', 0, 'ACTIVE', '10W'),
(32, 'FUSO', 0, 'ACTIVE', '10W'),
(33, 'FUSO', 0, 'ACTIVE', '10W'),
(34, 'FUSO', 0, 'ACTIVE', '10W'),
(35, 'FUSO', 0, 'ACTIVE', '10W'),
(36, 'HINO', 0, 'ACTIVE', '6W'),
(37, 'HINO', 0, 'ACTIVE', '6W'),
(38, 'HINO', 0, 'ACTIVE', '6W'),
(39, 'HINO', 0, 'ACTIVE', '6W'),
(40, 'HINO', 0, 'ACTIVE', '6W'),
(41, 'HINO', 0, 'ACTIVE', '6W'),
(42, 'HINO', 0, 'ACTIVE', '6W'),
(43, 'HINO', 0, 'INACTIVE', '6W'),
(44, 'HINO', 0, 'ACTIVE', '6W'),
(45, 'HINO', 0, 'ACTIVE', '6W'),
(46, 'HINO', 0, 'ACTIVE', '6W'),
(47, 'HINO', 0, 'ACTIVE', '6W'),
(48, 'HINO', 0, 'ACTIVE', '6W'),
(49, 'HINO', 0, 'ACTIVE', '6W Cold'),
(50, 'FUSO', 0, 'ACTIVE', '6W'),
(51, 'FUSO', 0, 'ACTIVE', '6W'),
(52, 'HINO', 0, 'ACTIVE', '6W EV'),
(53, 'HINO', 0, 'ACTIVE', '6W EV'),
(54, 'HINO', 0, 'ACTIVE', '6W EV'),
(55, 'HINO', 0, 'ACTIVE', '6W EV'),
(56, 'HINO', 0, 'ACTIVE', '6W EV'),
(57, 'HINO', 0, 'ACTIVE', '6W EV'),
(58, 'FUSO', 0, 'ACTIVE', 'FLEXI'),
(59, 'FUSO', 0, 'ACTIVE', 'FLEXI'),
(60, 'FUSO', 0, 'ACTIVE', 'FLEXI'),
(61, 'FUSO', 0, 'ACTIVE', 'FLEXI'),
(62, 'FUSO', 0, 'ACTIVE', 'FLEXI'),
(63, 'FUSO', 0, 'ACTIVE', 'FLEXI'),
(64, 'FUSO', 0, 'ACTIVE', 'FLEXI'),
(65, 'FUSO', 0, 'ACTIVE', 'FLEXI'),
(66, 'FUSO', 0, 'ACTIVE', 'FLEXI'),
(67, 'FUSO', 0, 'ACTIVE', 'FLEXI'),
(68, 'FUSO', 0, 'ACTIVE', 'FLEXI'),
(69, 'FUSO', 0, 'ACTIVE', 'FLEXI'),
(70, 'FUSO', 0, 'ACTIVE', 'FLEXI'),
(71, 'FUSO', 0, 'ACTIVE', 'FLEXI'),
(72, 'FUSO', 0, 'ACTIVE', 'FLEXI'),
(73, 'FUSO', 0, 'INACTIVE', 'FLEXI'),
(74, 'FUSO', 0, 'ACTIVE', 'FLEXI'),
(75, 'FUSO', 0, 'ACTIVE', 'FLEXI'),
(76, 'FUSO', 0, 'ACTIVE', 'FLEXI'),
(77, 'FUSO', 0, 'ACTIVE', 'FLEXI'),
(78, 'FUSO', 0, 'ACTIVE', 'FLEXI'),
(79, 'FOTON', 0, 'INACTIVE', 'FLEXI'),
(80, 'FUSO', 0, 'ACTIVE', 'FLEXI'),
(81, 'FUSO', 0, 'ACTIVE', 'FLEXI'),
(82, 'FUSO', 0, 'ACTIVE', 'FLEXI'),
(83, 'FUSO', 0, 'ACTIVE', 'FLEXI'),
(84, 'FUSO', 0, 'ACTIVE', 'FLEXI'),
(85, 'FUSO', 0, 'ACTIVE', 'FLEXI'),
(86, 'FUSO', 0, 'INACTIVE', 'FLEXI'),
(87, 'FUSO', 0, 'INACTIVE', 'FLEXI'),
(88, 'FUSO', 0, 'INACTIVE', 'FLEXI'),
(89, 'FUSO', 0, 'INACTIVE', 'FLEXI');

-- --------------------------------------------------------

--
-- Table structure for table `usertype`
--

CREATE TABLE `usertype` (
  `u_id` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `position` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `usertype`
--

INSERT INTO `usertype` (`u_id`, `type`, `position`) VALUES
(1, 1, 'Senior Director of Transport & Energy'),
(2, 1, 'Transport Head'),
(3, 2, 'Sales and Planning Lead'),
(4, 2, 'Operations Lead'),
(5, 3, 'POD Lead'),
(6, 4, 'Billing Officer'),
(7, 5, 'HRD RECRUITEMENT ASSISTANT'),
(8, 5, 'SITE ADMIN'),
(9, 5, 'LIAISON'),
(10, 6, 'ACCOUNTING OFFICER'),
(11, 6, 'ACCOUNTING ASSISTANT'),
(12, 7, 'CONTROLLER'),
(13, 7, 'CONTROLLER'),
(14, 7, 'DISPATCHER'),
(15, 8, 'DISPATCHER'),
(16, 8, 'ENCODER'),
(17, 9, 'POD CLERK'),
(18, 9, 'POD CLERK'),
(19, 9, 'POD CLERK'),
(20, 9, 'POD CLERK'),
(21, 9, 'REFUSAL / RETURN OFFICER');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ar`
--
ALTER TABLE `ar`
  ADD PRIMARY KEY (`ar_id`),
  ADD KEY `cs_id` (`cs_id`);

--
-- Indexes for table `budget`
--
ALTER TABLE `budget`
  ADD PRIMARY KEY (`budget_id`),
  ADD UNIQUE KEY `cs_id` (`cs_id`);

--
-- Indexes for table `customerservice`
--
ALTER TABLE `customerservice`
  ADD PRIMARY KEY (`cs_id`);

--
-- Indexes for table `driver`
--
ALTER TABLE `driver`
  ADD PRIMARY KEY (`driver_id`);

--
-- Indexes for table `employee`
--
ALTER TABLE `employee`
  ADD PRIMARY KEY (`emp_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `u_id` (`u_id`) USING BTREE;

--
-- Indexes for table `helper1`
--
ALTER TABLE `helper1`
  ADD PRIMARY KEY (`helper1_id`);

--
-- Indexes for table `helper2`
--
ALTER TABLE `helper2`
  ADD PRIMARY KEY (`helper2_id`);

--
-- Indexes for table `mileage`
--
ALTER TABLE `mileage`
  ADD PRIMARY KEY (`mileage_id`);

--
-- Indexes for table `pod`
--
ALTER TABLE `pod`
  ADD PRIMARY KEY (`pod_id`),
  ADD UNIQUE KEY `cs_id` (`cs_id`);

--
-- Indexes for table `truck`
--
ALTER TABLE `truck`
  ADD PRIMARY KEY (`truck_id`);

--
-- Indexes for table `usertype`
--
ALTER TABLE `usertype`
  ADD PRIMARY KEY (`u_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ar`
--
ALTER TABLE `ar`
  MODIFY `ar_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `budget`
--
ALTER TABLE `budget`
  MODIFY `budget_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customerservice`
--
ALTER TABLE `customerservice`
  MODIFY `cs_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `driver`
--
ALTER TABLE `driver`
  MODIFY `driver_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- AUTO_INCREMENT for table `employee`
--
ALTER TABLE `employee`
  MODIFY `emp_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=85;

--
-- AUTO_INCREMENT for table `helper1`
--
ALTER TABLE `helper1`
  MODIFY `helper1_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- AUTO_INCREMENT for table `helper2`
--
ALTER TABLE `helper2`
  MODIFY `helper2_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `mileage`
--
ALTER TABLE `mileage`
  MODIFY `mileage_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pod`
--
ALTER TABLE `pod`
  MODIFY `pod_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `truck`
--
ALTER TABLE `truck`
  MODIFY `truck_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

--
-- AUTO_INCREMENT for table `usertype`
--
ALTER TABLE `usertype`
  MODIFY `u_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
