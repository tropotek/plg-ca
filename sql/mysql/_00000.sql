-- ---------------------------------
-- Author: Michael Mifsud <info@tropotek.com>
-- ---------------------------------


-- ----------------------------
-- Asessment
-- ----------------------------
CREATE TABLE IF NOT EXISTS ca_assessment (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uid VARCHAR(128) NOT NULL DEFAULT '',                   -- Reserved to be used via an external API identifier or for historic reporting, this could just = id for now.
    course_id INT UNSIGNED DEFAULT 0 NOT NULL,              -- In the EMS this will be the profile_id

    name VARCHAR(128) DEFAULT '' NOT NULL,
    icon VARCHAR(32) DEFAULT 'fa fa-rebel' NOT NULL,        -- a bootstrap CSS icon for the collection EG: 'fa fa-pen'

    status_available VARCHAR(128) DEFAULT '' NOT NULL,      -- JSON array of status values when an assessment can be edited
    assessor_group VARCHAR(20) DEFAULT 'student' NOT NULL,  -- student = self assessment, Learner = staff student assessment, supervisor/External = company/mentor student assessment
    -- Assessors:
    --   `Student` Only gets one entry for sel-assessment [Single Entry] (Default if no fkey exists, other options should be hidden)
    --   `Learner` Each learner/staff will have an icon to add/edit an assessment entry (coordinators would be able to view/edit those assessments) [Multiple Entry]
    --   `Supervisor/External` Enables a public form that will be sent to the placement supervisor [$fkey->getSupervisor()] (will need an interface for this object too)
    --  NOTE: to support the rotation requirements, a coordinator needs to be able to compile multiple results into one final somehow?
    --      If there are any more behaviours required then they should go here as an assessment can only have one type of behaviour.
    --      Here is a though, based on the fkey we should see what behaviours are available and let the App interface inform us of them.

    multi BOOL NOT NULL DEFAULT 0,                          -- Can have multiple assessments by unique users (ignored for self-assessment)
    include_zero BOOL DEFAULT 0 NOT NULL,                   -- Should zero values be included in overall average calculations (Default: false)
    -- TODO: this cannot be here it belongs to the subject for a cohort of students
    -- publish_result DATETIME,                                -- Can the student view their average results for this assessment: Past Date is enabled, Future date would enable it then, NULL dissabled.

    description TEXT,                                       -- Description will be placed on the top of the assessment submition form. (Put instructions here)
    notes TEXT,
    del BOOL NOT NULL DEFAULT 0,
    modified DATETIME NOT NULL,
    created DATETIME NOT NULL,

    KEY (uid),
    KEY (course_id),
    KEY del(del)
) ENGINE = InnoDB;

-- A place to enable entry views on a per subject level
CREATE TABLE IF NOT EXISTS ca_assessment_subject (
    assessment_id INT UNSIGNED NOT NULL DEFAULT 0,
    subject_id INT UNSIGNED NOT NULL DEFAULT 0,
    publish_result DATETIME,                                -- Can the student view their average results for this assessment: Past Date is enabled, Future date would enable it then, NULL dissabled.
    PRIMARY KEY (assessment_id, subject_id)
) ENGINE=InnoDB;

-- --------------------------------------------
-- Associate assessments with placement types
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS ca_assessment_placement_type (
  assessment_id INT UNSIGNED NOT NULL DEFAULT 0,
  placement_type_id INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (assessment_id, placement_type_id)
) ENGINE=InnoDB;


-- --------------------------------------------
-- Learning domains for the competancies
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS ca_domain (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uid VARCHAR(128) NOT NULL DEFAULT '',
    institution_id INT UNSIGNED NOT NULL DEFAULT 1,
    -- course_id INT UNSIGNED NOT NULL DEFAULT 0,
    name VARCHAR(255) NOT NULL DEFAULT '',
    description TEXT,
    label VARCHAR(16) NOT NULL,                         -- abbreviated label
    order_by INT UNSIGNED NOT NULL DEFAULT 0,
    del BOOL NOT NULL DEFAULT 0,
    modified DATETIME NOT NULL,
    created DATETIME NOT NULL,
    KEY (uid),
    KEY (institution_id),
    KEY del(del)
) ENGINE = InnoDB;

