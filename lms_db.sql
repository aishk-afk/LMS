-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 04, 2026 at 06:05 AM
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
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`user_id`) VALUES
(1);

-- --------------------------------------------------------

--
-- Table structure for table `author`
--

CREATE TABLE `author` (
  `author_id` int(11) NOT NULL,
  `first_name` varchar(20) NOT NULL,
  `last_name` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `book`
--

CREATE TABLE `book` (
  `book_id` int(11) NOT NULL,
  `ISBN` varchar(15) NOT NULL,
  `title` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `Publisher_publisher_id` int(11) NOT NULL,
  `image_url` varchar(2083) DEFAULT NULL,
  `Admin_user_id` int(11) NOT NULL,
  `Genre_genre_id` int(11) NOT NULL,
  `publication_date` year(4) DEFAULT NULL,
  `edition` varchar(10) DEFAULT NULL,
  `price` int(11) NOT NULL,
  `copies` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `book`
--

INSERT INTO `book` (`book_id`, `ISBN`, `title`, `description`, `Publisher_publisher_id`, `image_url`, `Admin_user_id`, `Genre_genre_id`, `publication_date`, `edition`, `price`, `copies`) VALUES
(5, '9780385385480', 'The Maze Runner Movie Tie-In Edition', '', 2, 'http://books.google.com/books/content?id=jcJWAAAAQBAJ&printsec=frontcover&img=1&zoom=1&edge=curl&source=gbs_api', 1, 7, '2000', '', 1766, 1),
(6, '9781538103661', 'A Book about the Film Monty Python\'s Life of Brian', '', 2, 'http://books.google.com/books/content?id=fjxCDwAAQBAJ&printsec=frontcover&img=1&zoom=1&edge=curl&source=gbs_api', 1, 1, '2000', '', 1234, 4),
(7, '9781846289637', 'Object-Oriented Programming and Java', 'This is a book about object-oriented programming and Java', 2, 'http://books.google.com/books/content?id=r10U16kgmkwC&printsec=frontcover&img=1&zoom=1&edge=curl&source=gbs_api', 1, 1, '2000', '', 3589, 1),
(8, '9781853261589', 'The Little Prince', 'The Little Prince,\" written by Antoine de Saint-Exupéry, begins with a pilot who crashes in the Sahara Desert. While trying to repair his plane, he meets a young boy known as the little prince, who asks him to draw a sheep. This encounter sparks a deep friendship and leads to the prince sharing his life story.', 2, 'http://books.google.com/books/content?id=CQYg20lTHtMC&printsec=frontcover&img=1&zoom=1&edge=curl&source=gbs_api', 1, 7, '1995', '', 6890, 1),
(10, '8126906073', 'Handbook of Universities', 'The Most Authentic Source Of Information On Higher Education In India The Handbook Of Universities, Deemed Universities, Colleges, Private Universities And Prominent Educational & Research Institutions Provides Much Needed Information On Degree And Diploma Awarding Universities And Institutions Of National Importance That Impart General, Technical And Professional Education In India. Although Another Directory Of Similar Nature Is Available In The Market, The Distinct Feature Of The Present Handbook, That Makes It One Of Its Kind, Is That It Also Includes Entries And Details Of The Private Universities Functioning Across The Country.In This Handbook, The Universities Have Been Listed In An Alphabetical Order. This Facilitates Easy Location Of Their Names. In Addition To The Brief History Of These Universities, The Present Handbook Provides The Names Of Their Vice-Chancellor, Professors And Readers As Well As Their Faculties And Departments. It Also Acquaints The Readers With The Various Courses Of Studies Offered By Each University.It Is Hoped That The Handbook In Its Present Form, Will Prove Immensely Helpful To The Aspiring Students In Choosing The Best Educational Institution For Their Career Enhancement. In Addition, It Will Also Prove Very Useful For The Publishers In Mailing Their Publicity Materials. Even The Suppliers Of Equipment And Services Required By These Educational Institutions Will Find It Highly Valuable.', 2, 'http://books.google.com/books/content?id=ZKgM7P5iGwgC&printsec=frontcover&img=1&zoom=1&edge=curl&source=gbs_api', 1, 3, '2006', '', 2345, 1),
(14, '9789361800276', 'MANAGERIAL ECONOMICS', 'Buy E-Book of MANAGERIAL ECONOMICS For MBA 1st Semester of ( AKTU ) Dr. A.P.J. Abdul Kalam Technical University ,UP', 2, 'http://books.google.com/books/content?id=8QMvEQAAQBAJ&printsec=frontcover&img=1&zoom=1&edge=curl&source=gbs_api', 1, 6, '2024', '', 204, 1),
(17, '0011155221', 'The Sum of All Things', '', 2, 'https://www.ingramspark.com/hs-fs/hubfs/TheSumofAllThings_cover_June21_option4(1).jpg?width=1125&name=TheSumofAllThings_cover_June21_option4(1).jpg', 1, 7, '2014', '', 23346, 1);

-- --------------------------------------------------------

--
-- Table structure for table `book_author_assignment`
--

CREATE TABLE `book_author_assignment` (
  `Book_book_id` int(11) NOT NULL,
  `Author_author_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `book_copy`
--

CREATE TABLE `book_copy` (
  `copy_id` int(11) NOT NULL,
  `Book_book_id` int(11) NOT NULL,
  `status` enum('Available','Borrowed','Reserved','Lost') DEFAULT 'Available',
  `condition` enum('New','Usable','Damaged','Lost') DEFAULT 'Usable'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `book_copy`
--

INSERT INTO `book_copy` (`copy_id`, `Book_book_id`, `status`, `condition`) VALUES
(1, 5, 'Available', 'New'),
(2, 5, 'Available', 'New'),
(3, 5, 'Available', 'New'),
(4, 5, 'Available', 'New'),
(5, 6, 'Available', 'New'),
(6, 6, 'Available', 'New'),
(7, 6, 'Available', 'New'),
(8, 6, 'Available', 'New'),
(9, 6, 'Available', 'New'),
(10, 6, 'Available', 'New'),
(11, 6, 'Available', 'New'),
(12, 6, 'Available', 'New'),
(13, 7, 'Available', 'New'),
(14, 7, 'Available', 'New'),
(15, 8, 'Available', 'New'),
(16, 10, 'Available', 'New'),
(21, 14, 'Available', 'New');

-- --------------------------------------------------------

--
-- Table structure for table `book_transaction`
--

CREATE TABLE `book_transaction` (
  `borrow_id` int(11) NOT NULL,
  `Book_Copy_copy_id` int(11) NOT NULL,
  `Member_user_id` int(11) NOT NULL,
  `borrow_date` date NOT NULL,
  `due_date` date NOT NULL,
  `return_date` date DEFAULT NULL,
  `status` enum('Active','Returned','Overdue') DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fine`
--

CREATE TABLE `fine` (
  `fine_id` int(11) NOT NULL,
  `fine_rate` int(11) NOT NULL,
  `Book_Transaction_borrow_id` int(11) NOT NULL,
  `total_amount_accrued` int(11) NOT NULL,
  `amount_paid` int(11) DEFAULT 0,
  `balance` int(11) NOT NULL,
  `overdue_date` date NOT NULL,
  `overdue_days` int(11) DEFAULT NULL,
  `Member_user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fine_payment`
--

CREATE TABLE `fine_payment` (
  `payment_id` int(11) NOT NULL,
  `Fine_fine_id` int(11) NOT NULL,
  `amount_deposited` int(11) NOT NULL,
  `payment_date` datetime DEFAULT current_timestamp(),
  `payment_status` enum('Paid','Unsettled') DEFAULT 'Unsettled'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `genre`
--

CREATE TABLE `genre` (
  `genre_id` int(11) NOT NULL,
  `genre_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `genre`
--

INSERT INTO `genre` (`genre_id`, `genre_name`) VALUES
(1, 'Computer Science'),
(2, 'Software Engineering'),
(3, 'Psychology'),
(4, 'Biology'),
(5, 'Philosophy'),
(6, 'Economics'),
(7, 'Literature'),
(8, 'Physics'),
(9, 'Sociology');

-- --------------------------------------------------------

--
-- Table structure for table `member`
--

CREATE TABLE `member` (
  `user_id` int(11) NOT NULL,
  `Department` varchar(50) DEFAULT NULL,
  `Course` varchar(50) DEFAULT NULL,
  `Section` varchar(8) DEFAULT NULL,
  `Member_Role` enum('Student','Faculty') DEFAULT 'Student'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `member`
--

INSERT INTO `member` (`user_id`, `Department`, `Course`, `Section`, `Member_Role`) VALUES
(2, 'IT Department', 'BSIT', '3A', 'Student');

-- --------------------------------------------------------

--
-- Table structure for table `publisher`
--

CREATE TABLE `publisher` (
  `publisher_id` int(11) NOT NULL,
  `publisher_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `publisher`
--

INSERT INTO `publisher` (`publisher_id`, `publisher_name`) VALUES
(1, 'Default Publisher'),
(2, ''),
(3, 'Oxford Handbooks Online'),
(4, 'Unknown Publisher'),
(5, 'Kaith Neilson Dizon');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `setting_id` int(11) NOT NULL,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` decimal(10,2) NOT NULL,
  `setting_group` varchar(30) DEFAULT 'fines',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`setting_id`, `setting_key`, `setting_value`, `setting_group`, `updated_at`) VALUES
(1, 'rate_standard', 50.00, 'fines', '2026-05-04 03:59:16'),
(3, 'rate_high', 100.00, 'fines', '2026-05-04 03:59:16'),
(4, 'admin_fee', 300.00, 'fines', '2026-05-04 03:59:16'),
(5, 'replacement_surcharge', 0.10, 'fines', '2026-05-04 02:56:40'),
(6, 'fine_cap_percent', 0.25, 'fines', '2026-05-04 02:56:40'),
(7, 'base_repair_fee', 100.00, 'fines', '2026-05-04 03:20:16'),
(8, 'damage_severity_high', 1.50, 'fines', '2026-05-04 03:20:16');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
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
(1, 'John', 'Escoto', 'admin@school.edu', 'ef92b778bafe771e89245b89ecbc08a44a4e166c06659911881f383d4473e94f', 'Admin'),
(2, 'Jane', 'Doe', 'jane@school.edu', '703b0a3d6ad75b649a28adde7d83c6251da457549263bc7ff45ec709b0a8448b', 'Member');

-- --------------------------------------------------------

--
-- Table structure for table `waitlist`
--

CREATE TABLE `waitlist` (
  `waitlist_id` int(11) NOT NULL,
  `Member_user_id` int(11) NOT NULL,
  `Book_book_id` int(11) NOT NULL,
  `request_date` date NOT NULL,
  `priority` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  ADD KEY `Publisher_publisher_id` (`Publisher_publisher_id`),
  ADD KEY `Admin_user_id` (`Admin_user_id`),
  ADD KEY `Genre_genre_id` (`Genre_genre_id`);

--
-- Indexes for table `book_author_assignment`
--
ALTER TABLE `book_author_assignment`
  ADD PRIMARY KEY (`Author_author_id`,`Book_book_id`),
  ADD KEY `Book_book_id` (`Book_book_id`);

--
-- Indexes for table `book_copy`
--
ALTER TABLE `book_copy`
  ADD PRIMARY KEY (`copy_id`),
  ADD KEY `Book_book_id` (`Book_book_id`);

--
-- Indexes for table `book_transaction`
--
ALTER TABLE `book_transaction`
  ADD PRIMARY KEY (`borrow_id`),
  ADD KEY `Book_Copy_copy_id` (`Book_Copy_copy_id`),
  ADD KEY `Member_user_id` (`Member_user_id`);

--
-- Indexes for table `fine`
--
ALTER TABLE `fine`
  ADD PRIMARY KEY (`fine_id`),
  ADD KEY `Book_Transaction_borrow_id` (`Book_Transaction_borrow_id`),
  ADD KEY `Member_user_id` (`Member_user_id`);

--
-- Indexes for table `fine_payment`
--
ALTER TABLE `fine_payment`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `Fine_fine_id` (`Fine_fine_id`);

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
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`setting_id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `waitlist`
--
ALTER TABLE `waitlist`
  ADD PRIMARY KEY (`waitlist_id`),
  ADD KEY `Member_user_id` (`Member_user_id`),
  ADD KEY `Book_book_id` (`Book_book_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `author`
--
ALTER TABLE `author`
  MODIFY `author_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `book`
--
ALTER TABLE `book`
  MODIFY `book_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `book_copy`
--
ALTER TABLE `book_copy`
  MODIFY `copy_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `book_transaction`
--
ALTER TABLE `book_transaction`
  MODIFY `borrow_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fine`
--
ALTER TABLE `fine`
  MODIFY `fine_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fine_payment`
--
ALTER TABLE `fine_payment`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `genre`
--
ALTER TABLE `genre`
  MODIFY `genre_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `publisher`
--
ALTER TABLE `publisher`
  MODIFY `publisher_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `waitlist`
--
ALTER TABLE `waitlist`
  MODIFY `waitlist_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin`
--
ALTER TABLE `admin`
  ADD CONSTRAINT `admin_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `book`
--
ALTER TABLE `book`
  ADD CONSTRAINT `book_ibfk_1` FOREIGN KEY (`Publisher_publisher_id`) REFERENCES `publisher` (`publisher_id`),
  ADD CONSTRAINT `book_ibfk_2` FOREIGN KEY (`Admin_user_id`) REFERENCES `admin` (`user_id`),
  ADD CONSTRAINT `book_ibfk_3` FOREIGN KEY (`Genre_genre_id`) REFERENCES `genre` (`genre_id`);

--
-- Constraints for table `book_author_assignment`
--
ALTER TABLE `book_author_assignment`
  ADD CONSTRAINT `book_author_assignment_ibfk_1` FOREIGN KEY (`Author_author_id`) REFERENCES `author` (`author_id`),
  ADD CONSTRAINT `book_author_assignment_ibfk_2` FOREIGN KEY (`Book_book_id`) REFERENCES `book` (`book_id`);

--
-- Constraints for table `book_copy`
--
ALTER TABLE `book_copy`
  ADD CONSTRAINT `book_copy_ibfk_1` FOREIGN KEY (`Book_book_id`) REFERENCES `book` (`book_id`);

--
-- Constraints for table `book_transaction`
--
ALTER TABLE `book_transaction`
  ADD CONSTRAINT `book_transaction_ibfk_1` FOREIGN KEY (`Book_Copy_copy_id`) REFERENCES `book_copy` (`copy_id`),
  ADD CONSTRAINT `book_transaction_ibfk_2` FOREIGN KEY (`Member_user_id`) REFERENCES `member` (`user_id`);

--
-- Constraints for table `fine`
--
ALTER TABLE `fine`
  ADD CONSTRAINT `fine_ibfk_1` FOREIGN KEY (`Book_Transaction_borrow_id`) REFERENCES `book_transaction` (`borrow_id`),
  ADD CONSTRAINT `fine_ibfk_2` FOREIGN KEY (`Member_user_id`) REFERENCES `member` (`user_id`);

--
-- Constraints for table `fine_payment`
--
ALTER TABLE `fine_payment`
  ADD CONSTRAINT `fine_payment_ibfk_1` FOREIGN KEY (`Fine_fine_id`) REFERENCES `fine` (`fine_id`);

--
-- Constraints for table `member`
--
ALTER TABLE `member`
  ADD CONSTRAINT `member_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `waitlist`
--
ALTER TABLE `waitlist`
  ADD CONSTRAINT `waitlist_ibfk_1` FOREIGN KEY (`Member_user_id`) REFERENCES `member` (`user_id`),
  ADD CONSTRAINT `waitlist_ibfk_2` FOREIGN KEY (`Book_book_id`) REFERENCES `book` (`book_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
