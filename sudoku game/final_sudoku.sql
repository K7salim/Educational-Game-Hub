-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 30, 2025 at 06:02 AM
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
-- Database: `final_sudoku`
--

-- --------------------------------------------------------

--
-- Table structure for table `achievements`
--

CREATE TABLE `achievements` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `badge_image` varchar(255) NOT NULL,
  `requirement_type` enum('levels_completed','perfect_games','total_score','time_bonus') NOT NULL,
  `requirement_value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `achievements`
--

INSERT INTO `achievements` (`id`, `name`, `description`, `badge_image`, `requirement_type`, `requirement_value`) VALUES
(1, 'First Steps', 'Complete your first level', 'badges/first_steps.png', 'levels_completed', 1),
(2, 'Sudoku Novice', 'Complete 10 levels', 'badges/novice.png', 'levels_completed', 10),
(3, 'Sudoku Apprentice', 'Complete 50 levels', 'badges/apprentice.png', 'levels_completed', 50),
(4, 'Sudoku Master', 'Complete 100 levels', 'badges/master.png', 'levels_completed', 100),
(5, 'Speed Demon', 'Complete a level in under 2 minutes', 'badges/speed_demon.png', 'time_bonus', 120),
(6, 'Perfect Score', 'Complete a level without any mistakes', 'badges/perfect.png', 'perfect_games', 1),
(7, 'Daily Challenger', 'Complete 7 daily challenges', 'badges/daily.png', 'levels_completed', 7),
(8, 'Score Hunter', 'Reach a total score of 10,000', 'badges/score_hunter.png', 'total_score', 10000);

-- --------------------------------------------------------

--
-- Table structure for table `daily_challenges`
--

CREATE TABLE `daily_challenges` (
  `id` int(11) NOT NULL,
  `date` date NOT NULL,
  `difficulty_id` int(11) NOT NULL,
  `puzzle_data` text NOT NULL,
  `solution_data` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `daily_challenge_completions`
--

CREATE TABLE `daily_challenge_completions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `challenge_id` int(11) NOT NULL,
  `score` int(11) NOT NULL,
  `time_taken` int(11) NOT NULL,
  `completed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `difficulties`
--

CREATE TABLE `difficulties` (
  `id` int(11) NOT NULL,
  `name` varchar(20) NOT NULL,
  `description` text DEFAULT NULL,
  `min_level` int(11) NOT NULL,
  `max_level` int(11) NOT NULL,
  `time_multiplier` float DEFAULT 1,
  `total_completions` int(11) DEFAULT 0,
  `average_completion_time` int(11) DEFAULT NULL,
  `perfect_completions` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `difficulties`
--

INSERT INTO `difficulties` (`id`, `name`, `description`, `min_level`, `max_level`, `time_multiplier`, `total_completions`, `average_completion_time`, `perfect_completions`) VALUES
(1, 'Easy', 'Perfect for beginners', 1, 30, 1, 0, NULL, 0),
(2, 'Medium', 'For players who want a challenge', 1, 50, 1.2, 0, NULL, 0),
(3, 'Hard', 'For experienced players', 1, 100, 1.5, 0, NULL, 0),
(4, 'Expert', 'For true Sudoku masters', 1, 200, 2, 0, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `leaderboard`
--

CREATE TABLE `leaderboard` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `level_id` int(11) NOT NULL,
  `score` int(11) NOT NULL,
  `time_taken` int(11) NOT NULL,
  `completed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leaderboard`
--

INSERT INTO `leaderboard` (`id`, `user_id`, `level_id`, `score`, `time_taken`, `completed_at`) VALUES
(6, 1, 6, 476, 24, '2025-05-29 05:35:24'),
(7, 3, 6, 470, 30, '2025-05-29 06:35:14'),
(8, 3, 7, 1040, 470, '2025-05-29 06:48:49'),
(9, 3, 8, 470, 30, '2025-05-29 08:25:11'),
(10, 3, 9, 462, 38, '2025-05-29 08:50:42'),
(11, 3, 10, 479, 21, '2025-05-29 13:33:58'),
(12, 3, 11, 477, 23, '2025-05-29 14:20:52'),
(13, 3, 14, 1478, 22, '2025-05-29 14:34:08'),
(14, 3, 15, 1472, 28, '2025-05-29 14:34:44'),
(15, 3, 16, 1479, 21, '2025-05-29 14:35:18'),
(16, 3, 17, 481, 19, '2025-05-29 14:36:37'),
(17, 2, 6, 480, 20, '2025-05-29 15:16:00'),
(18, 2, 7, 478, 22, '2025-05-29 17:03:01');

-- --------------------------------------------------------

--
-- Table structure for table `levels`
--

CREATE TABLE `levels` (
  `id` int(11) NOT NULL,
  `difficulty_id` int(11) NOT NULL,
  `level_number` int(11) NOT NULL,
  `grid_size` int(11) NOT NULL,
  `time_limit` int(11) NOT NULL,
  `puzzle_data` text NOT NULL,
  `solution_data` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `levels`
--

INSERT INTO `levels` (`id`, `difficulty_id`, `level_number`, `grid_size`, `time_limit`, `puzzle_data`, `solution_data`, `created_at`) VALUES
(6, 1, 1, 9, 180, '[[0,3,4,0,7,0,9,0,0],[0,7,2,1,9,0,3,0,8],[1,9,0,3,0,2,0,6,7],[0,5,9,0,0,1,0,2,0],[0,2,6,8,0,3,7,0,0],[0,0,0,0,0,0,8,0,0],[0,6,1,0,3,7,2,8,0],[0,8,0,0,1,9,0,3,0],[3,4,0,0,0,0,0,7,9]]', '[[5,3,4,6,7,8,9,1,2],[6,7,2,1,9,5,3,4,8],[1,9,8,3,4,2,5,6,7],[8,5,9,7,6,1,4,2,3],[4,2,6,8,5,3,7,9,1],[7,1,3,9,2,4,8,5,6],[9,6,1,5,3,7,2,8,4],[2,8,7,4,1,9,6,3,5],[3,4,5,2,8,6,1,7,9]]', '2025-05-29 05:35:24'),
(7, 1, 2, 9, 600, '[[5,3,0,0,0,0,9,1,0],[0,7,0,0,0,5,0,0,0],[0,0,8,0,4,2,0,0,0],[8,5,0,0,6,0,4,2,3],[4,2,6,0,5,3,7,9,0],[7,0,0,0,2,4,8,0,6],[0,0,1,0,0,7,0,0,0],[2,8,0,4,0,0,0,0,5],[3,4,5,2,0,6,0,0,9]]', '[[5,3,4,6,7,8,9,1,2],[6,7,2,1,9,5,3,4,8],[1,9,8,3,4,2,5,6,7],[8,5,9,7,6,1,4,2,3],[4,2,6,8,5,3,7,9,1],[7,1,3,9,2,4,8,5,6],[9,6,1,5,3,7,2,8,4],[2,8,7,4,1,9,6,3,5],[3,4,5,2,8,6,1,7,9]]', '2025-05-29 06:48:49'),
(8, 4, 1, 9, 900, '[[5,0,0,0,0,0,0,0,0],[0,0,2,1,9,0,0,0,0],[0,0,8,0,0,2,0,0,0],[0,5,0,0,6,1,0,0,0],[4,0,6,0,0,3,0,9,1],[0,0,0,9,0,0,0,0,6],[0,0,1,0,0,7,0,0,0],[0,0,7,0,0,0,6,0,5],[0,4,5,0,0,0,1,0,9]]', '[[5,3,4,6,7,8,9,1,2],[6,7,2,1,9,5,3,4,8],[1,9,8,3,4,2,5,6,7],[8,5,9,7,6,1,4,2,3],[4,2,6,8,5,3,7,9,1],[7,1,3,9,2,4,8,5,6],[9,6,1,5,3,7,2,8,4],[2,8,7,4,1,9,6,3,5],[3,4,5,2,8,6,1,7,9]]', '2025-05-29 08:25:11'),
(9, 1, 3, 9, 600, '[[0,0,4,6,0,8,9,0,2],[0,0,0,0,9,0,3,0,0],[1,0,8,3,0,0,5,0,7],[8,5,9,0,6,0,0,0,0],[0,2,6,0,5,0,0,0,1],[0,0,0,9,2,0,0,0,0],[9,6,0,0,3,7,2,0,0],[2,8,7,0,1,9,6,0,0],[3,4,5,0,8,6,0,0,0]]', '[[5,3,4,6,7,8,9,1,2],[6,7,2,1,9,5,3,4,8],[1,9,8,3,4,2,5,6,7],[8,5,9,7,6,1,4,2,3],[4,2,6,8,5,3,7,9,1],[7,1,3,9,2,4,8,5,6],[9,6,1,5,3,7,2,8,4],[2,8,7,4,1,9,6,3,5],[3,4,5,2,8,6,1,7,9]]', '2025-05-29 08:50:42'),
(10, 2, 1, 9, 180, '[[0,0,0,0,0,0,0,1,0],[6,7,2,0,0,0,0,0,0],[1,9,0,3,0,0,5,6,0],[0,0,0,7,6,1,0,2,3],[4,0,6,8,5,0,0,9,0],[7,0,3,9,0,4,8,0,0],[9,0,0,0,3,7,0,8,0],[2,0,0,0,0,0,6,0,0],[3,4,5,0,8,0,0,7,0]]', '[[5,3,4,6,7,8,9,1,2],[6,7,2,1,9,5,3,4,8],[1,9,8,3,4,2,5,6,7],[8,5,9,7,6,1,4,2,3],[4,2,6,8,5,3,7,9,1],[7,1,3,9,2,4,8,5,6],[9,6,1,5,3,7,2,8,4],[2,8,7,4,1,9,6,3,5],[3,4,5,2,8,6,1,7,9]]', '2025-05-29 13:33:58'),
(11, 1, 4, 9, 600, '[[0,3,0,6,0,0,9,0,0],[6,0,0,1,0,0,3,0,8],[1,0,8,3,4,0,5,0,7],[8,0,0,0,6,1,0,0,3],[4,0,6,0,0,0,7,9,1],[0,1,0,0,2,4,0,5,0],[9,0,1,0,0,0,0,8,0],[2,8,0,4,1,0,0,0,5],[0,4,5,0,0,0,1,0,0]]', '[[5,3,4,6,7,8,9,1,2],[6,7,2,1,9,5,3,4,8],[1,9,8,3,4,2,5,6,7],[8,5,9,7,6,1,4,2,3],[4,2,6,8,5,3,7,9,1],[7,1,3,9,2,4,8,5,6],[9,6,1,5,3,7,2,8,4],[2,8,7,4,1,9,6,3,5],[3,4,5,2,8,6,1,7,9]]', '2025-05-29 14:15:49'),
(12, 3, 1, 9, 180, '[[0,3,0,0,0,8,0,0,0],[6,7,0,1,0,5,3,0,0],[0,9,0,0,0,0,0,0,0],[0,5,9,0,0,0,4,2,3],[0,0,0,0,0,3,0,0,0],[0,0,0,0,0,4,8,0,6],[0,0,1,5,3,7,0,8,0],[0,8,0,0,1,9,0,0,5],[3,4,0,0,8,0,0,7,0]]', '[[5,3,4,6,7,8,9,1,2],[6,7,2,1,9,5,3,4,8],[1,9,8,3,4,2,5,6,7],[8,5,9,7,6,1,4,2,3],[4,2,6,8,5,3,7,9,1],[7,1,3,9,2,4,8,5,6],[9,6,1,5,3,7,2,8,4],[2,8,7,4,1,9,6,3,5],[3,4,5,2,8,6,1,7,9]]', '2025-05-29 14:27:02'),
(13, 2, 2, 9, 180, '[[0,0,4,6,0,0,9,1,0],[0,0,2,1,9,5,0,0,0],[1,0,8,0,4,2,5,0,0],[8,5,0,7,6,1,0,0,3],[4,0,6,0,5,0,0,9,0],[0,1,0,9,0,4,0,0,0],[0,0,1,0,0,0,0,0,0],[0,0,0,0,1,0,6,0,0],[0,4,0,0,8,6,1,7,0]]', '[[5,3,4,6,7,8,9,1,2],[6,7,2,1,9,5,3,4,8],[1,9,8,3,4,2,5,6,7],[8,5,9,7,6,1,4,2,3],[4,2,6,8,5,3,7,9,1],[7,1,3,9,2,4,8,5,6],[9,6,1,5,3,7,2,8,4],[2,8,7,4,1,9,6,3,5],[3,4,5,2,8,6,1,7,9]]', '2025-05-29 14:27:57'),
(14, 1, 5, 9, 600, '[[5,0,0,0,7,8,0,1,2],[6,0,2,1,0,5,0,0,8],[0,9,0,0,0,2,0,0,7],[8,0,0,0,6,1,4,2,3],[4,0,6,0,5,0,7,9,0],[0,0,0,9,2,4,8,5,0],[9,0,0,0,0,0,2,0,4],[0,8,7,0,0,9,6,0,0],[0,0,5,0,8,6,0,7,9]]', '[[5,3,4,6,7,8,9,1,2],[6,7,2,1,9,5,3,4,8],[1,9,8,3,4,2,5,6,7],[8,5,9,7,6,1,4,2,3],[4,2,6,8,5,3,7,9,1],[7,1,3,9,2,4,8,5,6],[9,6,1,5,3,7,2,8,4],[2,8,7,4,1,9,6,3,5],[3,4,5,2,8,6,1,7,9]]', '2025-05-29 14:33:44'),
(15, 1, 6, 9, 600, '[[0,3,4,0,7,0,9,0,2],[6,7,0,1,0,5,3,0,8],[1,9,0,0,4,2,0,6,0],[0,0,9,0,6,1,0,0,3],[0,0,6,0,0,0,7,0,1],[0,0,0,0,2,4,0,0,0],[0,6,0,5,0,7,2,0,0],[2,8,7,0,1,0,0,3,0],[3,4,0,0,8,6,1,0,9]]', '[[5,3,4,6,7,8,9,1,2],[6,7,2,1,9,5,3,4,8],[1,9,8,3,4,2,5,6,7],[8,5,9,7,6,1,4,2,3],[4,2,6,8,5,3,7,9,1],[7,1,3,9,2,4,8,5,6],[9,6,1,5,3,7,2,8,4],[2,8,7,4,1,9,6,3,5],[3,4,5,2,8,6,1,7,9]]', '2025-05-29 14:34:13'),
(16, 1, 7, 9, 600, '[[5,0,0,6,7,8,9,0,0],[6,7,0,0,9,0,0,0,0],[1,0,8,0,4,2,5,0,7],[0,0,9,7,0,0,0,2,0],[0,2,6,0,0,3,0,0,1],[7,1,0,0,0,4,0,5,6],[0,0,1,5,3,7,0,8,0],[0,8,0,0,1,9,6,0,0],[0,0,5,0,0,6,0,7,9]]', '[[5,3,4,6,7,8,9,1,2],[6,7,2,1,9,5,3,4,8],[1,9,8,3,4,2,5,6,7],[8,5,9,7,6,1,4,2,3],[4,2,6,8,5,3,7,9,1],[7,1,3,9,2,4,8,5,6],[9,6,1,5,3,7,2,8,4],[2,8,7,4,1,9,6,3,5],[3,4,5,2,8,6,1,7,9]]', '2025-05-29 14:34:46'),
(17, 1, 8, 9, 600, '[[0,3,4,6,0,8,0,0,2],[0,0,0,1,0,0,0,4,0],[0,9,8,3,0,0,0,6,0],[0,5,9,0,0,1,4,2,3],[4,2,0,8,0,3,7,9,1],[7,1,0,9,2,0,0,0,0],[9,0,1,0,3,0,0,8,4],[0,0,7,0,0,0,0,0,5],[0,0,0,0,8,0,1,7,0]]', '[[5,3,4,6,7,8,9,1,2],[6,7,2,1,9,5,3,4,8],[1,9,8,3,4,2,5,6,7],[8,5,9,7,6,1,4,2,3],[4,2,6,8,5,3,7,9,1],[7,1,3,9,2,4,8,5,6],[9,6,1,5,3,7,2,8,4],[2,8,7,4,1,9,6,3,5],[3,4,5,2,8,6,1,7,9]]', '2025-05-29 14:36:37');

-- --------------------------------------------------------

--
-- Table structure for table `recent_games`
--

CREATE TABLE `recent_games` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `level_id` int(11) NOT NULL,
  `score` int(11) NOT NULL,
  `time_taken` int(11) NOT NULL,
  `mistakes` int(11) DEFAULT 0,
  `hints_used` int(11) DEFAULT 0,
  `completed` tinyint(1) DEFAULT 0,
  `played_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_perfect` tinyint(1) DEFAULT 0,
  `is_speed_game` tinyint(1) DEFAULT 0,
  `difficulty_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recent_games`
--

INSERT INTO `recent_games` (`id`, `user_id`, `level_id`, `score`, `time_taken`, `mistakes`, `hints_used`, `completed`, `played_at`, `is_perfect`, `is_speed_game`, `difficulty_id`) VALUES
(1, 3, 6, 0, 0, 0, 0, 1, '2025-05-29 08:34:02', 0, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `saved_games`
--

CREATE TABLE `saved_games` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `level_id` int(11) NOT NULL,
  `game_state` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`game_state`)),
  `time_remaining` int(11) NOT NULL,
  `saved_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `saved_games`