-- ---------------------------------------
-- Competency question setup tables
-- ---------------------------------------
CREATE TABLE IF NOT EXISTS ca_competency (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uid VARCHAR(128) NOT NULL DEFAULT '',               -- TODO: Use to create unique/updateable data
    institution_id INT UNSIGNED NOT NULL DEFAULT 1,
    -- course_id INT UNSIGNED NOT NULL DEFAULT 0,
    name TEXT,
    description TEXT,
    del BOOL NOT NULL DEFAULT 0,
    modified DATETIME NOT NULL,
    created DATETIME NOT NULL,
    KEY (uid),
    KEY (institution_id),
    KEY (del)
) ENGINE = InnoDB;


-- --------------------------------------------
-- Scale
--  TODO: initially I do not think we will make these fully editable by general staff. Only admins
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS ca_scale (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uid VARCHAR(128) NOT NULL DEFAULT '',
    institution_id INT UNSIGNED NOT NULL DEFAULT 1,     -- TODO: I think we will keep this option globalfor now....
    name VARCHAR(255) NOT NULL DEFAULT '',
    description TEXT,
    type VARCHAR(255) NOT NULL DEFAULT 'text',          -- `text`, `points`, `percentage` OR a `choice` using options in the the ca_option table ???
    multiple BOOL NOT NULL DEFAULT 0,                   -- if using a list: when true then use checkboxes when false use radio boxes
                                                        -- NOTE: This also means there will be many values per item when true.
    calc_type VARCHAR(16) NOT NULL DEFAULT 'avg',       -- avg, add .... ?? (this would be the calculation method for one item when using multiple values)
    max_value DECIMAL(6, 2) NOT NULL DEFAULT 0,         -- Used for when entering a grade `points` or `percentage` and when averaging out overall item results
    del BOOL NOT NULL DEFAULT 0,
    modified DATETIME NOT NULL,
    created DATETIME NOT NULL,
    KEY (institution_id),
    KEY del(del)
) ENGINE = InnoDB;

-- --------------------------------------------
--
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS ca_option (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    scale_id INT UNSIGNED NOT NULL DEFAULT 0,
    name VARCHAR(255) NOT NULL DEFAULT '',
    description TEXT,
    value DECIMAL(6, 2) NOT NULL DEFAULT 0,             -- Typical values (0, 1, 2, 3, 4) ???
    del BOOL NOT NULL DEFAULT 0,
    modified DATETIME NOT NULL,
    created DATETIME NOT NULL,
    KEY (scale_id),
    KEY del(del)
) ENGINE = InnoDB;


-- --------------------------------------------
-- Item
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS ca_item (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uid VARCHAR(128) NOT NULL DEFAULT '',
    assessment_id INT UNSIGNED NOT NULL DEFAULT 0,
    scale_id INT UNSIGNED NOT NULL DEFAULT 0,
    domain_id INT UNSIGNED NOT NULL DEFAULT 0,  -- If domain Id is 0, then it is assumed this item is related directly to the assessment and should be rendered at the end of the form.

    name VARCHAR(255) NOT NULL DEFAULT '',
    description TEXT,
    gradable BOOL NOT NULL DEFAULT 0,          -- When true whatever the calculated score is, will be used in an average for the overall assessment
    -- NOTE: Enable only one `avg` or `percent` item to allow the user to enter a score/grade manually.
    order_by INT UNSIGNED NOT NULL DEFAULT 0,
    del BOOL NOT NULL DEFAULT 0,
    modified DATETIME NOT NULL,
    created DATETIME NOT NULL,

    KEY (uid),
    KEY (assessment_id),
    KEY (scale_id),
    KEY del(del)
) ENGINE = InnoDB;

-- --------------------------------------------
--
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS ca_item_competancy (
    item_id INT UNSIGNED NOT NULL DEFAULT 0,
    competancy_id INT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (item_id, competancy_id)
) ENGINE = InnoDB;





-- ------------------------------------------------------------------------------------------------------------------------------------


TRUNCATE ca_assessment;
INSERT INTO ca_assessment (uid, course_id, name, icon, status_available, assessor_group, multi, include_zero, description, modified, created) VALUES
    (1, 2, 'GOALS', 'tk tk-goals', 'assessing,evaluating,completed,failed', 'company', 0, 0, '', NOW(), NOW()),
    (2, 2, 'Self Assessment', 'fa fa-user-circle-o', '', 'student', 0, 0, '', NOW(), NOW()),
    (3, 2, 'Supervisor Feedback', 'fa fa-user-md', 'approved,assessing,evaluating,completed,failed', 'company', 0, 0, '', NOW(), NOW())
;

