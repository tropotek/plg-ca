-- --------------------------------------------
-- @version 3.2.
-- @author: Michael Mifsud <http://www.tropotek.com/>
--
-- --------------------------------------------


alter table ca_assessment add enable_absent_doc BOOL default FALSE not null after enable_checkbox;


