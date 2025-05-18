-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 18, 2025 at 03:58 AM
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
-- Database: `aklat_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `myths`
--

CREATE TABLE `myths` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `myths`
--

INSERT INTO `myths` (`id`, `title`, `content`, `image_url`, `created_at`, `user_id`) VALUES
(14, 'The Three Friends: the Monkey, the Dog, and the Carabao', 'Narrated by José M. Hilario, a Tagalog from Batangas, Batangas.\r\n\r\nOnce there lived three friends — a monkey, a dog, and a carabao. They were getting tired of city life, so they decided to go to the country to hunt. They took along with them rice, meat, and some kitchen utensils.\r\n\r\nThe first day the carabao was left at home to cook the food, so that his two companions might have something to eat when they returned from the hunt. After the monkey and the dog had departed, the carabao began to fry the meat. Unfortunately the noise of the frying was heard by the Buñgisñgis in the forest. Seeing this chance to fill his stomach, the Buñgisñgis went up to the carabao and said, “Well, friend, I see that you have prepared food for me.”\r\n\r\nFor an answer, the carabao made a furious attack on him. The Buñgisñgis was angered by the carabao’s lack of hospitality and, seizing him by the horn, threw him knee-deep into the earth. Then the Buñgisñgis ate up all the food and disappeared.\r\n\r\nWhen the monkey and the dog came home, they saw that everything was in disorder and found their friend sunk knee-deep in the ground. The carabao informed them that a big strong man had come and beaten him in a fight. The three then cooked their food. The Buñgisñgis saw them cooking, but he did not dare attack all three of them at once, for in union there is strength.\r\n\r\nThe next day the dog was left behind as cook. As soon as the food was ready, the Buñgisñgis came and spoke to him in the same way he had spoken to the carabao. The dog began to snarl, and the Buñgisñgis, taking offense, threw him down. The dog could not cry to his companions for help, for if he did, the Buñgisñgis would certainly kill him. So he retired to a corner of the room and watched his unwelcome guest eat all of the food. Soon after the Buñgisñgis’s departure, the monkey and the carabao returned. They were angry to learn that the Buñgisñgis had been there again.\r\n\r\nThe next day the monkey was cook, but, before cooking, he made a pitfall in front of the stove. After putting away enough food for his companions and himself, he put the rice on the stove. When the Buñgisñgis came, the monkey said very politely, “Sir, you have come just in time. The food is ready, and I hope you’ll compliment me by accepting it.”\r\n\r\nThe Buñgisñgis gladly accepted the offer and, after sitting down in a chair, began to devour the food. The monkey took hold of a leg of the chair, gave a jerk, and sent his guest tumbling into the pit. He then filled the pit with earth so that the Buñgisñgis was buried with no solemnity.\r\n\r\nWhen the monkey’s companions arrived, they asked about the Buñgisñgis. At first the monkey was not inclined to tell them what had happened, but, on being urged and urged by them, he finally said that the Buñgisñgis was buried “there in front of the stove.”\r\n\r\nHis foolish companions, curious, began to dig up the grave. Unfortunately the Buñgisñgis was still alive. He jumped out, and killed the dog, and lamed the carabao, but the monkey climbed up a tree and so escaped.\r\n\r\nOne day while the monkey was wandering in the forest, he saw a beehive on top of a vine.\r\n\r\n“Now I’ll certainly kill you,” said someone coming towards the monkey.\r\n\r\nTurning around, the monkey saw the Buñgisñgis. “Spare me,” he said, “and I will give up my place to you. The king has appointed me to ring each hour of the day that bell up there,” pointing to the top of the vine.\r\n\r\n“All right! I accept the position,” said the Buñgisñgis.\r\n\r\n“Stay here while I find out what time it is,” said the monkey.\r\n\r\nThe monkey had been gone a long time, and the Buñgisñgis, becoming impatient, pulled the vine. The bees immediately buzzed about him and punished him for his curiosity.\r\n\r\nMaddened with pain, the Buñgisñgis went in search of the monkey and found him playing with a boa-constrictor. “You villain! I’ll not hear any excuses from you. You shall certainly die,” he said.\r\n\r\n“Don’t kill me, and I will give you this belt which the king has given me,” pleaded the monkey.\r\n\r\nNow, the Buñgisñgis was pleased with the beautiful colors of the belt and wanted to possess it, so he said to the monkey, “Put the belt around me, then, and we shall be friends.”\r\n\r\nThe monkey placed the boa-constrictor around the body of the Buñgisñgis. Then he pinched the boa, which soon made an end of his enemy.', NULL, '2025-05-15 01:27:42', 5),
(15, 'Three Brothers of Fortune', 'Narrated by Eugenio Estayo, a Pangasinan, who heard the story from Toribio Serafica, a native of Rosales, Pangasinan.\r\n\r\nIn former times there lived in a certain village a wealthy man who had three sons — Suan, Iloy, and Ambo. As this man was a lover of education, he sent all his boys to another town to school. But these three brothers did not study: they spent their time in idleness and extravagance. When vacation came, they were ashamed to go back to their home town because they did not know anything; so, instead, they wandered from town to town seeking their fortunes.\r\n\r\nIn the course of their travels, they met an old woman broken with age. “Should you like to buy this book, my grandsons?” asked the old woman as she stopped them.\r\n\r\n“What is the virtue of that book, grandmother?” asked Ambo.\r\n\r\n“My grandsons,” replied she, “if you want to restore a dead person to life, just open this book before him, and in an instant he will be revived.” Without questioning her further, Ambo at once bought the book. Then the three continued their journey.\r\n\r\nAgain they met an old woman selling a mat. Now, Iloy was desirous of possessing a charm, so he asked the old woman what virtue the mat had.\r\n\r\n“Why, if you want to travel through the air,” she said, “just step on it, and in an instant you will be where you desire to go.” Iloy did not hesitate but bought the mat at once.\r\n\r\nNow, Suan was the only one who had no charm. They had not gone far, however, before he saw two stones which once in a while would meet and unite to form one round black stone and then separate again. Believing that these stones possessed some magical power, Suan picked them up, for it occurred to him that with them he would be able to unite things of the same or similar kind. This belief of his came true, as we shall see.\r\n\r\nThese three brothers, each possessing a charm, were very happy. They went on their way light-hearted. Not long afterward, they came upon a crowd of persons weeping over the dead body of a beautiful young lady. Ambo told the parents of the young woman that he would restore her to life if they would pay him a reasonable sum of money. As they gladly agreed, Ambo opened his book, and the dead lady was brought back to life. Ambo was paid all the money he asked, but as soon as he had received his reward, Iloy placed his mat on the ground and told his two brothers to hold the young woman and step on the mat. They did so, and in an instant all four were transported to the seashore.\r\n\r\nFrom that place they took ship to another country, but when they were in the middle of the sea, a severe storm came, and their boat was wrecked. All on board would have been drowned had not Suan repaired the broken planks with his two magical stones. When they landed, a quarrel arose among the three brothers as to which one was entitled to the young woman.\r\n\r\nAmbo said, “I am the one who should have her, for it was I who restored her to life.”\r\n\r\n“But if it had not been for me, we should not have the lady with us,” said Iloy.\r\n\r\n“And if it had not been for me,” said Suan, “we should all be dead now, and nobody could have her.”\r\n\r\nAs they could not come to any agreement, they took the question before the king. He decided to divide the young woman into three parts to be distributed among the three brothers. His judgment was carried out. When each had received his share, Iloy and Ambo were discontented because their portions were useless, so they threw them away, but Suan picked up the shares of his two brothers and united them with his own. The young woman was brought to life again and lived happily with Suan. So, after all, Suan was the most fortunate.', NULL, '2025-05-15 01:30:23', 5),
(16, 'The Clever Husband and Wife', 'Narrated by Elisa Cordero, a Tagalog from Pagsanjan, La Laguna. She heard the story from her servant.\r\n\r\nPedro had been living as a servant in a doctor’s house for more than nine years. He wanted very much to have a wife, but he had no business of any kind on which to support one.\r\n\r\nOne day he felt very sad. His look of dejection did not escape the notice of his master, who said, “What is the matter, my boy? Why do you look so sad? Is there anything I can do to comfort you?”\r\n\r\n“Oh, yes!” said Pedro.\r\n\r\n“What do you want me to do?” asked the doctor.\r\n\r\n“Master,” the man replied, “I want a wife, but I have no money to support one.”\r\n\r\n“Oh, don’t worry about money!” replied his master. “Be ready tomorrow, and I will let you marry the woman you love.”\r\n\r\nThe next day the wedding was held. The doctor let the couple live in a cottage not far from his hacienda, and he gave them two hundred pieces of gold. When they received the money, they hardly knew what to do with it as Pedro had never had any business of any sort.\r\n\r\n“What shall we do after we have spent all our money?” asked the wife.\r\n\r\n“Oh, we can ask the doctor for more,” answered Pedro.\r\n\r\nYears passed by, and one day the couple had not even a cent with which to buy food. So Pedro went to the doctor and asked him for some money. The doctor, who had always been kind to them, gave him twenty pieces of gold, but these did not last very long, and it was not many days before the money was all spent. The husband and wife now thought of another way by which they could get money from the doctor.\r\n\r\nEarly one day Pedro went to the doctor’s house weeping. He said that his wife had died and that he had nothing with which to pay for her burial. He had rubbed onion-juice on his eyes, so that he looked as if he were really crying.\r\n\r\nWhen the doctor heard Pedro’s story, he pitied the man and said to him, “What was the matter with your wife? How long was she sick?”\r\n\r\n“For two days,” answered Pedro.\r\n\r\n“Two days!” exclaimed the doctor. “Why did you not call me, then? We should have been able to save her. Well, take this money and see that she gets a decent burial.”\r\n\r\nPedro returned home in good spirits. He found his wife Marta waiting for him at the door, and they were happy once more, but in a month the money was all used up, and they were on the point of starving again.\r\n\r\nNow, the doctor had a married sister whom Pedro and his wife had worked for off and on after their marriage. Pedro told his wife to go to the doctor’s sister and tell her that he was dead and that she had no money to pay for the burial. Marta set out, as she was told, and when she arrived at the sister’s house, the woman said to her, “Marta, why are you crying?”\r\n\r\n“My husband is dead, and I have no money to pay for his burial,” said Marta, weeping.\r\n\r\n“You have served us well, so take this money and see that masses are said for your husband’s soul,” said the kind-hearted mistress.\r\n\r\nThat evening the doctor visited his sister to see her son who was sick. The sister told him that Marta’s husband had died.\r\n\r\n“No,” answered the doctor, “it was Marta who died.” They argued and argued, but could not agree, so they finally decided to send one of the doctor’s servants to see which one was dead.\r\n\r\nWhen Pedro saw the servant coming, he told his wife to lie flat and stiff in the bed as if she were dead, and when the servant entered, Pedro showed him his dead wife.\r\n\r\nThe servant returned and told the doctor and his sister that it was Marta who was dead, but the sister would not believe him, for she said that perhaps he was joking.\r\n\r\nSo they sent another servant. This time Marta made Pedro lie down stiff and flat in the bed, and when the servant entered the house, he saw the man lying as if dead. So he hurried back and told the doctor and his sister what he had seen.\r\n\r\nNow neither knew what to believe. The next morning, therefore, the doctor and his sister together visited the cottage of Pedro. They found the couple both lying as if dead. After examining them, however, the doctor realized that they were merely feigning death.\r\n\r\nHe was so pleased by the joke and so glad to find his old servants alive that he took them home with him and made them stay at his house.', NULL, '2025-05-15 01:32:42', 5),
(17, 'Why the Ocean is Salty', 'Narrated by José M. Paredes of Bangued, Ilocos Sur. He heard the story from a farmer.\r\n\r\nA few years after the creation of the world there lived a tall giant by the name of Ang-ngalo, the only son of the god of building. Ang-ngalo was a wanderer, and a lover of work. He lived in the mountains, where he dug many caves. These caves he protected from the continual anger of Angin, the goddess of the wind, by precipices and sturdy trees.\r\n\r\nOne bright morning while Ang-ngalo was climbing to his loftiest cave, he spied across the ocean — the ocean at the time was pure, its water being the accumulated tears of disappointed goddesses — a beautiful maid. She beckoned to him and waved her black handkerchief, so Ang-ngalo waded across to her through the water. The deep caverns in the ocean are his footprints.\r\n\r\nThis beautiful maid was Sipgnet, the goddess of the dark. She said to Ang-ngalo, “I am tired of my dark palace in heaven. You are a great builder. What I want you to do for me is to erect a great mansion on this spot. This mansion must be built of bricks as white as snow.”\r\n\r\nAng-ngalo could not find any bricks as white as snow; the only white thing there was then was salt. So he went for help to Asin, the ruler of the kingdom of Salt. Asin gave him pure bricks of salt, as white as snow.\r\n\r\nThen Ang-ngalo built hundreds of bamboo bridges across the ocean. Millions of men were employed day and night transporting the white bricks from one side of the ocean to the other.\r\n\r\nAt last the patience of Ocean came to an end: she could not bear to have her deep and quiet slumber disturbed. One day, while the men were busy carrying the salt bricks across the bridges, she sent forth big waves and destroyed them. The brick-carriers and their burden were buried in her deep bosom. In time the salt dissolved, and today the ocean is salty.', NULL, '2025-05-15 01:42:03', 5),
(18, 'The Story of our Fingers', 'Narrated by Leopoldo Uichanco, a Tagalog from Calamba, La Laguna.\r\n\r\n“Why,” said Antonio to his grandfather one day, “does our thumb stand separate from the other fingers?”\r\n\r\n“That is only so in our days,” replied old Julian. “In the days of long ago the fingers of our ancestors stood together in the same position. One day one of these fingers, the one we call the little finger, became very hungry, and he asked the finger next to him to give him some food.\r\n\r\n“ ‘O brother!’ said the Ring-Finger in reply, ‘I am hungry also, but where shall we get food?’\r\n\r\n“ ‘Heaven is merciful,’ put in the Middle-Finger, trying to comfort his two brothers; ‘Heaven will give us some.’\r\n\r\n“ ‘But, Brother Middle-Finger,’ protested the Forefinger, ‘what if Heaven gives us no food?’\r\n\r\n“ ‘Well, then,’ interposed the Thumb, ‘let us steal!’\r\n\r\n“ ‘Steal!’ echoed the Forefinger, not at all pleased by the advice that had just been given. ‘Mr. Thumb knows better than to do that, I hope!’\r\n\r\n“ ‘That is bad policy, Mr. Thumb,’ concluded the other three unanimously. ‘Your idea is against morality, against God, against yourself, against everybody. Our conscience will not permit us to steal.’\r\n\r\n“ ‘Oh, no, no!’ returned Thumb angrily, ‘you are greatly mistaken, my friends! Haven’t you sense enough even to know how foolish you are to oppose my plan? Do you call my scheme bad policy — to save your lives and mine?’\r\n\r\n“ ‘Ay, if that be your plan,’ said the other four fingers, ‘you can go your own way. As for us, we would rather starve and die than steal.’ Then the four virtuous brothers drove Thumb in shame out of their community and would have nothing more to do with him.\r\n\r\n“So that is why,” concluded old Julian, “we see our thumbs separated from the other four fingers. He was a thiefm and the other four, who were honest, did not care to live with him. And it is because Little-Finger did not have enough to eat, that we see him lean and weak these days.”', NULL, '2025-05-15 01:44:27', 5),
(19, 'The Wicked Woman’s Reward', 'Narrated by Gregorio Frondoso, a Bicol from Camarines. The story was told by a father to one of his sons.\r\n\r\nOnce there lived a certain king. He had concubines, five in number. Two of them he loved more than the others for they were to bear him children. He said that the one who should give birth to a male baby he would marry.\r\n\r\nSoon one of them bore a child, but it was a girl, and shortly afterward the other bore a handsome boy. The one which had given birth to the baby girl was restless: she wished that she might have the boy. In order to satisfy her wish, she thought of an ingenious plan whereby she might get possession of the boy.\r\n\r\nOne midnight, when all were sound asleep, she killed her own baby and secretly buried it. Then she quietly crept to her rival’s bed and stole her boy, putting in his place a newborn cat. Early in the morning the king went to the room of his concubine who had borne the boy, and was surprised to find a cat by her side instead of a human child. He was so enraged that he immediately ordered her to be drowned in the river. His order was at once executed.\r\n\r\nThen he went into the room of the wicked woman. The moment he saw the boy baby, he was filled with great joy, and he smothered the child with kisses. As he had promised, he married the woman. After the marriage the king sent away all his other concubines, and he harbored a deep love for his deceitful wife.\r\n\r\nSoon afterwards there was a great confusion throughout the kingdom. Everybody wondered why it was that the river smelled so fragrant, and the people were very anxious to find out the cause of the sweet odor. It was not many days before the townspeople along the river-bank found the corpse of the drowned woman floating in the water, and this was the source of the sweetness that was causing their restlessness. It was full of many different kinds of flowers which had been gathered by the birds. When the people attempted to remove the corpse from the water, the birds pecked them and would not let the body be taken away.\r\n\r\nAt last the news of the miracle was brought to the ears of the king. He himself went to the river to see the wonderful corpse. As soon as he saw the figure of the drowned woman, he was tortured with remorse.\r\n\r\nThen, to his great surprise and fear, the corpse suddenly stood up out of the water and said to him in sorrowful tones, “O king! as you see, my body has been floating on the water. The birds would have buried me, but I wanted you to know that you ordered me to be killed without any investigation of my fault. Your wife stole my boy and, as you saw, she put a cat by my side.”\r\n\r\nThe ghost vanished, and the king saw the body float away again down the river. The king at once ordered the body of his favorite to be taken out of the water and brought to the palace, and he himself was driven back to the town, violent with rage and remorse. There he seized his treacherous wife and hurled her out of the window of the palace, and he even ordered her body to be hanged.\r\n\r\nHaving gotten rid of this evil woman, the king ordered the body of the innocent woman to be buried among the noble dead. The corpse was placed in a magnificent tomb and was borne in a procession with pompous funeral ceremonies. He himself dressed entirely in black as a sign of his genuine grief for her, yet, in spite of his sorrow for his true wife, he took comfort in her son, who grew to be a handsome boy.\r\n\r\nAs time went on, the prince developed into a brave youth who was able to perform the duties of his father the king; so, as his father became old, no longer able to bear the responsibilities of regal power, the prince succeeded to the throne, and ruled the kingdom well. He proved himself to be the son of the good woman by his wise and just rule over his subjects.', NULL, '2025-05-15 01:46:52', 5);

-- --------------------------------------------------------

--
-- Table structure for table `myth_files`
--

CREATE TABLE `myth_files` (
  `file_id` int(11) NOT NULL,
  `myth_id` int(11) NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `stored_filename` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(100) NOT NULL,
  `file_size` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `upload_timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `myth_files`