TRUNCATE ca_scale;
INSERT INTO ca_scale (name, description, type, multiple, calc_type, max_value, modified, created) VALUES
('Text', 'Prompt the user for an answer as a text value', 'text', 0, 'avg', 0, NOW(), NOW()),
('Points', 'Allow the user to enter a value. [0-1000]', 'value', 0, 'avg', 1000, NOW(), NOW()),
('Percent', 'Allow the user to add a percentage value. [0-100]', 'value', 0, 'avg', 100, NOW(), NOW()),
('EMS Assessment', 'The EMS evaluation criteria as of 2020 as radio selections. [0-3]', 'choice', 0, 'avg', 3, NOW(), NOW()),
('Yes/No', 'Prompt the user for a yes or no question. [0-1]', 'choice', 0, 'avg', 0, NOW(), NOW()),
('Rotation Assessment', 'The rotation evaluation criteria as of 2020 as radio selections. [0-4]', 'choice', 0, 'avg', 4, NOW(), NOW())
;

TRUNCATE ca_option;
INSERT INTO ca_option (scale_id, name, description, value, modified, created) VALUES
(4, 'Not Observed', '', 0, NOW(), NOW()),
(4, 'Needs Further Development', '', 1, NOW(), NOW()),
(4, 'Meets Expectations', '', 2, NOW(), NOW()),
(4, 'Exceeds Expectations', '', 3, NOW(), NOW())
;
INSERT INTO ca_option (scale_id, name, description, value, modified, created) VALUES
(5, 'No', '', 0, NOW(), NOW()),
(5, 'Yes', '', 1, NOW(), NOW())
;
INSERT INTO ca_option (scale_id, name, description, value, modified, created) VALUES
(6, 'Unable', '', 0, NOW(), NOW()),
(6, 'Almost There', '', 1, NOW(), NOW()),
(6, 'Competent', '', 2, NOW(), NOW()),
(6, 'Very Good', '', 3, NOW(), NOW()),
(6, 'Excels', '', 4, NOW(), NOW())
;

TRUNCATE ca_competency;
INSERT INTO ca_competency (institution_id, name, description, modified, created) VALUES
    (1, 'Demonstrates honesty, integrity, commitment, compassion, respect, teamwork and inclusivity', '', NOW(), NOW()),
    (1, 'Communicates effectively with clients, students, veterinary colleagues and staff through a range of media', '', NOW(), NOW()),
    (1, 'Actively participates in case management including prioritisation and completion of tasks', '', NOW(), NOW()),
    (1, 'Engages in self-directed learning, demonstrates an ability to reflect upon their performance and invites feedback ', '', NOW(), NOW()),
    (1, 'Demonstrates understanding of legal, ethical and welfare issues encountered in the veterinary profession and animal production systems', '', NOW(), NOW()),
    (1, 'Demonstrates understanding of responsible dispensing of medications that complies with professional, regulatory and legal requirements', '', NOW(), NOW()),
    (1, 'Demonstrates understanding of the need for clinical records to comply with professional, regulatory and legal requirements', '', NOW(), NOW()),
    (1, 'Recognises actual or potential animal pain or distress', '', NOW(), NOW()),
    (1, 'Recognises actual or potential animal pain or distress and discusses relevant methods of management', '', NOW(), NOW()),
    (1, 'Recognises when euthanasia is appropriate and discusses relevant humane techniques for euthanasia', '', NOW(), NOW()),
    (1, 'Practices safe, low stress, animal handling techniques', '', NOW(), NOW()),
    (1, 'Recognises patients requiring emergency care', '', NOW(), NOW()),
    (1, 'Recognises patients requiring emergency care and discusses appropriate management', '', NOW(), NOW()),
    (1, 'Observes basic diagnostic procedures and practical skills', '', NOW(), NOW()),
    (1, 'Observes sedation and general anaesthesia and recovery of a patient ', '', NOW(), NOW()),
    (1, 'Observes common surgical procedures', '', NOW(), NOW()),
    (1, 'Gathers a history, performs a clinical examination and develops a diagnostic plan', '', NOW(), NOW()),
    (1, 'Performs basic diagnostic procedures and practical skills', '', NOW(), NOW()),
    (1, 'Participates in sedation and general anesthesia including recovery of a stable patient, monitoring and supportive treatments', '', NOW(), NOW()),
    (1, 'Performs common surgical procedures on a stable patient', '', NOW(), NOW()),
    (1, 'Develops a pre and post-operative management plan for common surgical procedures', '', NOW(), NOW()),
    (1, 'Discusses a pre and post-operative management plan for common surgical procedures ', '', NOW(), NOW()),
    (1, 'Demonstrates understanding of health, safety and biosecurity in a veterinary setting', '', NOW(), NOW()),
    (1, 'Demonstrates ability to calculate an appropriate drug dose and knowledge of withholding periods where appropriate', '', NOW(), NOW()),
    (1, 'Demonstrates an understanding  of anatomy, physiology and pathophysiology', '', NOW(), NOW()),
    (1, 'Demonstrates ability to find information in the literature', '', NOW(), NOW()),
    (1, 'Applies principles of anatomy, physiology and pathophysiology to the diagnosis, prevention, and treatment of clinical cases', '', NOW(), NOW()),
    (1, 'Demonstrates ability to find information in the literature', '', NOW(), NOW()),
    (1, 'Creates a prioritised differential diagnosis list assimilating information from the history, clinical examination and diagnostic tests', '', NOW(), NOW()),
    (1, 'Develops an adaptive management plan that respects client wishes and finances and available resources (including personal skills and equipment)', '', NOW(), NOW()),
    (1, 'Demonstrates understanding of appropriate preventative health care programs', '', NOW(), NOW()),
    (1, 'Demonstrates understanding of an appropriate response to possible zoonotic, exotic or notifiable diseases', '', NOW(), NOW()),
    (1, 'Demonstrates understanding of appropriate plans to maintain and/or investigate breakdowns in food safety and public health', '', NOW(), NOW())
