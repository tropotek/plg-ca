-- ---------------------------------
-- Author: Michael Mifsud <info@tropotek.com>
-- ---------------------------------
-- Adding an entry reminder system to the plugin

-- This is just an example query in-case we need it.
# SELECT DISTINCT *
# FROM placement a
#          LEFT JOIN ca_entry ce ON (a.id = ce.placement_id AND ce.assessment_id = 1)
# WHERE
# -- All absolute values are to be inserted by the PHP script OBJECT Needed: Assessment, PlacementType, Subject
#     (a.placement_type_id = 8 )
#   AND a.subject_id = 53
#   -- These should come from the assessment placement status
#   AND (a.status = 'assessing' OR a.status = 'completed' OR a.status = 'evaluating' OR a.status = 'failed')
#
#
#   AND ce.id IS NULL
#   AND DATE(a.date_end) > '2020-01-01'     -- This date is an arbitrary start date so older placements are not included
#   AND DATE(DATE_ADD(a.date_end, INTERVAL 7 DAY)) <= DATE(NOW())
#
# ORDER BY a.date_end DESC
# ;

alter table ca_assessment add enable_reminder BOOL default 1 not null after enable_checkbox;
alter table ca_assessment add reminder_initial_days INT default 7 not null after enable_reminder;
alter table ca_assessment add reminder_repeat_days INT default 28 not null after reminder_initial_days;
alter table ca_assessment add reminder_repeat_cycles INT default 4 not null after reminder_repeat_days;


-- --------------------------------------------
-- A log of any reminders sent so we can track
--  the numbers and the dates
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS ca_reminder (
    assessment_id INT UNSIGNED NOT NULL DEFAULT 0,
    placement_id INT UNSIGNED NOT NULL DEFAULT 0,
    date DATETIME NOT NULL,
    PRIMARY KEY (assessment_id, placement_id, date),
    KEY (assessment_id),
    KEY (assessment_id, placement_id)
) ENGINE = InnoDB;




