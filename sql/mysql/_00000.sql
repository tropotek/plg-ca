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
    publish_result DATETIME,                                -- Can the student view their average results for this assessment: Past Date is enabled, Future date would enable it then, NULL dissabled.

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
    course_id INT UNSIGNED NOT NULL DEFAULT 0,
    name VARCHAR(255) NOT NULL DEFAULT '',
    description TEXT,
    label VARCHAR(16) NOT NULL,                         -- abbreviated label
    order_by INT UNSIGNED NOT NULL DEFAULT 0,
    del BOOL NOT NULL DEFAULT 0,
    modified DATETIME NOT NULL,
    created DATETIME NOT NULL,
    KEY (uid),
    KEY (course_id),
    KEY del(del)
) ENGINE = InnoDB;

-- ---------------------------------------
-- Competency question setup tables
-- ---------------------------------------
CREATE TABLE IF NOT EXISTS ca_competency (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uid VARCHAR(128) NOT NULL DEFAULT '',               -- TODO: Use to create unique/updateable data
    course_id INT UNSIGNED NOT NULL DEFAULT 0,
    name VARCHAR(255) NOT NULL DEFAULT '',
    description TEXT,
    del BOOL NOT NULL DEFAULT 0,
    modified DATETIME NOT NULL,
    created DATETIME NOT NULL,
    KEY (uid),
    KEY (course_id),
    KEY (del)
) ENGINE = InnoDB;


-- --------------------------------------------
-- Scale
--  TODO: initially I do not think we will make these fully editable by general staff. Only admins
-- --------------------------------------------
CREATE TABLE IF NOT EXISTS ca_scale (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uid VARCHAR(128) NOT NULL DEFAULT '',
    institution_id INT UNSIGNED NOT NULL DEFAULT 0,
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
    gradeable BOOL NOT NULL DEFAULT 0,          -- When true whatever the calculated score is, will be used in an average for the overall assessment
                                                -- Enable only one `avg` or `percent` item to allow the user to enter a score/grade manually.
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






