-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 01, 2025 at 07:55 PM
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
-- Table structure for table `address`
--

CREATE TABLE `address` (
  `AddressCode` int(10) NOT NULL,
  `Barangay` varchar(25) NOT NULL,
  `City` varchar(25) NOT NULL,
  `Province` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `address`
--

INSERT INTO `address` (`AddressCode`, `Barangay`, `City`, `Province`) VALUES
(1, 'Bunsuran 1st', 'Pandi', 'Bulacan');

-- --------------------------------------------------------

--
-- Table structure for table `checkoutinfo`
--

CREATE TABLE `checkoutinfo` (
  `TransactionID` int(10) NOT NULL,
  `OrderID` int(10) NOT NULL,
  `TransactionAmount` decimal(10,2) NOT NULL,
  `CustomerID` int(10) NOT NULL,
  `AmountPurchased` int(10) NOT NULL,
  `OrderStatus` varchar(10) NOT NULL,
  `OrderDate` date NOT NULL,
  `DeliveryDate` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customerdetails`
--

CREATE TABLE `customerdetails` (
  `CustomerID` int(10) NOT NULL,
  `Username` varchar(25) NOT NULL,
  `LastName` varchar(25) NOT NULL,
  `FirstName` varchar(25) NOT NULL,
  `AddressCode` int(10) NOT NULL,
  `Email` varchar(40) NOT NULL,
  `ContactNo.` varchar(14) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `customerdetails`
--

INSERT INTO `customerdetails` (`CustomerID`, `Username`, `LastName`, `FirstName`, `AddressCode`, `Email`, `ContactNo.`) VALUES
(1, 'mariz', 'Macasa', 'Mariz', 1, 'marizmacasa22@gmail.com', '09772115151'),
(2, 'jaja', 'Sandiego', 'Justine', 1, 'justine@gmail.com', '09878642542'),
(3, 'flor', 'Delfin', 'Florenstine', 1, 'florenstine@gmail.com', '09898858585');

-- --------------------------------------------------------

--
-- Table structure for table `orderlist`
--

CREATE TABLE `orderlist` (
  `OrderID` int(10) NOT NULL,
  `ProductID` int(10) NOT NULL,
  `OrderQuantity` int(10) NOT NULL,
  `CartID` int(10) NOT NULL,
  `ProductPrice` decimal(10,2) NOT NULL,
  `OrderDate` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
(1, '$2y$10$Du74YkRruYoi7xErYQFuSOG7/hySfcpnEl7Swekhq6YZOvICNv7/a'),
(2, '$2y$10$MFZbeOJs4ueYGM597t/XjuaKwb6qfNUNy1Xevs1B6L54AHj1eq5Rm'),
(3, '$2y$10$FRAaESf10xnftyGZzD32KewpctgP2Qg.4lBpcxmhqRSbyFh5n8BaC');

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
  `SellerID` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `productdetails`
--

INSERT INTO `productdetails` (`ProductID`, `ProductDescription`, `ProductName`, `Image`, `Category`, `Stock`, `ProductPrice`, `SellerID`) VALUES
(1, 'A rich, full-sounding 12-string dreadnought ideal for experienced players.', 'Takamine GD37CE 12-String', 'product1.jpg', 'Acoustic', 102, 3200.00, 1),
(2, 'A classic black Fender acoustic—great for both beginners and intermediate players.', 'Fender CD-60S- Black', 'product2.jpg', 'Acoustic', 76, 3500.00, 1),
(3, 'Compact dreadnought design, ideal for younger players or travelers.', 'Oscar Schmidt OG1P-A 3/4 ', 'product3.jpg', 'Acoustic', 89, 2900.00, 1),
(4, 'A sleek, beginner-friendly electric guitar with a classic Strat shape.', 'Dimavery ST-312 Electric ', 'product4.jpg', 'Electric', 61, 5800.00, 1),
(5, 'A premium Stratocaster with Clapton signature sound and feel.', 'Fender Artist Series Eric', 'product5.jpg', 'Electric', 47, 6900.00, 1),
(6, 'Bold design and premium build for modern electric guitarists.', 'JS-380 Roasted Poplar Bod', 'product6.jpg', 'Electric', 38, 7500.00, 1),
(7, 'Colorful soprano ukulele—perfect for casual strumming or learning.', 'Ukulele 21 inch (Soprano)', 'product7.jpg', 'Ukulele', 85, 400.00, 1),
(8, 'Water-resistant, fun, and travel-friendly ukulele.', 'Kala Waterman Soprano SWG', 'product8.jpg', 'Ukulele', 77, 450.00, 1),
(9, 'Eco-friendly ukulele with a unique bamboo look.', 'Kala Ukadelic UK-BAMBOO', 'product9.jpg', 'Ukulele', 85, 350.00, 1),
(10, 'Eye-catching shark-shaped capo with strong clamp.', 'Zinc Alloy Shark', 'product10.jpg', 'Capo', 90, 350.00, 1),
(11, 'Reliable capo from Dunlop—simple and effective.', 'Dunlop Guitar Capo', 'product11.jpg', 'Capo', 90, 300.00, 1),
(12, 'Quick-release capo that’s easy to adjust on stage.', 'Tanglewood Speedbar', 'product12.jpg', 'Capo', 90, 300.00, 1),
(13, 'Classic red pearl pick for smooth tone and grip.', 'Celluloid Red Pearl', 'product13.jpg', 'Pick', 90, 150.00, 1),
(14, 'Durable and sleek plectrum made from Delrin.', 'Exotic Plectrums Delrin', 'product14.jpg', 'Pick', 90, 100.00, 1),
(15, 'Stylish tribal design with solid tone performance.', 'Petroglyph Spiral Tribal', 'product15.jpg', 'Pick', 90, 100.00, 1);

-- --------------------------------------------------------

--
-- Table structure for table `sellerinfo`
--

CREATE TABLE `sellerinfo` (
  `SellerID` int(10) NOT NULL,
  `SellerLN` varchar(25) NOT NULL,
  `SellerFN` varchar(25) NOT NULL,
  `SellerEmail` varchar(25) NOT NULL,
  `SellerContactNo.` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `sellerinfo`
--

INSERT INTO `sellerinfo` (`SellerID`, `SellerLN`, `SellerFN`, `SellerEmail`, `SellerContactNo.`) VALUES
(1, 'macasa', 'mariz', 'marizmacasa22@gmail.com', '09772115151');

-- --------------------------------------------------------

--
-- Table structure for table `shoppingcart`
--

CREATE TABLE `shoppingcart` (
  `CartID` int(10) NOT NULL,
  `ProductName` varchar(30) NOT NULL,
  `Quantity` int(10) NOT NULL,
  `ProductPrice` decimal(10,2) NOT NULL,
  `ProductID` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `address`
--
ALTER TABLE `address`
  ADD PRIMARY KEY (`AddressCode`);

--
-- Indexes for table `checkoutinfo`
--
ALTER TABLE `checkoutinfo`
  ADD PRIMARY KEY (`TransactionID`),
  ADD KEY `OrderID` (`OrderID`),
  ADD KEY `CustomerID` (`CustomerID`);

--
-- Indexes for table `customerdetails`
--
ALTER TABLE `customerdetails`
  ADD PRIMARY KEY (`CustomerID`),
  ADD KEY `AddressCode` (`AddressCode`);

--
-- Indexes for table `orderlist`
--
ALTER TABLE `orderlist`
  ADD PRIMARY KEY (`OrderID`),
  ADD KEY `ProductID` (`ProductID`);

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
  ADD KEY `ProductID` (`ProductID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `address`
--
ALTER TABLE `address`
  MODIFY `AddressCode` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `checkoutinfo`
--
ALTER TABLE `checkoutinfo`
  MODIFY `TransactionID` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customerdetails`
--
ALTER TABLE `customerdetails`
  MODIFY `CustomerID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `orderlist`
--
ALTER TABLE `orderlist`
  MODIFY `OrderID` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `productdetails`
--
ALTER TABLE `productdetails`
  MODIFY `ProductID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `sellerinfo`
--
ALTER TABLE `sellerinfo`
  MODIFY `SellerID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `shoppingcart`
--
ALTER TABLE `shoppingcart`
  MODIFY `CartID` int(10) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `checkoutinfo`
--
ALTER TABLE `checkoutinfo`
  ADD CONSTRAINT `checkoutinfo_ibfk_1` FOREIGN KEY (`OrderID`) REFERENCES `orderlist` (`OrderID`),
  ADD CONSTRAINT `checkoutinfo_ibfk_2` FOREIGN KEY (`CustomerID`) REFERENCES `customerdetails` (`CustomerID`);

--
-- Constraints for table `customerdetails`
--
ALTER TABLE `customerdetails`
  ADD CONSTRAINT `customerdetails_ibfk_1` FOREIGN KEY (`AddressCode`) REFERENCES `address` (`AddressCode`);

--
-- Constraints for table `orderlist`
--
ALTER TABLE `orderlist`
  ADD CONSTRAINT `orderlist_ibfk_1` FOREIGN KEY (`ProductID`) REFERENCES `productdetails` (`ProductID`);

--
-- Constraints for table `passwords`
--
ALTER TABLE `passwords`
  ADD CONSTRAINT `passwords_ibfk_1` FOREIGN KEY (`CustomerID`) REFERENCES `customerdetails` (`CustomerID`),
  ADD CONSTRAINT `passwords_ibfk_2` FOREIGN KEY (`CustomerID`) REFERENCES `customerdetails` (`CustomerID`);

--
-- Constraints for table `productdetails`
--
ALTER TABLE `productdetails`
  ADD CONSTRAINT `productdetails_ibfk_1` FOREIGN KEY (`SellerID`) REFERENCES `sellerinfo` (`SellerID`);

--
-- Constraints for table `shoppingcart`
--
ALTER TABLE `shoppingcart`
  ADD CONSTRAINT `shoppingcart_ibfk_1` FOREIGN KEY (`ProductID`) REFERENCES `productdetails` (`ProductID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