--

INSERT INTO `saved_games` (`id`, `user_id`, `level_id`, `game_state`, `time_remaining`, `saved_at`) VALUES
(1, 3, 11, '{\"puzzle\":[[0,3,0,6,0,0,9,0,0],[6,0,0,1,0,0,3,0,8],[1,0,8,3,4,0,5,0,7],[8,0,0,0,6,1,0,0,3],[4,0,6,0,0,0,7,9,1],[0,1,0,0,2,4,0,5,0],[9,0,1,0,0,0,0,8,0],[2,8,0,4,1,0,0,0,5],[0,4,5,0,0,0,1,0,0]],\"timeLeft\":545,\"score\":1000,\"mistakes\":0,\"hintsUsed\":0,\"lastPlayed\":\"2025-05-29T14:27:47.958Z\"}', 545, '2025-05-29 14:27:47'),
(2, 3, 12, '{\"puzzle\":[[0,3,0,0,0,8,0,0,0],[6,7,0,1,0,5,3,0,0],[0,9,0,0,0,0,0,0,0],[0,5,9,0,0,0,4,2,3],[0,0,0,0,0,3,0,0,0],[0,0,0,0,0,4,8,0,6],[0,0,1,5,3,7,0,8,0],[0,8,0,0,1,9,0,0,5],[3,4,0,0,8,0,0,7,0]],\"timeLeft\":130,\"score\":1000,\"mistakes\":0,\"hintsUsed\":0,\"lastPlayed\":\"2025-05-29T14:33:40.008Z\"}', 130, '2025-05-29 14:33:40'),
(3, 3, 13, '{\"puzzle\":[[0,0,4,6,0,0,9,1,0],[0,0,2,1,9,5,0,0,0],[1,0,8,0,4,2,5,0,0],[8,5,0,7,6,1,0,0,3],[4,0,6,0,5,0,0,9,0],[0,1,0,9,0,4,0,0,0],[0,0,1,0,0,0,0,0,0],[0,0,0,0,1,0,6,0,0],[0,4,0,0,8,6,1,7,0]],\"timeLeft\":157,\"score\":1000,\"mistakes\":0,\"hintsUsed\":0,\"lastPlayed\":\"2025-05-29T14:32:59.623Z\"}', 157, '2025-05-29 14:32:59');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `total_score` int(11) DEFAULT 0,
  `games_played` int(11) DEFAULT 0,
  `perfect_games` int(11) DEFAULT 0,
  `total_perfect_games` int(11) DEFAULT 0,
  `total_speed_games` int(11) DEFAULT 0,
  `average_score` decimal(10,2) DEFAULT 0.00,
  `best_time` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `created_at`, `last_login`, `total_score`, `games_played`, `perfect_games`, `total_perfect_games`, `total_speed_games`, `average_score`, `best_time`) VALUES
