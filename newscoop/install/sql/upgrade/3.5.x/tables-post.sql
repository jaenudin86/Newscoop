-- Remove the sync phorum user from sql 
DELETE FROM `liveuser_rights` WHERE `right_define_name` = 'SyncPhorumUsers';

-- Remove the phorum
DROP TABLE IF EXISTS `ArticleComments`;
DROP TABLE IF EXISTS `phorum_banlists`;
DROP TABLE IF EXISTS `phorum_files`;
DROP TABLE IF EXISTS `phorum_forum_group_xref`;
DROP TABLE IF EXISTS `phorum_forums`;
DROP TABLE IF EXISTS `phorum_groups`;
DROP TABLE IF EXISTS `phorum_messages`;
DROP TABLE IF EXISTS `phorum_pm_buddies`;
DROP TABLE IF EXISTS `phorum_pm_folders`;
DROP TABLE IF EXISTS `phorum_pm_messages`;
DROP TABLE IF EXISTS `phorum_pm_xref`;
DROP TABLE IF EXISTS `phorum_search`;
DROP TABLE IF EXISTS `phorum_settings`;
DROP TABLE IF EXISTS `phorum_subscribers`;
DROP TABLE IF EXISTS `phorum_user_custom_fields`;
DROP TABLE IF EXISTS `phorum_user_group_xref`;
DROP TABLE IF EXISTS `phorum_user_newflags`;
DROP TABLE IF EXISTS `phorum_user_permissions`;
DROP TABLE IF EXISTS `phorum_users`;