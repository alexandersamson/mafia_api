<?php
$migrations = [
    1 => "CREATE TABLE `games` (
          `id` int(11) NOT NULL,
          `gid` varchar(64) NOT NULL,
          `name` varchar(32) NOT NULL,
          `status` varchar(16) NOT NULL DEFAULT 'open',
          `count_days` int(11) DEFAULT NULL,
          `host_keeps_time` tinyint(1) DEFAULT 1,
          `next_phase_timestamp` int(12) DEFAULT NULL,
          `paused_time_left` int(11) NOT NULL DEFAULT 0,
          `start_phase_id` int(11) NOT NULL DEFAULT 1,
          `current_phase_id` int(11) DEFAULT 1,
          `show_game_roles` tinyint(1) NOT NULL DEFAULT 0,
          `is_public_listed` tinyint(1) NOT NULL DEFAULT 1,
          `pin_code` varchar(16) DEFAULT '0000',
          `creator_player_id` int(11) NOT NULL,
          `created_on` int(12) NOT NULL DEFAULT 0,
          `deleted` tinyint(1) NOT NULL DEFAULT 0
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
        ALTER TABLE `games`
          ADD PRIMARY KEY (`id`),
          ADD UNIQUE KEY `gid` (`gid`);
        ALTER TABLE `games`
          MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
        INSERT INTO `games` (`id`, `gid`, `name`, `status`, `count_days`, `host_keeps_time`, `next_phase_timestamp`, `paused_time_left`, `start_phase_id`, `current_phase_id`, `show_game_roles`, `is_public_listed`, `pin_code`, `creator_player_id`, `created_on`, `deleted`) VALUES
        (1, '8435e28ed631fb9f6a1e6cc74c1631dd640bbf3e691f67db96aea9cf9a0fa7fa', 'Test_Game', 'open', 0, 0, 0, 0, 1, 1, 0, 1, '0000', 1, '1598836487', 0);
        COMMIT;",

    2 => "CREATE TABLE IF NOT EXISTS `players` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `pid` varchar(64) NOT NULL UNIQUE,
          `name` varchar(64) NOT NULL,
          `discriminator` varchar(32) NOT NULL DEFAULT '#0000',
          `created_on` varchar(32) NOT NULL DEFAULT current_timestamp(),
          `last_seen` varchar(32) NOT NULL DEFAULT current_timestamp(),
          `email` varchar(128),
          `password` varchar(128),
          `games_played` int(11) NOT NULL DEFAULT 0,
          `games_hosted` int(11) NOT NULL DEFAULT 0,
          `blocked` tinyint(1) NOT NULL DEFAULT 0,
          `deleted` tinyint(1) NOT NULL DEFAULT 0,
          `is_superadmin` tinyint(1) NOT NULL DEFAULT 0,
          `is_admin` tinyint(1) NOT NULL DEFAULT 0,
          `is_moderator` tinyint(1) NOT NULL DEFAULT 0,
          `token` varchar(128) NOT NULL COMMENT 'sha3-512',
          `token_expires_on` int(12) NOT NULL
          PRIMARY KEY (id));",

    3 => "CREATE TABLE IF NOT EXISTS `roles` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `rid` varchar(16) NOT NULL UNIQUE,
          `name` varchar(64) NOT NULL,
          `type` varchar(32) NOT NULL,
          `balance_power` int(4) NOT NULL DEFAULT 100, 
          `description` varchar(1024) NOT NULL DEFAULT 'No description',
          `image_url` varchar(256) NOT NULL,
          `faction_id` int(11) NOT NULL,
          `abilities` varchar(256),
          `inventory` varchar(256),
          `deleted` tinyint(1) NOT NULL DEFAULT 0,
          PRIMARY KEY (id));",

    4 => "INSERT INTO `roles` (`id`, `rid`, `name`, `type`, `balance_power`, `description`, `image_url`, `faction_id`, `abilities`, `inventory`, `deleted`) VALUES
        (1, 'host', 'Game Host', 'Host', 0, 'The game host and moderator of the game. Not really a player, but the storyteller.', '', 1, NULL, NULL, 0),
        (2, 'citizen', 'Citizen', 'Innocent', 100, 'Just a generic citizen. Citizens have no special abilities. Their only power is within their vote during the day.', '', 2, 'voteday', NULL, 0),
        (3, 'mafia', 'Mafia Mobster', 'Killer', 250, 'A mafia goon, doing Godfathers\' dirty work. Can collectively kill someone at night in collaboration with other mobsters. When there is a Godfather alive, he will overrule the choice of target', '', 3, 'voteday;mafkill', NULL, 0),
        (4, 'gfather', 'Godfather', 'Deceptive', 350, 'The mafia boss himself. When investigated at night by an investigator, he will appear as \'innocent\' on the report. Also the Godfather can overrule the mobsters\' decisions on who to kill at night.', '', 3, 'voteday;mafkill;appinno', NULL, 0),
        (5, 'igator', 'Investigator', 'Investigative', 200, 'The Investigator can choose someone each night to investigate. The investigator will learn the role type of the targeted person.', '', 2, 'voteday;investigate', NULL, 0),
        (6, 'doctor', 'Medical Doctor', 'Supportive', 150, 'The Medical Doctor can choose to heal someone at night. When the visited person was attacked, they will not die in effect. This ability can be used 2 times during the entire game.', '', 2, 'voteday;docheal', 'medkit;medkit', 0),
        (7, 'skiller', 'Serial Killer', 'Killer', 350, 'The Serial Killer is a third-party murderer. They will act alone and will win when they are the sole survivor in town. Can choose a victim every night. When not going out for a kill in the night, they will instead kill everyone who visits them instead. This will reveal the identity of the Serial Killer though.', '', 4, 'voteday;skill;skillhome', NULL, 0);
        ",

    5 => "CREATE TABLE IF NOT EXISTS `seats` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `sid` varchar(64) NOT NULL,
          `player_id` int(11),
          `game_id` int(11) NOT NULL,
          `role_id` int(11),
          `original_role_id` int(11),
          `last_will` varchar(256),
          `is_alive` tinyint(1) NOT NULL DEFAULT 1, 
          `is_at_home` tinyint(1) NOT NULL DEFAULT 1, 
          `visits_player_id` int(11),
          `knows_own_role` tinyint(1) NOT NULL DEFAULT 0,
          `knows_own_faction` tinyint(1) NOT NULL DEFAULT 0,
          `has_role_exposed` tinyint(1) NOT NULL DEFAULT 0,
          `has_faction_exposed` tinyint(1) NOT NULL DEFAULT 0,
          `has_type_exposed` tinyint(1) NOT NULL DEFAULT 0,
          `has_inventory_exposed` tinyint(1) NOT NULL DEFAULT 0,
          `faction_id` int(11),
          `original_faction_id` int(11),
          `abilities` varchar(256) DEFAULT NULL,
          `inventory` varchar(256) DEFAULT NULL,
          `buffs` varchar(256) DEFAULT NULL,
          `banned` tinyint(1) NOT NULL DEFAULT 0,
          PRIMARY KEY (id));
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
        ALTER TABLE `seats`
          ADD PRIMARY KEY (`id`);
          ADD UNIQUE KEY `sid` (`sid`);
        COMMIT;",

    6 => "CREATE TABLE `abilities` (
          `id` int(11) NOT NULL,
          `aid` varchar(16) NOT NULL,
          `name` varchar(64) NOT NULL,
          `type` varchar(32) NOT NULL,
          `description` varchar(512) DEFAULT NULL,
          `must_be_activated` tinyint(1) NOT NULL DEFAULT 1,
          `can_use_at` varchar(64) NOT NULL DEFAULT 'night',
          `activate_text` varchar(128) NOT NULL,
          `priority` int(11) NOT NULL DEFAULT 1,
          `items_needed` varchar(128) NOT NULL,
          `gives_abilities` varchar(128) DEFAULT NULL,
          `strips_abilities` varchar(128) DEFAULT NULL,
          `works_from_home` tinyint(1) NOT NULL DEFAULT 1,
          `works_from_away` tinyint(1) NOT NULL DEFAULT 1,
          `stays_home` tinyint(1) NOT NULL DEFAULT 1,
          `needs_target` tinyint(1) NOT NULL DEFAULT 1,
          `can_target_self` tinyint(1) NOT NULL DEFAULT 1,
          `can_target_own_faction` tinyint(1) NOT NULL DEFAULT 1,
          `can_target_own_role_type` tinyint(1) NOT NULL DEFAULT 1,
          `can_target_others` tinyint(1) NOT NULL DEFAULT 1,
          `targets_target_at_home` tinyint(1) NOT NULL DEFAULT 1,
          `targets_target_away` tinyint(1) NOT NULL DEFAULT 1,
          `targets_visitors_of_self` tinyint(1) NOT NULL DEFAULT 0,
          `targets_visitors_of_target` tinyint(1) NOT NULL DEFAULT 0,
          `activates_once_per_faction` tinyint(1) NOT NULL DEFAULT 0,
          `once_per_faction_concurrency` varchar(16) NOT NULL DEFAULT 'majority',
          `once_per_faction_final_say_roles` varchar(64) DEFAULT NULL,
          `effect_is_permanent` tinyint(1) NOT NULL DEFAULT 0,
          `announce_use_to_public` tinyint(1) NOT NULL DEFAULT 0,
          `announce_user_to_public` tinyint(1) NOT NULL DEFAULT 0,
          `custom_value` int(11) DEFAULT 0,
          `deleted` tinyint(1) NOT NULL DEFAULT 0
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
        INSERT INTO `abilities` (`id`, `aid`, `name`, `type`, `description`, `must_be_activated`, `can_use_at`, `activate_text`, `priority`, `items_needed`, `gives_abilities`, `strips_abilities`, `works_from_home`, `works_from_away`, `stays_home`, `needs_target`, `can_target_self`, `can_target_own_faction`, `can_target_own_role_type`, `can_target_others`, `targets_target_at_home`, `targets_target_away`, `targets_visitors_of_self`, `targets_visitors_of_target`, `activates_once_per_faction`, `once_per_faction_concurrency`, `once_per_faction_final_say_roles`, `effect_is_permanent`, `announce_use_to_public`, `announce_user_to_public`, `custom_value`, `deleted`) VALUES
        (1, 'docheal', 'Emergency Surgery', 'protect_heal', 'Visit someone to save their life in the night. When the person who you visit was supposedly killed, you will save them.', 1, 'night', '[target] is protected tonight!', 100, 'medkit', NULL, NULL, 1, 1, 0, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0, 'majority', NULL, 0, 0, 0, 0, 0),
        (2, 'mafkill', 'Mafia Hit', 'kill', 'Points to a target to murder tonight. This ability is associative with the mafia. So all maffiosi together will attempt to murder one target. Is overruled by the Godfather.', 1, 'night', 'Voted for the hit on [target]', 500, '', NULL, NULL, 1, 1, 0, 1, 0, 1, 1, 1, 1, 1, 0, 0, 1, 'majority', 'gfather', 0, 0, 0, 0, 0),
        (3, 'voteday', 'Vote for condemnation', 'vote', 'Vote to condemn someone to the guillotine during the day phase.', 1, 'voting', 'Voted for [target]', 1, '', NULL, NULL, 1, 1, 1, 1, 0, 1, 1, 1, 0, 1, 0, 0, 0, 'majority', NULL, 0, 1, 1, 1, 0),
        (4, 'appinno', 'Appear Innocent', 'hide_faction', 'Appear as [faction:town] during investigations by [role:igator]s.', 0, '', '', 1, '', NULL, NULL, 1, 1, 1, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 'majority', NULL, 1, 0, 0, 0, 0),
        (5, 'skillhome', 'Massacre All visitors', 'kill', 'When staying home yourself, you will kill everyone who visits you. This will however reveal this action to everyone.', 0, 'night', '', 1, '', NULL, NULL, 1, 0, 1, 0, 0, 1, 1, 1, 0, 0, 1, 0, 0, 'majority', NULL, 0, 0, 0, 0, 0),
        (6, 'investigate', 'Investigate Someone', 'investigate_faction', 'Investigate someone at night to learn the persons faction.', 1, 'night', 'Starting investigation to [target]', 100, '', NULL, NULL, 1, 1, 0, 1, 0, 1, 1, 1, 1, 1, 0, 0, 0, 'majority', NULL, 0, 0, 0, 0, 0),
        (7, 'skill', 'Knife Kill', 'kill', 'Kill someone at night with a knife.', 1, 'night', 'Going out to kill [target].', 1, '', NULL, NULL, 1, 1, 0, 1, 0, 1, 1, 1, 1, 1, 0, 0, 0, 'majority', NULL, 0, 0, 0, 0, 0);
        ALTER TABLE `abilities`
          ADD PRIMARY KEY (`id`),
          ADD UNIQUE KEY `aid` (`aid`);
        ALTER TABLE `abilities`
          MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
        COMMIT;
       ",

    7 => "CREATE TABLE `factions` (
        `id` int(11) NOT NULL,
        `fid` varchar(16) NOT NULL,
        `name` varchar(32) NOT NULL,
        `description` varchar(256) DEFAULT NULL,
        `color` varchar(16) NOT NULL DEFAULT '#707070',
        `image_url` varchar(128) DEFAULT NULL,
        `win_as_whole_faction` tinyint(1) NOT NULL DEFAULT 1,
        `wins_with_factions` varchar(64) DEFAULT NULL,
        `reveal_roles_to_faction` tinyint(1) NOT NULL DEFAULT 0,
        `has_faction_chat` tinyint(1) NOT NULL DEFAULT 0,
        `list_priority` int(11) NOT NULL DEFAULT 1,
        `power-level` int(11) NOT NULL DEFAULT 0,
        `is_inert` int(1) NOT NULL DEFAULT 0,
        `deleted` tinyint(1) NOT NULL DEFAULT 0
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
        ALTER TABLE `factions`
          ADD PRIMARY KEY (`id`),
          ADD UNIQUE KEY `fid` (`fid`);
          ALTER TABLE `factions`
          MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
          INSERT INTO `factions` (`id`, `fid`, `name`, `description`, `color`, `image_url`, `win_as_whole_faction`, `wins_with_factions`, `reveal_roles_to_faction`, `has_faction_chat`, `list_priority`, `power-level`, `is_inert`, `deleted`) VALUES
        (1, 'host', 'Game Host', 'The game hosts\' faction. Not really sure why it\'s a faction, but hey, at least the have a faction.', '#000066', NULL, 0, NULL, 0, 0, 1, 0, 0, 0);
        (2, 'town', 'Town', 'The innocent people of the town. Or an unorganized and tyrannical mob rule. It really depends.', '#009933', NULL, 1, NULL, 0, 0, 2, 0, 0, 0),
        (3, 'mafia', 'Mafia', 'The crafty mobsters. Seeking for \'democratic\' world domination.', '#cc0000', NULL, 1, NULL, 1, 0, 3, 0, 0, 0),
        (4, 'thirdp', 'Third Party', 'Lonely Loners. Vile Killers. Boring Neutrals. Healing Hermits. All of them are within this faction. Most of them win or lose on their own.', '#666699', NULL, 0, NULL, 0, 0, 4, 0, 0, 0),
        COMMIT;",


     8 => "CREATE TABLE `game_phases` (
        `id` int(11) NOT NULL,
          `gpid` varchar(16) NOT NULL,
          `name` varchar(16) NOT NULL,
          `events` VARCHAR(128) NULL,
          `is_night` TINYINT(1) NOT NULL DEFAULT '0',
          `duration` int(11) NOT NULL DEFAULT 300 COMMENT 'in seconds',
          `next_phase_id` int(11) NOT NULL,
          `description` varchar(256) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
        ALTER TABLE `game_phases`
          ADD PRIMARY KEY (`id`);
        ALTER TABLE `game_phases`
          MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
        INSERT INTO `game_phases` (`id`, `gpid`, `name`, `duration`, `next_phase_id`, `description`) VALUES
        (1, 'day', 'Day', 480, 2, 'Day phase. Talking. Accusing. Lying. Finger pointing. This is the time to do it.'),
        (2, 'vote', 'Vote', 180, 3, 'Voting phase. The town will vote on who to condemn today.'),
        (3, 'sunset', 'Sunset', 60, 4, 'When the sun sets, just before night. Usually someone will be condemned around this hour of the day.'),
        (4, 'night', 'Night', 300, 5, 'The night. Where things happen...'),
        (5, 'sunrise', 'Sunrise', 60, 1, 'When the sun rises. What happened at night will be revealed now.');
        COMMIT;"

];