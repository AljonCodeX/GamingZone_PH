-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 04, 2026 at 12:50 PM
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
-- Database: `crud_sample`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `must_change_password` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `created_at`, `must_change_password`) VALUES
(1, 'admin', '$2y$10$N/jyn94HIrPP5y2sNmx.9Oz75hkZva9yOB.j60d/ZLYosyDGvT.MC', '2026-04-25 13:16:06', 0);

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `category` varchar(100) DEFAULT 'General',
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `sold` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`id`, `name`, `description`, `price`, `quantity`, `category`, `image`, `created_at`, `sold`) VALUES
(1, 'PlayStation 5 Console', 'Sony PS5 Disc Edition with DualSense controller. 825GB SSD, 4K gaming, ray tracing, 3D audio.', 30790.00, 15, 'ps5-console', 'img_69f1961d7c059.png', '2026-04-28 13:18:56', 42),
(2, 'PlayStation 5 Slim', 'PS5 Slim Disc Edition — smaller and lighter design with the same next-gen performance.', 46732.00, 10, 'ps5-console', 'img_69f197028315e.png', '2026-04-28 13:18:56', 28),
(3, 'DualSense Controller — Midnight Black', 'Official PS5 DualSense wireless controller in Midnight Black. Haptic feedback and adaptive triggers.', 3995.00, 30, 'ps5-controllers', 'img_69f1978470d7e.png', '2026-04-28 13:18:56', 87),
(4, 'DualSense Controller — Cosmic Red', 'Official PS5 DualSense in Cosmic Red colorway. Brand new, factory sealed.', 3995.00, 25, 'ps5-controllers', 'img_69f197f29feee.png', '2026-04-28 13:18:56', 64),
(5, 'PlayStation VR2', 'PS VR2 headset with Sense controllers. 4K HDR OLED display, eye tracking, 110-degree FOV.', 24999.00, 8, 'ps5-vr', 'img_69f1982fc30e7.webp', '2026-04-28 13:18:56', 15),
(6, 'God of War: Ragnarok', 'Epic PS5 action-adventure. Kratos and Atreus journey across the Nine Realms to prevent Ragnarok.', 2499.00, 40, 'ps5-action', 'img_69f1b7b2a8372.png', '2026-04-28 13:18:56', 110),
(7, 'Marvel\'s Spider-Man 2', 'Play as both Peter Parker and Miles Morales in this open-world blockbuster sequel.', 2799.00, 35, 'ps5-action', 'img_69f1b8019c9cf.png', '2026-04-28 13:18:56', 95),
(8, 'Gran Turismo 7', 'The ultimate PS5 driving simulator. 450+ cars, Real Driving Simulator physics, GT Cafe mode.', 2299.00, 20, 'ps5-racing', 'img_69f1b840f0dae.png', '2026-04-28 13:18:56', 73),
(9, 'EA Sports FC 25', 'The most realistic football simulation. Updated player squads, new HyperMotion V gameplay.', 2199.00, 50, 'ps5-sports', 'img_69f1b8909f211.png', '2026-04-28 13:18:56', 88),
(10, 'Pulse 3D Wireless Headset — White', 'Official Sony PS5 Pulse 3D wireless headset. Dual noise-cancelling mics, 3D audio, USB-C.', 4499.00, 18, 'ps5-headsets', 'img_69f1b93967122.png', '2026-04-28 13:18:56', 34),
(11, 'PlayStation 4 Slim 1TB', 'PS4 Slim console bundle with 1TB storage and one DualShock 4 controller. Great value.', 12995.00, 12, 'ps4-console', 'img_69f1b9ca4669c.png', '2026-04-28 13:18:56', 56),
(12, 'PlayStation 4 Pro 1TB', 'PS4 Pro with 1TB storage. Enhanced 4K gaming support, HDR, and boost mode for older titles.', 16995.00, 8, 'ps4-pro', 'img_69f1b9d80c786.png', '2026-04-28 13:18:56', 31),
(13, 'DualShock 4 Controller — Jet Black', 'Official PS4 DualShock 4 wireless controller. Touchpad, Share button, built-in speaker.', 2299.00, 45, 'ps4-controllers', 'img_69f1ba2b0ebca.png', '2026-04-28 13:18:56', 143),
(14, 'DualShock 4 Controller — Magma Red', 'Official PS4 DualShock 4 in Magma Red. Compatible with PS4 Slim, Pro, and PC via USB.', 2299.00, 30, 'ps4-controllers', 'img_69f1ba711656e.png', '2026-04-28 13:18:56', 92),
(15, 'The Last of Us Part II', 'Award-winning PS4 survival action. Ellie\'s story continues in a brutal, post-pandemic world.', 1995.00, 25, 'ps4-action', 'img_69f1bab70cc6c.png', '2026-04-28 13:18:56', 67),
(17, 'FIFA 23', 'EA Sports FIFA 23 for PS4. HyperMotion2 Technology, both men\'s and women\'s World Cups included.', 1099.00, 40, 'ps4-sports', 'img_69f1bb9f86faf.png', '2026-04-28 13:18:56', 101),
(18, 'Need for Speed Heat', 'High-octane street racing. Race legally by day, run illegal events by night in Palm City.', 999.00, 15, 'ps4-racing', 'img_69f1bbef76c08.png', '2026-04-28 13:18:56', 47),
(19, 'PS4 Gold Wireless Headset', 'Official Sony PS4 Gold 7.1 virtual surround headset. 3D audio, fold-flat, hidden microphone.', 3499.00, 10, 'ps4-headsets', 'img_69f1bc2f05aac.png', '2026-04-28 13:18:56', 22),
(20, 'DualShock 4 Dual Charging Station', 'Official Sony charging dock for two DualShock 4 controllers simultaneously. Compact design.', 1299.00, 35, 'ps4-charging', 'img_69f1bc706bba0.png', '2026-04-28 13:18:56', 79),
(21, 'Nintendo Switch OLED — White', 'Switch OLED model with 7-inch OLED screen, wide adjustable stand, 64GB storage, enhanced audio.', 19995.00, 14, 'switch-oled', 'img_69f1bd965dffa.png', '2026-04-28 13:18:56', 38),
(22, 'Nintendo Switch Lite — Yellow', 'Compact lightweight Switch Lite in Yellow. Dedicated handheld gaming, all Switch games supported.', 12995.00, 20, 'switch-lite', 'img_69f1bdc6d49ea.png', '2026-04-28 13:18:56', 55),
(23, 'Nintendo Switch — Neon Blue/Red', 'Standard Nintendo Switch with detachable Joy-Con in Neon Blue and Neon Red. TV and portable mode.', 17995.00, 10, 'switch-console', 'img_69f1be9c9ec38.png', '2026-04-28 13:18:56', 29),
(24, 'Zelda: Tears of the Kingdom', 'Link\'s epic open-world adventure. Build machines, explore the sky, and battle evil across Hyrule.', 3299.00, 30, 'switch-adventure', 'img_69f1bf0d30dbe.png', '2026-04-28 13:18:56', 120),
(25, 'Mario Kart 8 Deluxe', 'The definitive Mario Kart experience. 48 courses, 96 racers, Battle mode, local and online play.', 2799.00, 35, 'switch-family', 'img_69f1bf15bed58.png', '2026-04-28 13:18:56', 135),
(26, 'Animal Crossing: New Horizons', 'Create your dream island life. Relax, fish, catch bugs, decorate, and visit friends online.', 2499.00, 25, 'switch-family', 'img_69f1bf8041bf3.png', '2026-04-28 13:18:56', 98),
(27, 'Pokemon Scarlet', 'Explore the open world of Paldea. Catch, battle, and train your Pokemon team your own way.', 2799.00, 28, 'switch-rpg', 'img_69f1bf8a84d0f.png', '2026-04-28 13:18:56', 87),
(28, 'Joy-Con Pair — Neon Pink & Green', 'Official Nintendo replacement Joy-Con pair. Neon Pink (L) and Neon Green (R). Easy snap-on.', 3799.00, 20, 'switch-joycon', 'img_69f1bfcc6431c.png', '2026-04-28 13:18:56', 44),
(29, 'Nintendo Switch Hard Carry Case', 'Official Nintendo hard carry case. Fits Switch console and up to 4 game cards. Zipper closure.', 999.00, 50, 'switch-cases', 'img_69f1c028be4e5.png', '2026-04-28 13:18:56', 162),
(30, 'Nintendo Switch Dock Set', 'Official Switch dock set for TV mode output. Includes HDMI cable and USB-C power adapter.', 4299.00, 15, 'switch-dock', 'img_69f1c07250e1d.png', '2026-04-28 13:18:56', 27),
(31, 'Xbox Series X Console', 'The most powerful Xbox ever. 1TB SSD, 4K 120fps, ray tracing, Quick Resume feature.', 29995.00, 10, 'xbox-series-x', 'img_69f1c0fb96ffb.png', '2026-04-28 13:18:56', 21),
(32, 'Xbox Series S — Carbon Black', 'Xbox Series S in Carbon Black with 512GB SSD. All-digital next-gen gaming, compact design.', 19995.00, 15, 'xbox-series-s', 'img_69f1c104df2f2.png', '2026-04-28 13:18:56', 33),
(33, 'Xbox Wireless Controller — Carbon Black', 'Official Xbox wireless controller with textured grip, Share button, USB-C charging port.', 3299.00, 40, 'xbox-controllers', 'img_69f1c19285d93.png', '2026-04-28 13:18:56', 76),
(34, 'Xbox Wireless Controller — Robot White', 'Official Xbox controller in Robot White. Works with Xbox Series X/S, Xbox One, and PC.', 3299.00, 30, 'xbox-controllers', 'img_69f1c19bb9903.png', '2026-04-28 13:18:56', 58),
(35, 'Halo Infinite', 'Master Chief returns. Open-world campaign plus free-to-play multiplayer on Xbox Series X/S.', 1999.00, 20, 'xbox-action', 'img_69f1c1e171f00.png', '2026-04-28 13:18:56', 45),
(36, 'Forza Horizon 5', 'Best open-world racing game. Explore a stunning Mexico map in over 500 cars. Online and solo.', 2199.00, 22, 'xbox-racing', 'img_69f1c2a8d196b.png', '2026-04-28 13:18:56', 69),
(37, 'EA Sports FC 25 — Xbox', 'EA Sports FC 25 for Xbox Series X/S. The most authentic football simulation available.', 2199.00, 30, 'xbox-sports', 'img_69f1c2b34b07e.png', '2026-04-28 13:18:56', 52),
(38, 'Xbox Wireless Headset', 'Official Microsoft Xbox wireless headset. Spatial sound, auto-mute mic, 15-hour battery.', 4999.00, 12, 'xbox-headsets', 'img_69f1c35a6f4ea.png', '2026-04-28 13:18:56', 18),
(39, 'Xbox Play and Charge Kit', 'Official Xbox rechargeable battery pack with USB-C cable. Up to 30 hours per charge.', 1299.00, 40, 'xbox-charging', 'img_69f1c368331ab.png', '2026-04-28 13:18:56', 95),
(40, 'Xbox Game Pass Ultimate 3 Months', 'Xbox Game Pass Ultimate 3-month digital code. 100+ games, cloud gaming, EA Play included.', 1299.00, 100, 'xbox-series-x', 'img_69f1c391d755b.jpg', '2026-04-28 13:18:56', 211),
(41, 'Logitech G502 X Gaming Mouse', 'High-performance wired gaming mouse. HERO 25K sensor, 25,600 DPI, 11 programmable buttons.', 3499.00, 25, 'pc-mouse', 'img_69f1c3ed03204.png', '2026-04-28 13:18:56', 63),
(42, 'Razer BlackWidow V3 Keyboard', 'Full-size mechanical gaming keyboard. Razer Green switches, Chroma RGB lighting, PBT keycaps.', 5999.00, 18, 'pc-keyboards', 'img_69f1c5b7b638d.webp', '2026-04-28 13:18:56', 41),
(43, 'HyperX Cloud II Gaming Headset', '7.1 virtual surround gaming headset. Memory foam ear cushions, detachable mic, USB audio card.', 3999.00, 20, 'pc-headsets', 'img_69f1c5fa10226.png', '2026-04-28 13:18:56', 57),
(44, 'ASUS TUF Gaming 27-inch Monitor 165Hz', '27-inch FHD IPS gaming monitor. 165Hz refresh rate, 1ms response time, AMD FreeSync Premium.', 14999.00, 8, 'pc-monitors', 'img_69f1c6175f4ea.png', '2026-04-28 13:18:56', 12),
(45, 'Samsung 870 EVO 1TB SSD', 'Samsung 870 EVO 2.5-inch SATA SSD. Up to 560MB/s read speed. Reliable long-term storage.', 3299.00, 30, 'pc-storage', 'img_69f1c6a318d67.png', '2026-04-28 13:18:56', 44),
(46, 'Final Fantasy XVI Clive Figure', 'High-detail 8-inch Clive Rosfield action figure from Final Fantasy XVI. Includes Ifrit flame effect.', 2499.00, 10, 'collectibles-figures', 'img_69f1c6ad974e0.png', '2026-04-28 13:18:56', 19),
(47, 'God of War Kratos Bust Statue', 'Premium 12-inch Kratos bust. Hand-painted resin, collector-grade detail, numbered base.', 4999.00, 5, 'collectibles-figures', 'img_69f1fe5b65354.png', '2026-04-28 13:18:56', 7),
(48, 'PlayStation 30th Anniversary Pin Set', 'Commemorative 5-pin set celebrating 30 years of PlayStation. Limited edition collector item.', 999.00, 20, 'collectibles-merchandise', 'img_69f1fee7bbee6.png', '2026-04-28 13:18:56', 31),
(49, 'Zelda Tears of the Kingdom Art Book', 'Official 256-page hardcover art book. Full-color concept art, developer notes, world maps.', 3299.00, 12, 'collectibles-limited', 'img_69f1ff6085719.webp', '2026-04-28 13:18:56', 14),
(50, 'Pokemon Scarlet & Violet Booster Box', 'Sealed Pokemon TCG Scarlet & Violet booster box. 36 packs with 10 cards each. Factory sealed.', 5999.00, 8, 'collectibles-cards', 'img_69f0b4a3716bb.png', '2026-04-28 13:18:56', 23),
(51, 'Logitech G903 HERO Wireless Mouse', 'Wireless gaming mouse with HERO 25K sensor, LIGHTSPEED wireless, and RGB lighting. Up to 140 hours battery.', 4999.00, 20, 'pc-mouse', 'img_69f200597fbdc.png', '2026-04-29 00:37:08', 55),
(52, 'Logitech G PRO X Keyboard TKL', 'Tenkeyless mechanical gaming keyboard with swappable switches. RGB, compact, tournament-ready.', 6999.00, 15, 'pc-keyboards', 'img_69f2014fa65a8.png', '2026-04-29 00:37:08', 38),
(53, 'Logitech G733 Wireless Headset', 'Lightweight wireless gaming headset. LIGHTSPEED, Blue VO!CE mic, DTS 7.1, pastel colors.', 5499.00, 18, 'pc-headsets', 'img_69f201a03b092.png', '2026-04-29 00:37:08', 41),
(54, 'Razer DeathAdder V3 HyperSpeed', 'Ultra-lightweight wireless gaming mouse. HyperSpeed technology, Focus Pro 30K optical sensor.', 4299.00, 22, 'pc-mouse', 'img_69f201dee73dd.png', '2026-04-29 00:37:08', 67),
(55, 'Razer Huntsman V3 Pro TKL', 'Analog optical esports keyboard. Razer analog optical switches, Per-key RGB, doubleshot PBT keycaps.', 9999.00, 10, 'pc-keyboards', 'img_69f2025c13c35.png', '2026-04-29 00:37:08', 29),
(56, 'Razer Kraken V3 HyperSense', 'USB gaming headset with HyperSense haptic technology. THX Spatial Audio, TriForce Titanium 50mm.', 5999.00, 12, 'pc-headsets', 'img_69f202954bf8e.png', '2026-04-29 00:37:08', 33),
(57, 'SteelSeries Arctis Nova Pro Wireless', 'Multi-system wireless gaming headset. Dual wireless, hot-swappable battery, active noise cancellation.', 14999.00, 8, 'pc-headsets', 'img_69f202deaa779.png', '2026-04-29 00:37:08', 17),
(58, 'SteelSeries Apex Pro TKL Wireless', 'Wireless mechanical gaming keyboard with adjustable actuation. OLED display, magnetic wrist rest.', 12999.00, 6, 'pc-keyboards', 'img_69f2031193012.png', '2026-04-29 00:37:08', 14),
(59, 'SteelSeries Rival 650 Wireless Mouse', 'Wireless gaming mouse with dual sensor system. Quantum wireless, RGB, 24-hour battery life.', 5999.00, 14, 'pc-mouse', 'img_69f2034296181.png', '2026-04-29 00:37:08', 26),
(60, 'Logitech G640 Large Gaming Mouse Pad', 'Large cloth gaming surface optimized for low DPI gaming. 460x400mm, rubber base, machine washable.', 1299.00, 35, 'pc-storage', 'img_69f195c3dd28b.png', '2026-04-29 00:37:08', 89),
(61, 'NYXI Hyperion Pro Joy-Con', 'Wireless Joy-Con replacement with Hall Effect joysticks, RGB lighting, and programmable buttons for Nintendo Switch.', 3499.00, 20, 'switch-joycon', 'img_69f18fddaf878.png', '2026-04-29 00:37:08', 44),
(62, 'NYXI Hyperion 2 RGB Joy-Con', 'Switch Joy-Con with full RGB lighting, Hall Effect sticks, motion control, and turbo function.', 3299.00, 18, 'switch-joycon', 'img_69f18f962cd14.png', '2026-04-29 00:37:08', 38),
(63, 'NYXI Hyperion 3 Joy-Con', 'Latest NYXI Joy-Con with improved ergonomics, clickable sticks, and enhanced wireless range.', 3799.00, 15, 'switch-joycon', 'img_69f18f1f0ce42.png', '2026-04-29 00:37:08', 22),
(64, 'NYXI Warrior Wireless Controller', 'Full-size wireless Switch controller in orange/black. Hall Effect joysticks, 20-hour battery.', 2999.00, 25, 'switch-joycon', 'img_69f18eb7d669f.png', '2026-04-29 00:37:08', 51),
(65, 'NYXI Warrior Lite Controller', 'Compact wireless Switch controller. Hall Effect sticks, turbo, motion support, USB-C charging.', 2499.00, 30, 'switch-joycon', 'img_69f18e623b89b.png', '2026-04-29 00:37:08', 63),
(66, 'NYXI Zeus Joy-Con Charging Stand', 'RGB charging stand for Switch Joy-Con controllers. Holds two pairs simultaneously, LED indicator.', 1999.00, 22, 'switch-dock', 'img_69f18e0cea0a9.png', '2026-04-29 00:37:08', 35),
(67, 'NYXI Wizard GameCube Controller', 'GameCube-style wireless controller for Switch. Hall Effect joysticks, classic layout, USB-C.', 2799.00, 18, 'switch-joycon', 'img_69f18daab74da.webp', '2026-04-29 00:37:08', 29),
(68, 'NYXI Atlas Switch Carry Case', 'Hard shell carry case for Nintendo Switch OLED with storage for 10 game cards and accessories.', 1499.00, 40, 'switch-cases', 'img_69f18d8ebc63e.png', '2026-04-29 00:37:08', 77),
(69, 'NYXI Odyssey Switch Case XL', 'Extra-large protective travel case for Nintendo Switch. Fits console, dock, cables, and controllers.', 1799.00, 25, 'switch-cases', 'img_69f18c524c645.png', '2026-04-29 00:37:08', 48),
(70, 'NYXI Rechargeable Mid Bridge', 'Rechargeable battery bridge for Nintendo Switch Joy-Con. Adds 5000mAh capacity, USB-C pass-through.', 1299.00, 30, 'switch-dock', 'img_69f18bfc36048.png', '2026-04-29 00:37:08', 42),
(71, 'Red Dead Redemption 2 — PS4', 'Rockstar Games open-world western. Arthur Morgan\'s outlaw epic across 1899 frontier America.', 1299.00, 30, 'ps4-adventure', 'img_69f18bbe3cec6.png', '2026-04-29 00:37:08', 95),
(73, 'GTA: The Trilogy Definitive Edition — PS4', 'Remastered GTA III, Vice City, and San Andreas. Updated graphics, controls, and achievements.', 1999.00, 25, 'ps4-action', 'img_69f18b4dd4422.png', '2026-04-29 00:37:08', 72),
(74, 'GTA: The Trilogy Definitive Edition — PS5', 'PS5 version with enhanced 4K visuals. All three classic GTA games remastered in one package.', 2299.00, 20, 'ps5-action', 'img_69f18af248ddd.png', '2026-04-29 00:37:08', 58),
(75, 'GTA: The Trilogy Definitive Edition — Switch', 'Play the classic GTA trilogy anywhere on Nintendo Switch. Optimized for handheld and TV mode.', 1799.00, 18, 'switch-action', 'img_69f1848cb0eb1.png', '2026-04-29 00:37:08', 47),
(76, 'Red Dead Redemption — PS4', 'The original Red Dead Redemption remastered for PS4. John Marston\'s legendary western story.', 1099.00, 22, 'ps4-adventure', 'img_69f1843a728b8.png', '2026-04-29 00:37:08', 63),
(77, 'NBA 2K25 — PS5', 'Take-Two\'s NBA 2K25 for PS5. Most realistic basketball sim with updated rosters and MyCareer.', 1450.00, 34, 'ps5-sports', 'img_69f1814f47249.png', '2026-04-29 00:37:08', 82),
(78, 'NBA 2K25 — PS4', 'NBA 2K25 for PS4. Updated gameplay, new MyCareer story, and improved ProPLAY technology.', 1295.00, 28, 'ps4-sports', 'img_69f180fbf1a26.png', '2026-04-29 00:37:08', 66),
(79, 'Borderlands 3 — PS4', 'Looter-shooter mayhem from 2K Games. Billions of guns, four Vault Hunters, epic co-op action.', 795.00, 20, 'ps4-action', 'img_69f180bbd0d03.png', '2026-04-29 00:37:08', 54),
(80, 'BioShock: The Collection — PS4', 'All three BioShock games remastered. Rapture, Columbia, and all DLC in one complete package.', 1299.00, 14, 'ps4-action', 'img_69f1804c2b052.png', '2026-04-29 00:37:08', 39),
(81, 'OneXPlayer APEX Standard', 'AMD Ryzen AI MAX+ 395, 8-inch 120Hz IPS display, 48GB RAM, 1TB SSD. Windows 11 handheld gaming PC.', 89995.00, 5, 'pc-desktops', 'img_69f1800b0ef36.png', '2026-04-29 00:37:08', 3),
(83, 'ASUS ROG Ally X', 'Handheld gaming PC with AMD Z1 Extreme processor. 7-inch FHD 120Hz display, 80Wh battery.', 59999.00, 8, 'pc-desktops', 'img_69f17f18162a2.png', '2026-04-29 00:37:08', 7),
(84, 'Steam Deck OLED 512GB', 'Valve Steam Deck with OLED display. 7.4-inch HDR screen, longer battery, faster WiFi 6E.', 39999.00, 10, 'pc-desktops', 'img_69f17e91e01ff.png', '2026-04-29 00:37:08', 12),
(85, 'Carrying Case for Handheld PC', 'Universal hard shell carry case compatible with Steam Deck, ROG Ally, and OneXPlayer devices.', 1499.00, 30, 'pc-storage', 'img_69f17e1901974.png', '2026-04-29 00:37:08', 28),
(86, 'Intel Core i9-14900K Processor', 'Intel 14th Gen flagship CPU. 24 cores (8P+16E), up to 6.0GHz boost, LGA1700, unlocked.', 24999.00, 10, 'pc-components', 'img_69f17dc57e5fd.png', '2026-04-29 00:37:08', 8),
(87, 'AMD Ryzen 9 7900X Processor', '12-core AMD Ryzen 9 7900X. 5.6GHz boost, PCIe 5.0, AM5 socket, 170W TDP.', 19999.00, 12, 'pc-components', 'img_69f17d8007f9b.png', '2026-04-29 00:37:08', 11),
(88, 'ASUS ROG Strix B650E-F Motherboard', 'AMD AM5 ATX motherboard. PCIe 5.0, DDR5, WiFi 6E, 4x M.2 slots, USB 3.2 Gen 2x2.', 16999.00, 8, 'pc-components', 'img_69f17d143e497.png', '2026-04-29 00:37:08', 6),
(89, 'TeamGroup DDR5 32GB RAM Kit', 'TeamGroup T-Force Delta RGB DDR5 32GB (2x16GB) kit. 6000MHz, CL38, compatible with Intel and AMD.', 7999.00, 20, 'pc-components', 'img_69f17d0cda791.png', '2026-04-29 00:37:08', 19),
(90, 'Lexar NQ790 2TB NVMe SSD', 'Lexar NQ790 PCIe 4.0 NVMe SSD. Up to 7400MB/s read, 2TB capacity, slim M.2 2280 form factor.', 5999.00, 25, 'pc-storage', 'img_69f17c5b8bfc0.png', '2026-04-29 00:37:08', 31),
(91, 'WD Green 1TB SSD', 'Western Digital Green SATA SSD. Reliable everyday storage, up to 550MB/s read, 2.5-inch form.', 2299.00, 40, 'pc-storage', 'img_69f17c50b3ef1.png', '2026-04-29 00:37:08', 67),
(92, 'Samsung 990 Pro 2TB NVMe SSD', 'Samsung 990 Pro PCIe 4.0 NVMe. 7450MB/s read, optimized for PS5 and PC, heat shield included.', 8999.00, 15, 'pc-storage', 'img_69f17bd650af7.png', '2026-04-29 00:37:08', 22),
(93, 'TUF Gaming GT302 ARGB PC Case', 'Mid-tower ATX case with tempered glass, ARGB fans, mesh front panel, and tool-free installation.', 5999.00, 12, 'pc-components', 'img_69f17b9ad04cf.png', '2026-04-29 00:37:08', 9),
(94, 'Cooler Master Hyper 212 RGB', 'Air CPU cooler with 120mm RGB fan. Compatible with Intel LGA1700 and AMD AM5. Easy install.', 1999.00, 30, 'pc-components', 'img_69f17b60b8ef7.png', '2026-04-29 00:37:08', 44),
(95, 'Corsair RM850x 850W PSU', 'Fully modular 80 Plus Gold power supply. 850W, zero RPM mode, 10-year warranty, ATX 3.0.', 7999.00, 10, 'pc-components', 'img_69f153496e37c.png', '2026-04-29 00:37:08', 13),
(105, 'GTA 5 (PC - Steam Key)', 'Grand Theft Auto V for PC. Includes GTA Online access. Steam digital key, no disc needed.', 749.00, 50, 'switch-game', 'img_69f219eb8a7d8.jpg', '2026-04-29 14:39:48', 0),
(106, 'One Piece Monkey D. Luffy Figurine', 'High-quality PVC figurine of Luffy in Gear 5 form. Stands 20cm tall. Great for collectors.', 1299.00, 30, 'collectibles-figurine', 'img_69f2191d01d36.png', '2026-04-29 14:39:48', 0),
(107, 'Gaming Chair Pro X', 'Ergonomic gaming chair with lumbar support, adjustable armrests, and reclining backrest up to 160 degrees.', 5999.00, 15, 'pc-accesorries', 'img_69f219ccb92c9.png', '2026-04-29 14:39:48', 0),
(108, 'NVIDIA GeForce RTX 4060', 'Mid-range GPU with DLSS 3 and ray tracing support. 8GB GDDR6 VRAM.', 19999.00, 10, 'pc-components', 'img_69f219a59065b.png', '2026-04-29 14:39:48', 0),
(109, 'NVIDIA GeForce RTX 4070 Super', 'High-performance GPU for 1440p and 4K gaming. 12GB GDDR6X VRAM.', 34999.00, 8, 'pc-components', 'img_69f219b5363b5.png', '2026-04-29 14:39:48', 0),
(110, 'Mechanical Gaming Keyboard RGB', 'TKL mechanical keyboard with RGB backlight and anti-ghosting.', 2499.00, 40, 'pc-components', 'img_69f21982ef626.png', '2026-04-29 14:39:48', 0),
(111, 'Gaming Mouse 12000 DPI', 'Wired gaming mouse with 12000 DPI sensor and RGB lighting.', 899.00, 60, 'pc-components', 'img_69f21968ddecd.png', '2026-04-29 14:39:48', 0),
(112, 'One Piece Roronoa Zoro Figurine', 'Zoro in his iconic three-sword stance. 22cm PVC figure.', 1499.00, 20, 'collectibles-figurine', 'img_69f2190dee68c.png', '2026-04-29 14:39:48', 0);

-- --------------------------------------------------------

--
-- Table structure for table `pending_items`
--

CREATE TABLE `pending_items` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `image` varchar(255) DEFAULT '',
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `reject_reason` varchar(255) DEFAULT '',
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'admin', 'admin@cyberzone.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '2026-04-20 07:48:28'),
(4, 'utoytzy', 'utoy@gmail.com', '$2y$10$hsZ7Ocaiu8ZAHlsOkGXOq.fk9h9sMZDmsS.BFyBjKZthzEitEyyk2', 'user', '2026-04-25 08:27:57'),
(5, 'Utoy', 'aljonmanabat@gmail.com', '$2y$10$7/p0Meqgj6DPGFOCtkI4u.wHBHsnInKSe4uL4aIE3pny85WjKaLR2', 'user', '2026-04-25 13:30:55'),
(6, 'Lebron', 'lebronjames@gmail.com', '$2y$10$3thHmj.bge7tVNDJ9YhyH./QWBu5ionQ1XuYGIJZNYjd5xylqMlB6', 'user', '2026-05-04 10:35:06');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pending_items`
--
ALTER TABLE `pending_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=113;

--
-- AUTO_INCREMENT for table `pending_items`
--
ALTER TABLE `pending_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