(1, 'jude', '$2y$10$5PxizP5FRoGKA/9oZ4jJIO/3OdCS2.w2J3HtRiSPoiOoRngE./A9K', NULL, '2025-05-29 03:38:20', NULL, 0, 0, 0, 0, 0, 0.00, NULL),
(2, 'aldrin', '$2y$10$VzFbI.mWoWBD0JUqREB9W.lGvci4zpWvvB02EdBhLxIc67EBbtm.a', NULL, '2025-05-29 06:33:38', NULL, 0, 0, 0, 0, 0, 0.00, NULL),
(3, 'boy', '$2y$10$RXO565AaH1S3wFvmKIlvGepMJpzfrvx81HkfjcJtYp7vrkaRQeKpe', NULL, '2025-05-29 06:34:17', NULL, 0, 0, 0, 0, 0, 0.00, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_achievements`
--

CREATE TABLE `user_achievements` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `achievement_id` int(11) NOT NULL,
  `earned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_progress`
--

CREATE TABLE `user_progress` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `level_id` int(11) NOT NULL,
  `completed` tinyint(1) DEFAULT 0,
  `best_time` int(11) DEFAULT NULL,
  `attempts` int(11) DEFAULT 0,
  `best_score` int(11) DEFAULT 0,
  `stars` int(11) DEFAULT 0,
  `last_played` timestamp NOT NULL DEFAULT current_timestamp(),
  `perfect_games` int(11) DEFAULT 0,
  `speed_games` int(11) DEFAULT 0,
  `total_score` int(11) DEFAULT 0,
  `last_attempt` timestamp NOT NULL DEFAULT current_timestamp(),
  `perfect_game` tinyint(1) DEFAULT 0,
  `speed_game` tinyint(1) DEFAULT 0,
  `hints_used` int(11) DEFAULT 0,
  `last_completed_at` timestamp NULL DEFAULT NULL,
  `total_attempts` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_progress`
--

INSERT INTO `user_progress` (`id`, `user_id`, `level_id`, `completed`, `best_time`, `attempts`, `best_score`, `stars`, `last_played`, `perfect_games`, `speed_games`, `total_score`, `last_attempt`, `perfect_game`, `speed_game`, `hints_used`, `last_completed_at`, `total_attempts`) VALUES
(6, 1, 6, 1, 24, 1, 476, 0, '2025-05-29 05:35:24', 0, 0, 0, '2025-05-30 03:59:51', 0, 0, 0, NULL, 0),
(7, 3, 6, 1, 30, 1, 470, 0, '2025-05-29 06:35:14', 0, 0, 0, '2025-05-30 03:59:51', 0, 0, 0, NULL, 0),
(8, 3, 7, 1, 470, 1, 1040, 0, '2025-05-29 06:48:49', 0, 0, 0, '2025-05-30 03:59:51', 0, 0, 0, NULL, 0),
(9, 3, 8, 1, 30, 1, 470, 0, '2025-05-29 08:25:11', 0, 0, 0, '2025-05-30 03:59:51', 0, 0, 0, NULL, 0),
(10, 3, 9, 1, 38, 1, 462, 0, '2025-05-29 08:50:42', 0, 0, 0, '2025-05-30 03:59:51', 0, 0, 0, NULL, 0),
(11, 3, 10, 1, 21, 1, 479, 0, '2025-05-29 13:33:58', 0, 0, 0, '2025-05-30 03:59:51', 0, 0, 0, NULL, 0),
(12, 3, 11, 1, NULL, 1, 477, 0, '2025-05-29 14:27:47', 0, 0, 0, '2025-05-30 03:59:51', 0, 0, 0, NULL, 0),
(16, 3, 12, 0, NULL, 0, 0, 0, '2025-05-29 14:33:40', 0, 0, 0, '2025-05-30 03:59:51', 0, 0, 0, NULL, 0),
(19, 3, 13, 0, NULL, 0, 0, 0, '2025-05-29 14:32:59', 0, 0, 0, '2025-05-30 03:59:51', 0, 0, 0, NULL, 0),
(28, 3, 14, 1, 22, 1, 1478, 0, '2025-05-29 14:34:08', 0, 0, 0, '2025-05-30 03:59:51', 0, 0, 0, NULL, 0),
(29, 3, 15, 1, 28, 1, 1472, 0, '2025-05-29 14:34:44', 0, 0, 0, '2025-05-30 03:59:51', 0, 0, 0, NULL, 0),
(30, 3, 16, 1, 21, 1, 1479, 0, '2025-05-29 14:35:18', 0, 0, 0, '2025-05-30 03:59:51', 0, 0, 0, NULL, 0),
(31, 3, 17, 1, 19, 1, 481, 0, '2025-05-29 14:36:37', 0, 0, 0, '2025-05-30 03:59:51', 0, 0, 0, NULL, 0),
(32, 2, 6, 1, 20, 1, 480, 0, '2025-05-29 15:16:00', 0, 0, 0, '2025-05-30 03:59:51', 0, 0, 0, NULL, 0),
(33, 2, 7, 1, 22, 1, 478, 0, '2025-05-29 17:03:01', 0, 0, 0, '2025-05-30 03:59:51', 0, 0, 0, NULL, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `achievements`
--
ALTER TABLE `achievements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `daily_challenges`
--
ALTER TABLE `daily_challenges`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `date` (`date`),
  ADD KEY `difficulty_id` (`difficulty_id`);

--
-- Indexes for table `daily_challenge_completions`
--
ALTER TABLE `daily_challenge_completions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_challenge` (`user_id`,`challenge_id`),
  ADD KEY `challenge_id` (`challenge_id`);

--
-- Indexes for table `difficulties`
--
ALTER TABLE `difficulties`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `leaderboard`
--
ALTER TABLE `leaderboard`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `level_id` (`level_id`);

--
-- Indexes for table `levels`
--
ALTER TABLE `levels`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_level` (`difficulty_id`,`level_number`);

--
-- Indexes for table `recent_games`
--
ALTER TABLE `recent_games`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `level_id` (`level_id`),
  ADD KEY `difficulty_id` (`difficulty_id`);

--
-- Indexes for table `saved_games`
--
ALTER TABLE `saved_games`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_level` (`user_id`,`level_id`),
  ADD KEY `level_id` (`level_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_achievements`
--
ALTER TABLE `user_achievements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_achievement` (`user_id`,`achievement_id`),
  ADD KEY `achievement_id` (`achievement_id`);

--
-- Indexes for table `user_progress`
--
ALTER TABLE `user_progress`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_level` (`user_id`,`level_id`),
  ADD KEY `level_id` (`level_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `achievements`
--
ALTER TABLE `achievements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `daily_challenges`
--
ALTER TABLE `daily_challenges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `daily_challenge_completions`
--
ALTER TABLE `daily_challenge_completions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `difficulties`
--
ALTER TABLE `difficulties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `leaderboard`
--
ALTER TABLE `leaderboard`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `levels`
--
ALTER TABLE `levels`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `recent_games`
--
ALTER TABLE `recent_games`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `saved_games`
--
ALTER TABLE `saved_games`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user_achievements`
--
ALTER TABLE `user_achievements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_progress`
--
ALTER TABLE `user_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `daily_challenges`
--
ALTER TABLE `daily_challenges`
  ADD CONSTRAINT `daily_challenges_ibfk_1` FOREIGN KEY (`difficulty_id`) REFERENCES `difficulties` (`id`);

--
-- Constraints for table `daily_challenge_completions`
--
ALTER TABLE `daily_challenge_completions`
  ADD CONSTRAINT `daily_challenge_completions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `daily_challenge_completions_ibfk_2` FOREIGN KEY (`challenge_id`) REFERENCES `daily_challenges` (`id`);

--
-- Constraints for table `leaderboard`
--
ALTER TABLE `leaderboard`
  ADD CONSTRAINT `leaderboard_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `leaderboard_ibfk_2` FOREIGN KEY (`level_id`) REFERENCES `levels` (`id`);

--
-- Constraints for table `levels`
--
ALTER TABLE `levels`
  ADD CONSTRAINT `levels_ibfk_1` FOREIGN KEY (`difficulty_id`) REFERENCES `difficulties` (`id`);

--
-- Constraints for table `recent_games`
--
ALTER TABLE `recent_games`
  ADD CONSTRAINT `recent_games_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `recent_games_ibfk_2` FOREIGN KEY (`level_id`) REFERENCES `levels` (`id`),
  ADD CONSTRAINT `recent_games_ibfk_3` FOREIGN KEY (`difficulty_id`) REFERENCES `difficulties` (`id`);

--
-- Constraints for table `saved_games`
--
ALTER TABLE `saved_games`
  ADD CONSTRAINT `saved_games_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `saved_games_ibfk_2` FOREIGN KEY (`level_id`) REFERENCES `levels` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_achievements`
--
ALTER TABLE `user_achievements`
  ADD CONSTRAINT `user_achievements_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `user_achievements_ibfk_2` FOREIGN KEY (`achievement_id`) REFERENCES `achievements` (`id`);

--
-- Constraints for table `user_progress`
--
ALTER TABLE `user_progress`
  ADD CONSTRAINT `user_progress_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `user_progress_ibfk_2` FOREIGN KEY (`level_id`) REFERENCES `levels` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
