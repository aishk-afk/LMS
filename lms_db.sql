-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 29, 2026 at 02:08 PM
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
-- Database: `lms_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `user_id` varchar(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO users (user_id, first_name, last_name, email, password, user_type)
VALUES
('ADMIN001', 'John', 'Escoto', 'admin@school.edu', SHA2('password123', 256), 'Admin'),
('ST001', 'Jane', 'Doe', 'jane@school.edu', SHA2('student123', 256), 'Member');

-- --------------------------------------------------------

--
-- Table structure for table `author`
--

CREATE TABLE `author` (
  `author_id` varchar(12) NOT NULL,
  `first_name` varchar(20) DEFAULT NULL,
  `last_name` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `book`
--

CREATE TABLE `book` (
  `book_id` varchar(12) NOT NULL,
  `ISBN` varchar(15) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `edition` varchar(50) DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `image_url` varchar(2083) DEFAULT NULL,
  `publication_date` varchar(20) DEFAULT NULL,
  `publisher_name` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT 0.00,
  `copies` int(11) DEFAULT 1,
  `Publisher_publisher_id` varchar(12) DEFAULT NULL,
  `Admin_user_id` varchar(11) DEFAULT NULL,
  `Genre_genre_id` varchar(12) DEFAULT NULL,
  `Category_category_id` varchar(12) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `book`
--

INSERT INTO `book` (`book_id`, `ISBN`, `title`, `edition`, `description`, `image_url`, `publication_date`, `publisher_name`, `price`, `copies`, `Publisher_publisher_id`, `Admin_user_id`, `Genre_genre_id`, `Category_category_id`) VALUES
('BK-39659', '9780786493234', 'Approaching the Hunger Games Trilogy', '', 'This book addresses Suzanne Collins\'s work from a number of literary and cultural perspectives in an effort to better understand both its significance and its appeal. It takes an interdisciplinary approach to the Hunger Games trilogy, drawing from literary studies, psychology, gender studies, media studies, philosophy, and cultural studies. An analytical rather than evaluative work, it dispenses with extended theoretical discussions and academic jargon. Assuming that readers are familiar with the entire trilogy, the book also avoids plot summary and character analysis, instead focusing on the significance of the story and its characters. It includes a biographical essay, glossaries, questions for further study, and an extensive bibliography. Instructors considering this book for use in a course may request an examination copy here.', 'http://books.google.com/books/content?id=AyqesEOQ4P8C&printsec=frontcover&img=1&zoom=1&edge=curl&source=gbs_api', '2012', 'McFarland', 0.00, 1, 'PUB-001', 'ADM-001', 'G01', 'C01'),
('BK-82916', '9781853261589', 'The Little Prince', '', 'The story of an airman\'s discovery in the desert of a small boy from another planet.ther planet._', 'http://books.google.com/books/content?id=CQYg20lTHtMC&printsec=frontcover&img=1&zoom=1&edge=curl&source=gbs_api', '1995', 'Wordsworth Editions', 0.00, 1, 'PUB-001', 'ADM-001', 'G01', 'C01');

-- --------------------------------------------------------

--
-- Table structure for table `book_copy`
--

CREATE TABLE `book_copy` (
  `copy_id` varchar(12) NOT NULL,
  `Book_book_id` varchar(12) DEFAULT NULL,
  `status` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `book_transaction`
--

CREATE TABLE `book_transaction` (
  `borrow_id` varchar(30) NOT NULL,
  `Book_Copy_copy_id` varchar(12) DEFAULT NULL,
  `Member_user_id` varchar(11) DEFAULT NULL,
  `borrow_date` datetime NOT NULL,
  `due_date` datetime NOT NULL,
  `return_date` datetime DEFAULT NULL,
  `status` enum('Borrowed','Returned','Overdue') NOT NULL DEFAULT 'Borrowed'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `category_id` varchar(12) NOT NULL,
  `category_name` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`category_id`, `category_name`) VALUES
('C01', 'Home Use'),
('C02', 'Classroom Use'),
('C03', 'Reference');

-- --------------------------------------------------------

--
-- Table structure for table `fine`
--

CREATE TABLE `fine` (
  `fine_id` varchar(12) NOT NULL,
  `Book_Transaction_borrow_id` varchar(30) DEFAULT NULL,
  `fine_rate` int(11) DEFAULT 3,
  `overdue_days` int(11) DEFAULT 0,
  `total_amount_accrued` int(11) NOT NULL,
  `amount_paid` int(11) DEFAULT 0,
  `balance` int(11) NOT NULL,
  `payment_status` varchar(10) DEFAULT NULL,
  `status` varchar(12) DEFAULT NULL,
  `overdue_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `genre`
--