--

INSERT INTO `myth_files` (`file_id`, `myth_id`, `original_filename`, `stored_filename`, `file_path`, `file_type`, `file_size`, `description`, `upload_timestamp`) VALUES
(9, 14, 'Carabao.jpg', 'mythfile_6825430e747ca0.81656001.jpg', 'aklatanUploads/mythfile_6825430e747ca0.81656001.jpg', '', 110047, 'Narrated by José M. Hilario, a Tagalog from Batangas, Batangas.', '2025-05-15 01:27:42'),
(10, 15, 'Hundred_Island.jpg', 'mythfile_682543af6e7b24.12510401.jpg', 'aklatanUploads/mythfile_682543af6e7b24.12510401.jpg', '', 55582, 'Narrated by Eugenio Estayo, a Pangasinan, who heard the story from Toribio Serafica, a native of Rosales, Pangasinan.', '2025-05-15 01:30:23'),
(11, 16, 'Pagsanjan,LagunaChurchjf4282_21.JPG', 'mythfile_6825443ac79eb0.79066244.jpg', 'aklatanUploads/mythfile_6825443ac79eb0.79066244.jpg', '', 239523, 'Narrated by Elisa Cordero, a Tagalog from Pagsanjan, La Laguna. She heard the story from her servant.', '2025-05-15 01:32:42'),
(12, 17, '1280px-Piles_of_Salt_Salar_de_Uyuni_Bolivia_Luca_Galuzzi_2006_a.jpg', 'mythfile_6825466bd5df91.48333453.jpg', 'aklatanUploads/mythfile_6825466bd5df91.48333453.jpg', '', 297384, 'Narrated by José Laki of Guagua, Pampanga. He got the story from his uncle, who heard it from an old Pampango storyteller.', '2025-05-15 01:42:03'),
(13, 18, 'Screen Shot 2014-06-24 at 1.47.30 AM.png', 'mythfile_682546fb7ba059.93757489.png', 'aklatanUploads/mythfile_682546fb7ba059.93757489.png', '', 264596, 'Narrated by Leopoldo Uichanco, a Tagalog from Calamba, La Laguna.', '2025-05-15 01:44:27'),
(14, 19, 'Mount_Iriga.jpg', 'mythfile_6825478c1a2a74.96212815.jpg', 'aklatanUploads/mythfile_6825478c1a2a74.96212815.jpg', '', 60438, 'Narrated by Gregorio Frondoso, a Bicol from Camarines. The story was told by a father to one of his sons.', '2025-05-15 01:46:52');