;

TRUNCATE ca_domain;
INSERT INTO ca_domain (institution_id, name, description, label, modified, created) VALUES
    (1, 'Personal And Professional Development', '', 'PD', NOW(), NOW()),
    (1, 'Clinical And Technical Skills', '', 'CS', NOW(), NOW()),
    (1, 'Communication Skills', '', 'COM', NOW(), NOW()),
    (1, 'Knowledge And Problem Solving', '', 'KPS', NOW(), NOW()),                          -- Not a recognised domain????
    (1, 'Scientific Basis Of Clinical Practice', '', 'SB', NOW(), NOW()),
    (1, 'Safely Approach, Handle And Restrain Animals (Exotic)', '', 'EX', NOW(), NOW()),
    (1, 'Ethics And Animal Welfare', '', 'AW', NOW(), NOW()),
    (1, 'Biosecurity And Population Health', '', 'BIOS', NOW(), NOW())
;






















-- These are the instance tables.
-- TODO: OOHH!! lets create a conversation/discussion per entry instance, then the staff and student can converse on it....
-- TODO:
--
CREATE TABLE IF NOT EXISTS skill_entry (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    collection_id INT UNSIGNED NOT NULL DEFAULT 0,
    subjeca_id INT UNSIGNED NOT NULL DEFAULT 0,
    user_id INT UNSIGNED NOT NULL DEFAULT 0,             -- The student user id the entry belongs to
    placement_id INT UNSIGNED NOT NULL DEFAULT 0,        -- (optional) The placement this entry is linked to if 0 then assume self-assessment
    title VARCHAR(255) NOT NULL DEFAULT '',              -- A title for the assessment instance
    assessor VARCHAR(128) DEFAULT '' NOT NULL,           -- Name of person assessing the student if not supervisor.
    absent INT(4) DEFAULT '0' NOT NULL,                  -- Number of days absent from placement.
    average DECIMAL(6, 2) NOT NULL DEFAULT 0.0,          -- Average calculated from all item values
    weighted_average DECIMAL(6, 2) NOT NULL DEFAULT 0.0, -- Average calculated from all item values with their domain weight, including/not zero values
    confirm BOOL DEFAULT NULL,                           -- The value of the confirmation question true/false/null
    status VARCHAR(64) NOT NULL DEFAULT '',              -- pending, approved, not-approved
    notes TEXT,                                          -- Staff only notes
    del BOOL NOT NULL DEFAULT 0,
    modified DATETIME NOT NULL,
    created DATETIME NOT NULL,
    KEY (collection_id),
    KEY (subjeca_id),
    KEY (user_id),
    KEY (placement_id),
    KEY (del)
) ENGINE = InnoDB;


-- ------------------------------------------------------
-- TODO: Lets change the value table to have no entry for a zero values
-- TODO:  this will save around 1/4 of required disc space .....
-- TODO:
-- ------------------------------------------------------
CREATE TABLE IF NOT EXISTS skill_value (
    entry_id INT UNSIGNED NOT NULL DEFAULT 0,
    item_id INT UNSIGNED NOT NULL DEFAULT 0,
    value TEXT,
    PRIMARY KEY (entry_id, item_id)
) ENGINE = InnoDB;