CREATE TABLE `genre` (
  `genre_id` varchar(12) NOT NULL,
  `genre_name` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `genre`
--

INSERT INTO `genre` (`genre_id`, `genre_name`) VALUES
('G01', 'Computer Science'),
('G02', 'Software Engineering'),
('G03', 'Psychology'),
('G04', 'Biology'),
('G05', 'Philosophy'),
('G06', 'Economics');

-- --------------------------------------------------------

--
-- Table structure for table `member`
--

CREATE TABLE `member` (
  `user_id` varchar(11) NOT NULL,
  `Department` enum('CCS','CBA','CAMP','CON','CAS','CED','CEA','CCJE') NOT NULL,
  `Course` varchar(50) DEFAULT NULL,
  `Section` varchar(8) DEFAULT NULL,
  `Member_TYPE` enum('Student','Faculty','Staff') NOT NULL DEFAULT 'Student'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `member`
--

INSERT INTO `member` (`user_id`, `Department`, `Course`, `Section`, `Member_TYPE`) VALUES
('ST001', 'CCS', 'BS Information Technology', 'IT3A', 'Student');

-- --------------------------------------------------------

--
-- Table structure for table `publisher`
--

CREATE TABLE `publisher` (
  `publisher_id` varchar(12) NOT NULL,
  `publisher_name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `publisher`
--

INSERT INTO `publisher` (`publisher_id`, `publisher_name`) VALUES
('Pub-001', 'General Publisher');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` varchar(11) NOT NULL,
  `first_name` varchar(20) NOT NULL,
  `last_name` varchar(30) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_type` enum('Admin','Member') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `first_name`, `last_name`, `email`, `password`, `user_type`) VALUES
('ADMIN001', 'John', 'Escoto', 'admin@school.edu', 'ef92b778bafe771e89245b89ecbc08a44a4e166c06659911881f383d4473e94f', 'Admin'),
('ST001', 'Jane', 'Doe', 'jane@school.edu', '703b0a3d6ad75b649a28adde7d83c6251da457549263bc7ff45ec709b0a8448b', 'Member');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `author`
--
ALTER TABLE `author`
  ADD PRIMARY KEY (`author_id`);

--
-- Indexes for table `book`
--
ALTER TABLE `book`
  ADD PRIMARY KEY (`book_id`),
  ADD KEY `B_Pub_FK` (`Publisher_publisher_id`),
  ADD KEY `B_Gen_FK` (`Genre_genre_id`);

--
-- Indexes for table `book_copy`
--
ALTER TABLE `book_copy`
  ADD PRIMARY KEY (`copy_id`),
  ADD KEY `BC_B_FK` (`Book_book_id`);

--
-- Indexes for table `book_transaction`
--
ALTER TABLE `book_transaction`
  ADD PRIMARY KEY (`borrow_id`),
  ADD KEY `BT_Copy_FK` (`Book_Copy_copy_id`),
  ADD KEY `BT_Mem_FK` (`Member_user_id`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `fine`
--
ALTER TABLE `fine`
  ADD PRIMARY KEY (`fine_id`),
  ADD UNIQUE KEY `Book_Transaction_borrow_id` (`Book_Transaction_borrow_id`);

--
-- Indexes for table `genre`
--
ALTER TABLE `genre`
  ADD PRIMARY KEY (`genre_id`);

--
-- Indexes for table `member`
--
ALTER TABLE `member`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `publisher`
--
ALTER TABLE `publisher`
  ADD PRIMARY KEY (`publisher_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin`
--
ALTER TABLE `admin`
  ADD CONSTRAINT `Admin_User_FK` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `book`
--
ALTER TABLE `book`
  ADD CONSTRAINT `B_Gen_FK` FOREIGN KEY (`Genre_genre_id`) REFERENCES `genre` (`genre_id`),
  ADD CONSTRAINT `B_Pub_FK` FOREIGN KEY (`Publisher_publisher_id`) REFERENCES `publisher` (`publisher_id`);

--
-- Constraints for table `book_copy`
--
ALTER TABLE `book_copy`
  ADD CONSTRAINT `BC_B_FK` FOREIGN KEY (`Book_book_id`) REFERENCES `book` (`book_id`);

--
-- Constraints for table `book_transaction`
--
ALTER TABLE `book_transaction`
  ADD CONSTRAINT `BT_Copy_FK` FOREIGN KEY (`Book_Copy_copy_id`) REFERENCES `book_copy` (`copy_id`),
  ADD CONSTRAINT `BT_Mem_FK` FOREIGN KEY (`Member_user_id`) REFERENCES `member` (`user_id`);

--
-- Constraints for table `fine`
--
ALTER TABLE `fine`
  ADD CONSTRAINT `F_BT_FK` FOREIGN KEY (`Book_Transaction_borrow_id`) REFERENCES `book_transaction` (`borrow_id`);

--
-- Constraints for table `member`
--
ALTER TABLE `member`
  ADD CONSTRAINT `Member_User_FK` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