-- --------------------------------------------------------

--
-- Table structure for table `pending_myths`
--

CREATE TABLE `pending_myths` (
  `pending_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `tags` varchar(255) DEFAULT NULL,
  `source_info` text DEFAULT NULL,
  `image_suggestion` varchar(255) DEFAULT NULL,
  `submitted_by_user_id` int(11) DEFAULT NULL,
  `submitter_name` varchar(100) DEFAULT NULL,
  `submitter_email` varchar(100) DEFAULT NULL,
  `submission_timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `reviewed_by_admin_id` int(11) DEFAULT NULL,
  `review_timestamp` timestamp NULL DEFAULT NULL,
  `admin_notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pending_myths`
--

INSERT INTO `pending_myths` (`pending_id`, `title`, `content`, `category`, `tags`, `source_info`, `image_suggestion`, `submitted_by_user_id`, `submitter_name`, `submitter_email`, `submission_timestamp`, `status`, `reviewed_by_admin_id`, `review_timestamp`, `admin_notes`) VALUES
(6, 'ang alamat ng bahay', '\"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.\"', 'myth, tikbalang', 'bathala', 'sa bahay', 'test', NULL, 'Bianca Brioners', 'brennierenn1010@gmail.com', '2025-05-03 03:19:35', 'approved', 5, '2025-05-03 04:03:33', NULL),
(7, 'testing myth lang', '\"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.\"', 'myth, tikbalang', 'bathala', 'sa bahay lang po', 'test', NULL, 'Bianca Brioners', 'brennierenn1010@gmail.com', '2025-05-03 04:05:43', 'approved', 5, '2025-05-03 04:06:23', NULL),
(8, 'sdgsdfgsdf', 'sdfgsdfsdfs', 'sdfsdf', 'sdfsdf', 'sa school po', 'ewAN KO', NULL, 'Bianca Brioners', 'brennierenn1010@gmail.com', '2025-05-08 00:16:50', 'rejected', 5, '2025-05-09 04:39:17', NULL),
(9, 'Ang Alamat ng Hotdog', 'Tung Tung Tung Tung  Tung Sahur', NULL, NULL, NULL, NULL, NULL, 'Bianca Brioners', 'brennierenn1010@gmail.com', '2025-05-09 05:07:34', 'approved', 5, '2025-05-09 05:07:46', NULL),
(10, 'erwerwerwer', 'ewdfsfsdfsdfsdfsed', 'myth, tikbalang', 'bathala', 'sa bahay', NULL, NULL, 'Bianca Briones', 'brennierenn1010@gmail.com', '2025-05-09 09:20:16', 'approved', 5, '2025-05-09 09:20:39', NULL),
(11, 'The Three Friends: the Monkey, the Dog, and the Carabao', 'Narrated by José M. Hilario, a Tagalog from Batangas, Batangas.\r\n\r\nOnce there lived three friends — a monkey, a dog, and a carabao. They were getting tired of city life, so they decided to go to the country to hunt. They took along with them rice, meat, and some kitchen utensils.\r\n\r\nThe first day the carabao was left at home to cook the food, so that his two companions might have something to eat when they returned from the hunt. After the monkey and the dog had departed, the carabao began to fry the meat. Unfortunately the noise of the frying was heard by the Buñgisñgis in the forest. Seeing this chance to fill his stomach, the Buñgisñgis went up to the carabao and said, “Well, friend, I see that you have prepared food for me.”\r\n\r\nFor an answer, the carabao made a furious attack on him. The Buñgisñgis was angered by the carabao’s lack of hospitality and, seizing him by the horn, threw him knee-deep into the earth. Then the Buñgisñgis ate up all the food and disappeared.\r\n\r\nWhen the monkey and the dog came home, they saw that everything was in disorder and found their friend sunk knee-deep in the ground. The carabao informed them that a big strong man had come and beaten him in a fight. The three then cooked their food. The Buñgisñgis saw them cooking, but he did not dare attack all three of them at once, for in union there is strength.\r\n\r\nThe next day the dog was left behind as cook. As soon as the food was ready, the Buñgisñgis came and spoke to him in the same way he had spoken to the carabao. The dog began to snarl, and the Buñgisñgis, taking offense, threw him down. The dog could not cry to his companions for help, for if he did, the Buñgisñgis would certainly kill him. So he retired to a corner of the room and watched his unwelcome guest eat all of the food. Soon after the Buñgisñgis’s departure, the monkey and the carabao returned. They were angry to learn that the Buñgisñgis had been there again.\r\n\r\nThe next day the monkey was cook, but, before cooking, he made a pitfall in front of the stove. After putting away enough food for his companions and himself, he put the rice on the stove. When the Buñgisñgis came, the monkey said very politely, “Sir, you have come just in time. The food is ready, and I hope you’ll compliment me by accepting it.”\r\n\r\nThe Buñgisñgis gladly accepted the offer and, after sitting down in a chair, began to devour the food. The monkey took hold of a leg of the chair, gave a jerk, and sent his guest tumbling into the pit. He then filled the pit with earth so that the Buñgisñgis was buried with no solemnity.\r\n\r\nWhen the monkey’s companions arrived, they asked about the Buñgisñgis. At first the monkey was not inclined to tell them what had happened, but, on being urged and urged by them, he finally said that the Buñgisñgis was buried “there in front of the stove.”\r\n\r\nHis foolish companions, curious, began to dig up the grave. Unfortunately the Buñgisñgis was still alive. He jumped out, and killed the dog, and lamed the carabao, but the monkey climbed up a tree and so escaped.\r\n\r\nOne day while the monkey was wandering in the forest, he saw a beehive on top of a vine.\r\n\r\n“Now I’ll certainly kill you,” said someone coming towards the monkey.\r\n\r\nTurning around, the monkey saw the Buñgisñgis. “Spare me,” he said, “and I will give up my place to you. The king has appointed me to ring each hour of the day that bell up there,” pointing to the top of the vine.\r\n\r\n“All right! I accept the position,” said the Buñgisñgis.\r\n\r\n“Stay here while I find out what time it is,” said the monkey.\r\n\r\nThe monkey had been gone a long time, and the Buñgisñgis, becoming impatient, pulled the vine. The bees immediately buzzed about him and punished him for his curiosity.\r\n\r\nMaddened with pain, the Buñgisñgis went in search of the monkey and found him playing with a boa-constrictor. “You villain! I’ll not hear any excuses from you. You shall certainly die,” he said.\r\n\r\n“Don’t kill me, and I will give you this belt which the king has given me,” pleaded the monkey.\r\n\r\nNow, the Buñgisñgis was pleased with the beautiful colors of the belt and wanted to possess it, so he said to the monkey, “Put the belt around me, then, and we shall be friends.”\r\n\r\nThe monkey placed the boa-constrictor around the body of the Buñgisñgis. Then he pinched the boa, which soon made an end of his enemy.', 'myth', 'batangas, myth', 'at a book that i found at the library', 'water buffalo', NULL, 'Bianca Briones', 'brennierenn1010@gmail.com', '2025-05-16 03:49:45', 'pending', NULL, NULL, NULL),
(12, 'Why Mosquitoes Hum and Try to get into the Holes of our Ears', 'Narrated by Fermin Torralba, a Visayan from Tagbilaran, Bohol. He heard the story from an old man of his province.\r\n\r\nA long time ago, when the world was much quieter and younger than it is now, people told and believed many strange stories about wonderful things which none of us have ever seen. In those very early times, in the province of Bohol, there lived a creature called Mangla; he was king of the crabs.\r\n\r\nOne night, as he was very tired and sleepy, Mangla ordered his old sheriff, Cagang, leader of the small land-crabs, to call his followers, Bataktak, before him.\r\n\r\nAlthough the sheriff was old, yet he brought them all in in a very short time. Then Mangla said to the Bataktak, “You must all watch my house while I am sleeping, but do not make any noise that will waken me.”\r\n\r\nThe Bataktak said, “We are always ready to obey you.” So Mangla went to sleep.\r\n\r\nWhile he was snoring, it began to rain so hard that the guards could not help laughing. The king awoke very angry but, as he was still very tired and sleepy, he did not immediately ask the Bataktak why they laughed. He waited till morning came.\r\n\r\nSo, as soon as the sun shone, he called the Bataktak and said to them, “Why did you laugh last night? Did I not tell you not to make any noise?”\r\n\r\nThe Bataktak answered softly, “We could not help laughing, because last night we saw our old friend Hu-man carrying his house on his shoulder.” On account of this reasonable reply, the king pardoned the Bataktak.\r\n\r\nThen he called his sheriff and told him to summon Hu-man. In a short time he came. The king at once said to him, “What did you do last night?”\r\n\r\n“Sir,” replied Hu-man humbly, “I was carrying my house, because Aninipot was bringing fire, and I was afraid that my only dwelling would be burned.” This answer seemed reasonable to the king, so he pardoned Hu-man.\r\n\r\nThen he told his sheriff Cagang to summon Aninipot. When Aninipot appeared, the king, with eyes flashing with anger, said to the culprit, “Why were you carrying fire last night?”\r\n\r\n Aninipot was very much frightened, but he did not lose his wits. In a trembling voice he answered, “Sir, I was carrying fire, because Lamoc was always trying to bite me. To protect myself, I am going to carry fire all the time.” The king thought that Aninipot had a good reason, so he pardoned him also.\r\n\r\nThe king now realized that there was a great deal of trouble brewing in his kingdom of which he would not have been aware if he had not been awakened by the Bataktak. So he sent his sheriff to get Lamoc.\r\n\r\nIn a short time Cagang appeared with Lamoc. But Lamoc, before he left his own house, had told all his companions to follow him, for he expected trouble. Before Lamoc reached the palace, the king was already shouting with rage, so Lamoc approached the king and bit his face.\r\n\r\nThen Mangla cried out, “It is true what I heard from Bataktak, Hu-man, and Aninipot!” The king at once ordered his sheriff to kill Lamoc but, before Cagang could carry out the order, the companions of Lamoc rushed at him. He killed Lamoc, however, and then ran to his home, followed by Lamoc’s friends, who were bent on avenging the murder. As Cagang’s house was very deep under the ground, Lamoc’s friends could not get in, so they remained and hummed around the door.\r\n\r\nEven today we can see that at the doors of the houses of Cagang and his followers there are many friends of Lamoc humming and trying to go inside. It is said that the Lamoc mistake the holes of our ears for the house of Cagang, and that that is the reason mosquitoes hum about our ears now.', 'myth', 'visayas, bohol', 'at a book a read from my childhood', 'crab', NULL, 'Bianca Briones', 'brennierenn1010@gmail.com', '2025-05-16 03:51:50', 'pending', NULL, NULL, NULL),
(13, 'testing for the submission', 'asdasdasdasd', 'asdasd', 'asdasdasd', 'asdasdasd', 'asdasdasdasd', 5, 'Brenn Briones', '0323-3638@lspu.edu.ph', '2025-05-18 01:49:19', 'pending', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `quizzes`
--

CREATE TABLE `quizzes` (
  `quiz_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quizzes`
--

INSERT INTO `quizzes` (`quiz_id`, `title`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(8, 'The wicked woman\'s reward: Quiz', 'This is a short quiz all about the tale \"The wicked woman\'s reward\".', 1, '2025-05-15 01:51:37', '2025-05-15 01:59:40'),
(9, 'Three brothers of fortune: Quiz', 'This is a short quiz all about the tale \"The wicked woman\'s reward\".', 1, '2025-05-15 02:10:16', '2025-05-15 03:44:52');

-- --------------------------------------------------------

--
-- Table structure for table `quiz_answers`
--

CREATE TABLE `quiz_answers` (
  `answer_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `answer_text` varchar(255) NOT NULL,
  `is_correct` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quiz_answers`
--

INSERT INTO `quiz_answers` (`answer_id`, `question_id`, `answer_text`, `is_correct`) VALUES
(33, 9, 'A grand feast in her honor', 0),
(34, 9, 'He would marry her', 1),
(35, 9, 'A separate palace for her and the child', 0),
(36, 9, 'To name the child his sole heir immediately', 0),
(37, 10, 'Her own deceased baby girl', 0),
(38, 10, 'A bundle of rags', 0),
(39, 10, 'A newborn cat', 1),
(40, 10, 'A cursed doll', 0),
(41, 11, 'He ordered her banished from the kingdom.', 0),
(42, 11, 'He ordered her imprisoned in the dungeons.', 0),
(43, 11, 'He ordered her to be drowned in the river.', 1),
(44, 11, 'He ordered an immediate investigation.', 0),
(45, 12, 'The river water turned to blood.', 0),
(46, 12, 'The river smelled incredibly fragrant and sweet.', 1),
(47, 12, 'Fish were seen jumping out onto the banks.', 0),
(48, 12, 'The river suddenly dried up.', 0),
(49, 13, 'The wicked woman confessed during a moment of guilt.', 0),
(50, 13, 'A loyal servant who witnessed the act came forward.', 0),
(51, 13, 'The drowned woman\'s corpse stood up out of the water and spoke to him.', 1),
(52, 13, 'The birds carried a message written on a leaf to the king.', 0),
(53, 14, 'They were ashamed because they hadn\'t studied and knew nothing.', 1),
(54, 14, 'Their father sent them on a quest for magical items.', 0),
(55, 14, 'An old woman in their village told them to seek their fortunes elsewhere.', 0),
(56, 14, 'They were expelled from school for misbehavior.', 0),
(57, 15, 'A mat that could transport people through the air.', 0),
(58, 15, 'Two stones that could unite similar objects.', 0),
(59, 15, 'A cloak of invisibility.', 0),
(60, 15, 'A book that could restore a dead person to life.', 1),
(61, 16, 'He bought them from the same old woman as his brothers.', 0),
(62, 16, 'He found them by the roadside, noticing their unusual behavior.', 1),
(63, 16, 'He won them in a contest of wits.', 0),
(64, 16, 'His father gave them to him before he left home.', 0),
(65, 17, 'A passing merchant ship rescued them.', 0),
(66, 17, 'Iloy used his mat to fly all of them to the nearest shore.', 0),
(67, 17, 'Suan used his two magical stones to repair the broken planks of the boat.', 1),
(68, 17, 'Ambo used his book to calm the storm and repair the boat.', 0),
(69, 18, 'He ordered the young woman to be divided into three parts; Suan then used his stones to reunite the parts and make her whole again.', 1),
(70, 18, 'He decided they should draw lots; Suan won the draw.', 0),
(71, 18, 'He awarded her to Ambo; Suan later convinced Ambo to give her up.', 0),
(72, 18, 'He declared a series of challenges; Suan won the most challenges.', 0);

-- --------------------------------------------------------

--
-- Table structure for table `quiz_attempts`
--

CREATE TABLE `quiz_attempts` (
  `attempt_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `score` int(11) NOT NULL,
  `total_questions` int(11) NOT NULL,
  `attempt_timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quiz_questions`
--

CREATE TABLE `quiz_questions` (
  `question_id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `question_text` text NOT NULL,
  `question_type` varchar(50) DEFAULT 'multiple_choice',
  `order_index` int(11) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quiz_questions`
--

INSERT INTO `quiz_questions` (`question_id`, `quiz_id`, `question_text`, `question_type`, `order_index`, `updated_at`) VALUES
(9, 8, 'What did the king promise the concubine who would bear him a male child?', 'multiple_choice', 0, '2025-05-15 01:55:14'),
(10, 8, 'What did the wicked concubine place beside her rival after stealing the baby boy?', 'multiple_choice', 1, '2025-05-15 01:55:23'),
(11, 8, 'What was the king\'s immediate reaction and order when he found a cat instead of a baby boy with one of his concubines?', 'multiple_choice', 2, '2025-05-15 01:56:44'),
(12, 8, 'What unusual phenomenon occurred in the river that drew the kingdom\'s attention after the innocent woman\'s death?', 'multiple_choice', 3, '2025-05-15 01:57:57'),
(13, 8, 'How did the king finally learn the truth about his innocent concubine and the stolen child?', 'multiple_choice', 4, '2025-05-15 01:59:27'),
(14, 9, 'Why did the three brothers initially leave their hometown after their schooling period?', 'multiple_choice', 0, '2025-05-15 03:35:48'),
(15, 9, 'What magical item did Ambo acquire, and what was its power?', 'multiple_choice', 1, '2025-05-15 03:37:55'),
(16, 9, 'How did Suan obtain his magical stones?', 'multiple_choice', 2, '2025-05-15 03:40:23'),
(17, 9, 'During the sea voyage, how were the brothers and the young lady saved when their boat was wrecked in a storm?', 'multiple_choice', 3, '2025-05-15 03:42:22'),
(18, 9, 'What was the king\'s initial judgment to settle the dispute over the young woman, and how did Suan ultimately win her?', 'multiple_choice', 4, '2025-05-15 03:44:23');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL COMMENT 'Filename of the profile picture stored in assets/images/profiles/',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `first_name`, `last_name`, `profile_picture`, `created_at`) VALUES
(5, 'admin', '0323-3638@lspu.edu.ph', '$2y$10$aJmWgenfAEeG0sfLYoVMQ.qiREk70FQH1huBiTx8dkH5fJid77O0S', 'admin', 'Brenn', 'Briones', 'user_5_68292eba8da30.jpg', '2025-04-25 06:08:27'),
(8, 'Lorence', '0323-3879@lspu.edu.ph', '$2y$10$0ew.pRovLWf5RMDOL6uIAugo5FWXoNaoICzPyjtOTMwRmPpaXs0yG', 'user', 'Lorence', 'Jasareno', NULL, '2025-05-09 03:54:22'),
(11, 'Bianca_Brioners', 'brennierenn1010@gmail.com', '$2y$10$PuZlZotWkIifPpmvFkJxIOnScucl28H95bVvULmFpaLo6uv1JDz0K', 'user', 'Bianca', 'Brioners', 'user_11_68293c4caefee.jpg', '2025-05-18 01:39:43');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `myths`
--
ALTER TABLE `myths`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user_id` (`user_id`);

--
-- Indexes for table `myth_files`
--
ALTER TABLE `myth_files`
  ADD PRIMARY KEY (`file_id`),
  ADD UNIQUE KEY `stored_filename` (`stored_filename`),
  ADD KEY `fk_myth_files_myth` (`myth_id`);

--
-- Indexes for table `pending_myths`
--
ALTER TABLE `pending_myths`
  ADD PRIMARY KEY (`pending_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `submitted_by_user_id` (`submitted_by_user_id`),
  ADD KEY `reviewed_by_admin_id` (`reviewed_by_admin_id`);

--
-- Indexes for table `quizzes`
--
ALTER TABLE `quizzes`
  ADD PRIMARY KEY (`quiz_id`);

--
-- Indexes for table `quiz_answers`
--
ALTER TABLE `quiz_answers`
  ADD PRIMARY KEY (`answer_id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indexes for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD PRIMARY KEY (`attempt_id`),
  ADD KEY `idx_user_quiz` (`user_id`,`quiz_id`),
  ADD KEY `quiz_id` (`quiz_id`);

--
-- Indexes for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  ADD PRIMARY KEY (`question_id`),
  ADD KEY `quiz_id` (`quiz_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `idx_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `myths`
--
ALTER TABLE `myths`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `myth_files`
--
ALTER TABLE `myth_files`
  MODIFY `file_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `pending_myths`
--
ALTER TABLE `pending_myths`
  MODIFY `pending_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `quizzes`
--
ALTER TABLE `quizzes`
  MODIFY `quiz_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `quiz_answers`
--
ALTER TABLE `quiz_answers`
  MODIFY `answer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  MODIFY `attempt_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  MODIFY `question_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `myths`
--
ALTER TABLE `myths`
  ADD CONSTRAINT `fk_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `myth_files`
--
ALTER TABLE `myth_files`
  ADD CONSTRAINT `fk_myth_files_myth` FOREIGN KEY (`myth_id`) REFERENCES `myths` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pending_myths`
--
ALTER TABLE `pending_myths`
  ADD CONSTRAINT `pending_myths_ibfk_1` FOREIGN KEY (`submitted_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `pending_myths_ibfk_2` FOREIGN KEY (`reviewed_by_admin_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `quiz_answers`
--
ALTER TABLE `quiz_answers`
  ADD CONSTRAINT `quiz_answers_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `quiz_questions` (`question_id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD CONSTRAINT `quiz_attempts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_attempts_ibfk_2` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`quiz_id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  ADD CONSTRAINT `quiz_questions_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`quiz_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
