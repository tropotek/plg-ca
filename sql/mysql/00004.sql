-- --------------------------------------------
-- @version 3.2.6
--
-- Author: Michael Mifsud <http://www.tropotek.com/>
-- --------------------------------------------



UPDATE `ca_assessment` SET `placement_status` = REPLACE(`placement_status`, 'completed,', '');
UPDATE `ca_assessment` SET `uid` = `id`;



