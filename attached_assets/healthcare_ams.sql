-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 22, 2025 at 03:19 PM
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
-- Database: `healthcare_ams`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointment`
--

CREATE TABLE `appointment` (
  `Appt_ID` int(11) NOT NULL,
  `Pat_ID` int(11) NOT NULL,
  `Prov_ID` int(11) NOT NULL,
  `DateTime` datetime NOT NULL,
  `Status_ID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointment`
--

INSERT INTO `appointment` (`Appt_ID`, `Pat_ID`, `Prov_ID`, `DateTime`, `Status_ID`) VALUES
(1, 1, 1, '2025-05-01 10:00:00', 1),
(2, 2, 2, '2025-05-01 11:00:00', 2),
(3, 3, 3, '2025-05-02 09:30:00', 3),
(4, 4, 4, '2025-05-02 14:00:00', 4),
(5, 5, 5, '2025-05-03 13:00:00', 5),
(6, 6, 6, '2025-05-04 08:30:00', 6),
(7, 7, 7, '2025-05-04 15:30:00', 7),
(8, 8, 8, '2025-05-05 12:00:00', 8),
(9, 9, 9, '2025-05-06 10:15:00', 9),
(10, 10, 10, '2025-05-07 11:45:00', 10),
(11, 11, 11, '2025-05-08 09:00:00', 11),
(12, 12, 12, '2025-05-09 10:30:00', 12),
(13, 13, 13, '2025-05-10 13:15:00', 13),
(14, 14, 14, '2025-05-11 14:45:00', 14),
(15, 15, 15, '2025-05-12 15:00:00', 15);

-- --------------------------------------------------------

--
-- Table structure for table `appointmentstatus`
--

CREATE TABLE `appointmentstatus` (
  `Status_ID` int(11) NOT NULL,
  `Status_Descr` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointmentstatus`
--

INSERT INTO `appointmentstatus` (`Status_ID`, `Status_Descr`) VALUES
(1, 'Scheduled'),
(2, 'Completed'),
(3, 'Cancelled'),
(4, 'Rescheduled'),
(5, 'No Show'),
(6, 'Checked In'),
(7, 'Checked Out'),
(8, 'Pending'),
(9, 'Confirmed'),
(10, 'Waiting'),
(11, 'In Progress'),
(12, 'Delayed'),
(13, 'Declined'),
(14, 'Follow-Up Required'),
(15, 'Closed');

-- --------------------------------------------------------

--
-- Table structure for table `healthcareprovider`
--

