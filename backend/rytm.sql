-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 15, 2025 at 04:35 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `rytm`
--

-- --------------------------------------------------------

--
-- Table structure for table `customerdetails`
--

CREATE TABLE `customerdetails` (
  `CustomerID` int(10) NOT NULL,
  `Username` varchar(25) NOT NULL,
  `LastName` varchar(25) NOT NULL,
  `FirstName` varchar(25) NOT NULL,
  `Email` varchar(40) NOT NULL,
  `CustomerAddress` varchar(255) NOT NULL,
  `ContactNo` varchar(14) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `customerdetails`
--

INSERT INTO `customerdetails` (`CustomerID`, `Username`, `LastName`, `FirstName`, `Email`, `CustomerAddress`, `ContactNo`) VALUES
(4, 'floflo', 'Delfin', 'Flo', 'flodelfin@gmail.com', 'Balutan, Brgy. Santa Cruz, Angat, Bulacan', '09276517604');

-- --------------------------------------------------------

--
-- Table structure for table `orderitems`
--

CREATE TABLE `orderitems` (
  `OrderItemID` int(10) NOT NULL,
  `OrderID` int(10) NOT NULL,
  `ProductID` int(10) NOT NULL,
  `ProdOrdQty` int(10) NOT NULL,
  `ProductPrice` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orderitems`
--

INSERT INTO `orderitems` (`OrderItemID`, `OrderID`, `ProductID`, `ProdOrdQty`, `ProductPrice`) VALUES
(1, 1, 3, 8, 2900.00),
(2, 1, 2, 1, 3500.00),
(3, 3, 1, 1, 3200.00),
(4, 4, 2, 1, 3500.00),
(5, 5, 1, 1, 3200.00),
(6, 12, 2, 1, 3500.00);

-- --------------------------------------------------------

--
-- Table structure for table `orderlist`
--

CREATE TABLE `orderlist` (
  `OrderID` int(10) NOT NULL,
  `CustomerID` int(10) NOT NULL,
  `TotalAmount` decimal(10,2) NOT NULL,
  `TotalOrderQty` int(10) NOT NULL,
  `OrderDate` date NOT NULL,
  `OrderStatus` varchar(10) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `DeliveryDate` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orderlist`
--

INSERT INTO `orderlist` (`OrderID`, `CustomerID`, `TotalAmount`, `TotalOrderQty`, `OrderDate`, `OrderStatus`, `DeliveryDate`) VALUES
(1, 4, 26700.00, 9, '2025-09-14', 'Pending', '2025-09-17'),
(2, 4, 0.00, 0, '2025-09-14', 'Pending', '2025-09-17'),
(3, 4, 3200.00, 1, '2025-09-14', 'Pending', '2025-09-17'),
(4, 4, 3500.00, 1, '2025-09-14', 'Pending', '2025-09-17'),
(5, 4, 3200.00, 1, '2025-09-14', 'Pending', '2025-09-17'),
(6, 4, 0.00, 0, '2025-09-14', 'Pending', '2025-09-17'),
(7, 4, 0.00, 0, '2025-09-14', 'Pending', '2025-09-17'),
(8, 4, 0.00, 0, '2025-09-14', 'Pending', '2025-09-17'),
(9, 4, 0.00, 0, '2025-09-14', 'Pending', '2025-09-17'),
(10, 4, 0.00, 0, '2025-09-14', 'Pending', '2025-09-17'),
(11, 4, 0.00, 0, '2025-09-14', 'Pending', '2025-09-17'),
(12, 4, 3500.00, 1, '2025-09-14', 'Pending', '2025-09-17');

-- --------------------------------------------------------

--
-- Table structure for table `passwords`
--

CREATE TABLE `passwords` (
  `CustomerID` int(10) NOT NULL,
  `user_pass` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `passwords`
--

INSERT INTO `passwords` (`CustomerID`, `user_pass`) VALUES
(4, '$2y$10$mV7yCB1JaGchpL3k9I4I3eWNgtOiwrxFleAoENoxr1xl3p5la.xau');

-- --------------------------------------------------------

--
-- Table structure for table `productdetails`
--

CREATE TABLE `productdetails` (
  `ProductID` int(10) NOT NULL,
  `ProductDescription` varchar(255) NOT NULL,
  `ProductName` varchar(25) NOT NULL,
  `Image` varchar(255) DEFAULT NULL,
  `Category` varchar(25) NOT NULL,
  `Stock` int(3) NOT NULL,
  `ProductPrice` decimal(10,2) NOT NULL,
  `SellerID` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `productdetails`
--

INSERT INTO `productdetails` (`ProductID`, `ProductDescription`, `ProductName`, `Image`, `Category`, `Stock`, `ProductPrice`, `SellerID`) VALUES
(1, 'A rich, full-sounding 12-string dreadnought ideal for experienced players.', 'Takamine GD37CE 12-String', 'product1.jpg', 'Acoustic', 102, 3200.00, 'SEID1324'),
(2, 'A classic black Fender acoustic—great for both beginners and intermediate players.', 'Fender CD-60S- Black', 'product2.jpg', 'Acoustic', 76, 3500.00, 'SEID1324'),
(3, 'Compact dreadnought design, ideal for younger players or travelers.', 'Oscar Schmidt OG1P-A 3/4 ', 'product3.jpg', 'Acoustic', 89, 2900.00, 'SEID1324'),
(4, 'A sleek, beginner-friendly electric guitar with a classic Strat shape.', 'Dimavery ST-312 Electric ', 'product4.jpg', 'Electric', 61, 5800.00, 'SEID1324'),
(5, 'A premium Stratocaster with Clapton signature sound and feel.', 'Fender Artist Series Eric', 'product5.jpg', 'Electric', 47, 6900.00, 'SEID1324'),
(6, 'Bold design and premium build for modern electric guitarists.', 'JS-380 Roasted Poplar Bod', 'product6.jpg', 'Electric', 38, 7500.00, 'SEID1324'),
(7, 'Colorful soprano ukulele—perfect for casual strumming or learning.', 'Ukulele 21 inch (Soprano)', 'product7.jpg', 'Ukulele', 85, 400.00, 'SEID1324'),
(8, 'Water-resistant, fun, and travel-friendly ukulele.', 'Kala Waterman Soprano SWG', 'product8.jpg', 'Ukulele', 77, 450.00, 'SEID1324'),
(9, 'Eco-friendly ukulele with a unique bamboo look.', 'Kala Ukadelic UK-BAMBOO', 'product9.jpg', 'Ukulele', 85, 350.00, 'SEID1324'),
(10, 'Eye-catching shark-shaped capo with strong clamp.', 'Zinc Alloy Shark', 'product10.jpg', 'Capo', 90, 350.00, 'SEID1324'),
(11, 'Reliable capo from Dunlop—simple and effective.', 'Dunlop Guitar Capo', 'product11.jpg', 'Capo', 90, 300.00, 'SEID1324'),
(12, 'Quick-release capo that’s easy to adjust on stage.', 'Tanglewood Speedbar', 'product12.jpg', 'Capo', 90, 300.00, 'SEID1324'),
(13, 'Classic red pearl pick for smooth tone and grip.', 'Celluloid Red Pearl', 'product13.jpg', 'Pick', 90, 150.00, 'SEID1324'),
(14, 'Durable and sleek plectrum made from Delrin.', 'Exotic Plectrums Delrin', 'product14.jpg', 'Pick', 90, 100.00, 'SEID1324'),
(15, 'Stylish tribal design with solid tone performance.', 'Petroglyph Spiral Tribal', 'product15.jpg', 'Pick', 90, 100.00, 'SEID1324');

-- --------------------------------------------------------

--
-- Table structure for table `sellerinfo`
--

CREATE TABLE `sellerinfo` (
  `SellerID` varchar(10) NOT NULL,
  `SellerLN` varchar(25) NOT NULL,
  `SellerFN` varchar(25) NOT NULL,
  `SellerEmail` varchar(25) NOT NULL,
  `SellerContactNo` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `sellerinfo`
--

INSERT INTO `sellerinfo` (`SellerID`, `SellerLN`, `SellerFN`, `SellerEmail`, `SellerContactNo`) VALUES
('SEID1324', 'macasa', 'mariz', 'marizmacasa22@gmail.com', '09772115151');

-- --------------------------------------------------------

--
-- Table structure for table `shoppingcart`
--

CREATE TABLE `shoppingcart` (
  `CartID` int(10) NOT NULL,
  `CustomerID` int(10) NOT NULL,
  `ProductName` varchar(30) NOT NULL,
  `Quantity` int(10) NOT NULL,
  `ProductPrice` decimal(10,2) NOT NULL,
  `ProductID` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `customerdetails`
--
ALTER TABLE `customerdetails`
  ADD PRIMARY KEY (`CustomerID`);

--
-- Indexes for table `orderitems`
--
ALTER TABLE `orderitems`
  ADD PRIMARY KEY (`OrderItemID`),
  ADD KEY `orderitem_orderlistid` (`OrderID`),
  ADD KEY `orderitem_prodid` (`ProductID`);

--
-- Indexes for table `orderlist`
--
ALTER TABLE `orderlist`
  ADD PRIMARY KEY (`OrderID`),
  ADD KEY `orderlist_customer` (`CustomerID`);

--
-- Indexes for table `passwords`
--
ALTER TABLE `passwords`
  ADD KEY `CustomerID` (`CustomerID`);

--
-- Indexes for table `productdetails`
--
ALTER TABLE `productdetails`
  ADD PRIMARY KEY (`ProductID`),
  ADD KEY `SellerID` (`SellerID`);

--
-- Indexes for table `sellerinfo`
--
ALTER TABLE `sellerinfo`
  ADD PRIMARY KEY (`SellerID`);

--
-- Indexes for table `shoppingcart`
--
ALTER TABLE `shoppingcart`
  ADD PRIMARY KEY (`CartID`),
  ADD KEY `ProductID` (`ProductID`),
  ADD KEY `fk_cart_customer` (`CustomerID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `customerdetails`
--
ALTER TABLE `customerdetails`
  MODIFY `CustomerID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `orderitems`
--
ALTER TABLE `orderitems`
  MODIFY `OrderItemID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `orderlist`
--
ALTER TABLE `orderlist`
  MODIFY `OrderID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `productdetails`
--
ALTER TABLE `productdetails`
  MODIFY `ProductID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `shoppingcart`
--
ALTER TABLE `shoppingcart`
  MODIFY `CartID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orderitems`
--
ALTER TABLE `orderitems`
  ADD CONSTRAINT `orderitem_orderlistid` FOREIGN KEY (`OrderID`) REFERENCES `orderlist` (`OrderID`),
  ADD CONSTRAINT `orderitem_prodid` FOREIGN KEY (`ProductID`) REFERENCES `productdetails` (`ProductID`);

--
-- Constraints for table `orderlist`
--
ALTER TABLE `orderlist`
  ADD CONSTRAINT `orderlist_customer` FOREIGN KEY (`CustomerID`) REFERENCES `customerdetails` (`CustomerID`);

--
-- Constraints for table `passwords`
--
ALTER TABLE `passwords`
  ADD CONSTRAINT `passwords_ibfk_1` FOREIGN KEY (`CustomerID`) REFERENCES `customerdetails` (`CustomerID`);

--
-- Constraints for table `shoppingcart`
--
ALTER TABLE `shoppingcart`
  ADD CONSTRAINT `fk_cart_customer` FOREIGN KEY (`CustomerID`) REFERENCES `customerdetails` (`CustomerID`),
  ADD CONSTRAINT `shoppingcart_ibfk_1` FOREIGN KEY (`ProductID`) REFERENCES `productdetails` (`ProductID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
