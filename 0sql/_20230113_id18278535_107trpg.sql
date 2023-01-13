-- phpMyAdmin SQL Dump
-- version 4.2.11
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Jan 13, 2023 at 10:28 AM
-- Server version: 5.6.21
-- PHP Version: 5.5.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `id18278535_107trpg`
--

-- --------------------------------------------------------

--
-- Table structure for table `dnd5e_subraces`
--

CREATE TABLE IF NOT EXISTS `dnd5e_subraces` (
  `subrace` varchar(20) NOT NULL,
  `name` varchar(10) NOT NULL,
  `parent_race` varchar(20) NOT NULL,
  `description` text NOT NULL,
  `speed` text,
  `darkvision` text,
  `prof` text,
  `spell` text,
  `ability` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `dnd5e_subraces`
--

INSERT INTO `dnd5e_subraces` (`subrace`, `name`, `parent_race`, `description`, `speed`, `darkvision`, `prof`, `spell`, `ability`) VALUES
('seaelf', '海精靈', 'elf', '在最早的時期，海精靈就深深愛上了海洋那狂野之美。當其他精靈們在一個個國度之間旅行，海精靈則航行於最深的洋流，探索著百千世界的水域。如今，他們大多生活在海岸淺灘或水元素位面中，組成了小型而隱匿的聚落。', '{"swim": "30"}', NULL, '{"weapon": "矛|三叉戟|輕弩|捕網","language": "水族"}', NULL, NULL),
('woodelf', '木精靈', 'elf', '做為一名木精靈，你具有敏銳的感官和直覺，且你迅捷的步伐讓你得以快速而隱密的穿梭在你熟悉的森林中。這個分類包括了灰鷹世界的野精靈(wild elf)（又被稱作革魯哈祈(grugach)）、龍槍世界的卡貢納斯提精靈(Kagonesti)、以及在灰鷹世界和被遺忘國度中被稱作木精靈(wood elf)的種族。在費倫，木精靈（又被稱作野精靈、綠精靈、或森精靈）離群索居，且並不信任非精靈種族。\\n\\r木精靈的肌膚傾向赤銅色調，有時會帶有點蒼綠。他們的頭髮偏向棕色和黑色，但偶爾也會出現金黃色或赤銅色系。他們的眼睛呈現綠色、棕色、或淡褐色。', '{"walk": "35"}', NULL, '{"weapon": "長劍|短劍|短弓|長弓"}', NULL, NULL),
('highelf', '高等精靈', 'elf', '做為一名高等精靈，你具有敏銳的心靈且精通至少一種基礎魔法。在D&D的許多世界中，存在著二種高等精靈。其中一種（包括了灰鷹世界的灰精靈(gray elf)和山谷精靈(valley elf)、龍槍世界的西瓦納斯提精靈(Silvanesti)、和被遺忘國度的日精靈(sun elf)）傲慢而孤僻，深信他們自己遠比非精靈或甚至其他精靈種族來的更加優越。另一種類型（包括灰鷹世界的高等精靈(high elf)、龍槍世界的奎靈納斯提精靈(Qualinesti)、和被遺忘國度的月精靈(moon elf)）則更常見且更為友善，並且經常能在人類和其他種族之中被見到。', NULL, NULL, '{"weapon": "長劍|短劍|短弓|長弓", "language": "any"}', NULL, NULL),
('drowelf', '暗精靈', 'elf', '傳承自一個早期精靈的暗色皮膚亞種，卓爾因為追隨女神羅絲(Lolth)踏上邪惡與腐化之路，而被從地表世界放逐。如今，他們已經在幽暗地域的深處建立起了自己的文明，遵循著羅絲女神的意志。卓爾也被稱作黑暗精靈，他們有著如拋光黑曜石般的黑色肌膚，以及蒼白或淡白色的秀髮。他們通常有著帶淡紫、銀白、粉紅、或藍色色調的淡色瞳孔（以至於常被誤認為白色）。他們的身材比大部分精靈要來得更嬌小而細瘦。', NULL, NULL, '{"weapon": "刺劍|短劍|手弩"}', NULL, NULL),
('hilldwarf', '丘陵矮人', 'dwarf', '作為丘陵矮人，你有敏銳的觸覺、深邃的直覺、和可觀\r\n的適應力。費倫的富強南國的金矮人 gold dwarf 屬於丘\r\n陵矮人，而龍槍世設的克莱恩中被逐離家園奈逹氏族\r\nNeidar 和流離失所的克拉氏族 Klar 亦然。', NULL, NULL, NULL, NULL, NULL),
('mountaindwarf', '高山矮人', 'dwarf', '作為高山矮人，你身強力壯，習慣了崎嶇地形裡的艱難\r\n生活。以矮人而言你應該偏向高大和淡膚色。住在費倫\r\n北地的盾矮人 shield dwarf，以及龍槍中統治矮人的海\r\n勒氏族 Hylar 和貴族階級的逹瓦氏族 Daewar 都是高山\r\n矮人。', NULL, NULL, '{"armor": "輕甲|中甲"}', NULL, '{"str": "+1"}'),
('duergardwarf', '灰矮人', 'dwarf', '灰矮人們認為勤勞的價值高於一切。在他們的文化中顯露除了冷酷的決心或者是憤怒以外的感情是絕不可取的，不過他們看起來會為勞作而感到愉快。他們能像典型矮人那般欣賞規矩的、傳統的、完美的工藝，不過他們只用排除了美學和藝術價值的絕對功利性來衡量自己的商品。\r\n很少有灰矮人會成為冒險者，而他們當中出現在地表世界的更少因為他們是保守而多疑。通常只有被流放者才會願意離開他們自己的地下都市。請與你的地下城主討論你是否能扮演一名灰矮人角色。', NULL, NULL, '', '{"灰矮人魔法":{"spells":{"3": {"變巨/縮小術":{"time":"1/日", "remark":"只能使用變巨的選項。"}},"5": {"隱形術":{"time":"1/日", "remark":""}}},"remark": "","recover": "LR"}}', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `dnd5e_subraces`
--
ALTER TABLE `dnd5e_subraces`
 ADD PRIMARY KEY (`subrace`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