CREATE TABLE `healthcareprovider` (
  `Prov_ID` int(11) NOT NULL,
  `Prov_Name` varchar(100) NOT NULL,
  `Prov_Spec` varchar(100) DEFAULT NULL,
  `Prov_Email` varchar(100) NOT NULL,
  `Prov_Phone` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `healthcareprovider`
--

INSERT INTO `healthcareprovider` (`Prov_ID`, `Prov_Name`, `Prov_Spec`, `Prov_Email`, `Prov_Phone`) VALUES
(1, 'Dr. Sarah Green', 'Cardiologist', 's.green@hospital.com', '5551230001'),
(2, 'Dr. Thomas Black', 'Pediatrician', 't.black@hospital.com', '5551230002'),
(3, 'Dr. Linda Gold', 'Neurologist', 'l.gold@hospital.com', '5551230003'),
(4, 'Dr. Mark Silver', 'Orthopedic', 'm.silver@hospital.com', '5551230004'),
(5, 'Dr. Amy Blue', 'Dermatologist', 'a.blue@hospital.com', '5551230005'),
(6, 'Dr. John Red', 'Oncologist', 'j.red@hospital.com', '5551230006'),
(7, 'Dr. Kim White', 'General Practitioner', 'k.white@hospital.com', '5551230007'),
(8, 'Dr. Sam Brown', 'Urologist', 's.brown@hospital.com', '5551230008'),
(9, 'Dr. Olivia Gray', 'Gynecologist', 'o.gray@hospital.com', '5551230009'),
(10, 'Dr. Liam Indigo', 'Psychiatrist', 'l.indigo@hospital.com', '5551230010'),
(11, 'Dr. Ava Violet', 'ENT Specialist', 'a.violet@hospital.com', '5551230011'),
(12, 'Dr. Ethan Cyan', 'Endocrinologist', 'e.cyan@hospital.com', '5551230012'),
(13, 'Dr. Zoe Lime', 'Hematologist', 'z.lime@hospital.com', '5551230013'),
(14, 'Dr. Noah Mint', 'Nephrologist', 'n.mint@hospital.com', '5551230014'),
(15, 'Dr. Mia Rose', 'Pulmonologist', 'm.rose@hospital.com', '5551230015');

-- --------------------------------------------------------

--
-- Table structure for table `notification`
--

CREATE TABLE `notification` (
  `Noti_ID` int(11) NOT NULL,
  `Pat_ID` int(11) NOT NULL,
  `Message` text NOT NULL,
  `Type_ID` int(11) DEFAULT NULL,
  `SentDate` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notification`
--

INSERT INTO `notification` (`Noti_ID`, `Pat_ID`, `Message`, `Type_ID`, `SentDate`) VALUES
(1, 1, 'Your appointment is tomorrow.', 1, '2025-05-22 13:17:15'),
(2, 2, 'Your payment is due.', 2, '2025-05-22 13:17:15'),
(3, 3, 'You have a new message from Dr. Black.', 3, '2025-05-22 13:17:15'),
(4, 4, 'System maintenance tonight.', 4, '2025-05-22 13:17:15'),
(5, 5, 'Your prescription is ready.', 5, '2025-05-22 13:17:15'),
(6, 6, 'Your test results are available.', 6, '2025-05-22 13:17:15'),
(7, 7, 'Daily health tip: Drink water!', 7, '2025-05-22 13:17:15'),
(8, 8, 'Don’t forget your follow-up.', 8, '2025-05-22 13:17:15'),
(9, 9, 'Insurance has been updated.', 9, '2025-05-22 13:17:15'),
(10, 10, 'Emergency alert issued.', 10, '2025-05-22 13:17:15'),
(11, 11, 'Enjoy a discount on your next visit!', 11, '2025-05-22 13:17:15'),
(12, 12, 'We’ve updated our policy.', 12, '2025-05-22 13:17:15'),
(13, 13, 'Your billing statement is ready.', 13, '2025-05-22 13:17:15'),
(14, 14, 'Profile updated successfully.', 14, '2025-05-22 13:17:15'),
(15, 15, 'New login from a different device.', 15, '2025-05-22 13:17:15');

-- --------------------------------------------------------

--
-- Table structure for table `notificationtype`
--

CREATE TABLE `notificationtype` (
  `Type_ID` int(11) NOT NULL,
  `Type_Descr` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notificationtype`
--

INSERT INTO `notificationtype` (`Type_ID`, `Type_Descr`) VALUES
(1, 'Appointment Reminder'),
(2, 'Payment Reminder'),
(3, 'New Message'),
(4, 'System Alert'),
(5, 'Prescription Ready'),
(6, 'Test Results Available'),
(7, 'Health Tip'),
(8, 'Follow-Up Reminder'),
(9, 'Insurance Update'),
(10, 'Emergency Alert'),
(11, 'Promotional Offer'),
(12, 'Policy Change'),
(13, 'Billing Statement'),
(14, 'Profile Update'),
(15, 'Account Activity');

-- --------------------------------------------------------

--
-- Table structure for table `patient`
--

CREATE TABLE `patient` (
  `Pat_ID` int(11) NOT NULL,
  `Pat_Name` varchar(100) NOT NULL,
  `Pat_Email` varchar(100) NOT NULL,
  `Pat_Phone` varchar(20) DEFAULT NULL,
  `Pat_Addr` text DEFAULT NULL,
  `Pat_DOB` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patient`
--

INSERT INTO `patient` (`Pat_ID`, `Pat_Name`, `Pat_Email`, `Pat_Phone`, `Pat_Addr`, `Pat_DOB`) VALUES
(1, 'Alice Brown', 'alice.brown@example.com', '1234567890', '123 Main St', '1990-01-15'),
(2, 'Bob Smith', 'bob.smith@example.com', '2345678901', '456 Elm St', '1985-07-21'),
(3, 'Catherine Lee', 'catherine.lee@example.com', '3456789012', '789 Oak St', '1992-03-09'),
(4, 'David Jones', 'david.jones@example.com', '4567890123', '101 Pine St', '1979-11-05'),
(5, 'Emma White', 'emma.white@example.com', '5678901234', '202 Birch St', '2000-04-12'),
(6, 'Frank Taylor', 'frank.taylor@example.com', '6789012345', '303 Maple St', '1988-02-28'),
(7, 'Grace Kim', 'grace.kim@example.com', '7890123456', '404 Cedar St', '1995-06-30'),
(8, 'Henry Lopez', 'henry.lopez@example.com', '8901234567', '505 Spruce St', '1983-12-19'),
(9, 'Ivy Wilson', 'ivy.wilson@example.com', '9012345678', '606 Fir St', '1997-08-01'),
(10, 'Jackie Moore', 'jackie.moore@example.com', '0123456789', '707 Walnut St', '1991-10-23'),
(11, 'Kevin Clark', 'kevin.clark@example.com', '1112223333', '808 Chestnut St', '1986-05-17'),
(12, 'Laura Davis', 'laura.davis@example.com', '2223334444', '909 Hickory St', '1993-09-09'),
(13, 'Mike Hall', 'mike.hall@example.com', '3334445555', '100 Willow St', '1980-03-15'),
(14, 'Nina Adams', 'nina.adams@example.com', '4445556666', '111 Ash St', '1996-01-29'),
(15, 'Oscar Young', 'oscar.young@example.com', '5556667777', '222 Poplar St', '1989-07-11');

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `Payment_ID` int(11) NOT NULL,
  `Pat_ID` int(11) NOT NULL,
  `Amount` decimal(10,2) NOT NULL,
  `PaymentMeth_ID` int(11) DEFAULT NULL,
  `PaymentStat_ID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`Payment_ID`, `Pat_ID`, `Amount`, `PaymentMeth_ID`, `PaymentStat_ID`) VALUES
(1, 1, 50.00, 1, 1),
(2, 2, 75.00, 2, 2),
(3, 3, 30.00, 3, 3),
(4, 4, 100.00, 4, 4),
(5, 5, 60.00, 5, 5),
(6, 6, 90.00, 6, 6),
(7, 7, 40.00, 7, 7),
(8, 8, 120.00, 8, 8),
(9, 9, 25.00, 9, 9),
(10, 10, 80.00, 10, 10),
(11, 11, 55.00, 11, 11),
(12, 12, 45.00, 12, 12),
(13, 13, 65.00, 13, 13),
(14, 14, 70.00, 14, 14),
(15, 15, 85.00, 15, 15);

-- --------------------------------------------------------

--
-- Table structure for table `paymentmethod`
--

CREATE TABLE `paymentmethod` (
  `PaymentMeth_ID` int(11) NOT NULL,
  `MethodName` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `paymentmethod`
--

INSERT INTO `paymentmethod` (`PaymentMeth_ID`, `MethodName`) VALUES
(1, 'Cash'),
(2, 'Credit Card'),
(3, 'Debit Card'),
(4, 'Mobile Payment'),
(5, 'Bank Transfer'),
(6, 'PayPal'),
(7, 'Insurance'),
(8, 'Voucher'),
(9, 'Online Banking'),
(10, 'Health Card'),
(11, 'Apple Pay'),
(12, 'Google Pay'),
(13, 'Cheque'),
(14, 'POS'),
(15, 'Cryptocurrency');

-- --------------------------------------------------------

--
-- Table structure for table `paymentstatus`
--

CREATE TABLE `paymentstatus` (
  `PaymentStat_ID` int(11) NOT NULL,
  `Status_Descr` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `paymentstatus`
--

INSERT INTO `paymentstatus` (`PaymentStat_ID`, `Status_Descr`) VALUES
(1, 'Pending'),
(2, 'Paid'),
(3, 'Declined'),
(4, 'Refunded'),
(5, 'Failed'),
(6, 'In Review'),
(7, 'Completed'),
(8, 'Cancelled'),
(9, 'Processing'),
(10, 'Partial'),
(11, 'Approved'),
(12, 'Overdue'),
(13, 'Cleared'),
(14, 'Held'),
(15, 'Reversed');

-- --------------------------------------------------------

--
-- Table structure for table `provideravailability`
--

CREATE TABLE `provideravailability` (
  `Avail_ID` int(11) NOT NULL,
  `Prov_ID` int(11) NOT NULL,
  `Prov_Avail` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `provideravailability`
--

INSERT INTO `provideravailability` (`Avail_ID`, `Prov_ID`, `Prov_Avail`) VALUES
(1, 1, 'Mon-Fri: 9am-5pm'),
(2, 2, 'Tue-Thu: 10am-4pm'),
(3, 3, 'Mon-Wed: 8am-2pm'),
(4, 4, 'Fri-Sat: 1pm-6pm'),
(5, 5, 'Mon-Fri: 7am-3pm'),
(6, 6, 'Mon,Wed,Fri: 10am-6pm'),
(7, 7, 'Weekends Only: 9am-1pm'),
(8, 8, 'Mon-Fri: 11am-7pm'),
(9, 9, 'Tue-Thu: 9am-3pm'),
(10, 10, 'Mon-Fri: 8am-5pm'),
(11, 11, 'Wed-Fri: 10am-4pm'),
(12, 12, 'Mon,Thu: 2pm-8pm'),
(13, 13, 'Mon-Sat: 9am-12pm'),
(14, 14, 'Sun Only: 10am-2pm'),
(15, 15, 'Flexible');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointment`
--
ALTER TABLE `appointment`
  ADD PRIMARY KEY (`Appt_ID`),
  ADD KEY `Pat_ID` (`Pat_ID`),
  ADD KEY `Prov_ID` (`Prov_ID`),
  ADD KEY `Status_ID` (`Status_ID`);

--
-- Indexes for table `appointmentstatus`
--
ALTER TABLE `appointmentstatus`
  ADD PRIMARY KEY (`Status_ID`);

--
-- Indexes for table `healthcareprovider`
--
ALTER TABLE `healthcareprovider`
  ADD PRIMARY KEY (`Prov_ID`),
  ADD UNIQUE KEY `Prov_Email` (`Prov_Email`);

--
-- Indexes for table `notification`
--
ALTER TABLE `notification`
  ADD PRIMARY KEY (`Noti_ID`),
  ADD KEY `Pat_ID` (`Pat_ID`),
  ADD KEY `Type_ID` (`Type_ID`);

--
-- Indexes for table `notificationtype`
--
ALTER TABLE `notificationtype`
  ADD PRIMARY KEY (`Type_ID`);

--
-- Indexes for table `patient`
--
ALTER TABLE `patient`
  ADD PRIMARY KEY (`Pat_ID`),
  ADD UNIQUE KEY `Pat_Email` (`Pat_Email`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`Payment_ID`),
  ADD KEY `Pat_ID` (`Pat_ID`),
  ADD KEY `PaymentMeth_ID` (`PaymentMeth_ID`),
  ADD KEY `PaymentStat_ID` (`PaymentStat_ID`);

--
-- Indexes for table `paymentmethod`
--
ALTER TABLE `paymentmethod`
  ADD PRIMARY KEY (`PaymentMeth_ID`);

--
-- Indexes for table `paymentstatus`
--
ALTER TABLE `paymentstatus`
  ADD PRIMARY KEY (`PaymentStat_ID`);

--
-- Indexes for table `provideravailability`
--
ALTER TABLE `provideravailability`
  ADD PRIMARY KEY (`Avail_ID`),
  ADD KEY `Prov_ID` (`Prov_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointment`
--
ALTER TABLE `appointment`
  MODIFY `Appt_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `appointmentstatus`
--
ALTER TABLE `appointmentstatus`
  MODIFY `Status_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `healthcareprovider`
--
ALTER TABLE `healthcareprovider`
  MODIFY `Prov_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `notification`
--
ALTER TABLE `notification`
  MODIFY `Noti_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `notificationtype`
--
ALTER TABLE `notificationtype`
  MODIFY `Type_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `patient`
--
ALTER TABLE `patient`
  MODIFY `Pat_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `Payment_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `paymentmethod`
--
ALTER TABLE `paymentmethod`
  MODIFY `PaymentMeth_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `paymentstatus`
--
ALTER TABLE `paymentstatus`
  MODIFY `PaymentStat_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `provideravailability`
--
ALTER TABLE `provideravailability`
  MODIFY `Avail_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointment`
--
ALTER TABLE `appointment`
  ADD CONSTRAINT `appointment_ibfk_1` FOREIGN KEY (`Pat_ID`) REFERENCES `patient` (`Pat_ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointment_ibfk_2` FOREIGN KEY (`Prov_ID`) REFERENCES `healthcareprovider` (`Prov_ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointment_ibfk_3` FOREIGN KEY (`Status_ID`) REFERENCES `appointmentstatus` (`Status_ID`) ON DELETE SET NULL;

--
-- Constraints for table `notification`
--
ALTER TABLE `notification`
  ADD CONSTRAINT `notification_ibfk_1` FOREIGN KEY (`Pat_ID`) REFERENCES `patient` (`Pat_ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `notification_ibfk_2` FOREIGN KEY (`Type_ID`) REFERENCES `notificationtype` (`Type_ID`) ON DELETE SET NULL;

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`Pat_ID`) REFERENCES `patient` (`Pat_ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `payment_ibfk_2` FOREIGN KEY (`PaymentMeth_ID`) REFERENCES `paymentmethod` (`PaymentMeth_ID`) ON DELETE SET NULL,
  ADD CONSTRAINT `payment_ibfk_3` FOREIGN KEY (`PaymentStat_ID`) REFERENCES `paymentstatus` (`PaymentStat_ID`) ON DELETE SET NULL;

--
-- Constraints for table `provideravailability`
--
ALTER TABLE `provideravailability`
  ADD CONSTRAINT `provideravailability_ibfk_1` FOREIGN KEY (`Prov_ID`) REFERENCES `healthcareprovider` (`Prov_ID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
