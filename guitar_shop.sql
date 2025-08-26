-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 26, 2025 at 02:00 PM
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
-- Database: `guitar shop`
--

-- --------------------------------------------------------

--
-- Table structure for table `address`
--

CREATE TABLE `address` (
  `AddressCode` int(10) NOT NULL,
  `BuildingNo.` int(10) NOT NULL,
  `StreetName` varchar(25) NOT NULL,
  `Barangay` varchar(25) NOT NULL,
  `City` varchar(25) NOT NULL,
  `Province` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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

-- --------------------------------------------------------

--
-- Table structure for table `productdetails`
--

CREATE TABLE `productdetails` (
  `ProductID` int(10) NOT NULL,
  `ProductDescription` varchar(255) NOT NULL,
  `ProductName` varchar(25) NOT NULL,
  `Stock` int(3) NOT NULL,
  `ProductPrice` decimal(10,2) NOT NULL,
  `SellerID` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sellerinfo`
--

CREATE TABLE `sellerinfo` (
  `SellerID` int(10) NOT NULL,
  `SellerLN` varchar(25) NOT NULL,
  `SellerFN` varchar(25) NOT NULL,
  `SellerEmail` varchar(25) NOT NULL,
  `SellerNo.` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
  MODIFY `AddressCode` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `checkoutinfo`
--
ALTER TABLE `checkoutinfo`
  MODIFY `TransactionID` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customerdetails`
--
ALTER TABLE `customerdetails`
  MODIFY `CustomerID` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orderlist`
--
ALTER TABLE `orderlist`
  MODIFY `OrderID` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `productdetails`
--
ALTER TABLE `productdetails`
  MODIFY `ProductID` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sellerinfo`
--
ALTER TABLE `sellerinfo`
  MODIFY `SellerID` int(10) NOT NULL AUTO_INCREMENT;

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
