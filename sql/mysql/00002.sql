-- ---------------------------------
-- Author: Michael Mifsud <info@tropotek.com>
-- ---------------------------------



alter table ca_assessment
    add enable_checkbox TINYINT(1) default 1 not null comment 'Show Checkbox on Subject Assessment table' after include_zero;

