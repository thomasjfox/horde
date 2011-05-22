-- $Horde: ulaform/scripts/sql/ulaform.sql,v 1.4 2006-12-13 04:58:18 chuck Exp $

CREATE TABLE ulaform_forms (
    form_id           INT DEFAULT 0 NOT NULL,
    user_uid          VARCHAR(255) DEFAULT '' NOT NULL,
    form_name         VARCHAR(255) DEFAULT '' NOT NULL,
    form_action       VARCHAR(255) DEFAULT '' NOT NULL,
    form_params       TEXT NOT NULL,
    form_onsubmit     TEXT NOT NULL,
--
    PRIMARY KEY       (form_id)
);

CREATE TABLE ulaform_fields (
    field_id          INT DEFAULT 0 NOT NULL,
    form_id           INT DEFAULT 0 NOT NULL,
    field_name        VARCHAR(255) DEFAULT '' NOT NULL,
    field_order       INT DEFAULT 0 NOT NULL,
    field_label       VARCHAR(255) DEFAULT '' NOT NULL,
    field_type        VARCHAR(255) DEFAULT 'text' NOT NULL,
    field_params      TEXT,
    field_required    SMALLINT DEFAULT 0 NOT NULL,
    field_readonly    SMALLINT DEFAULT 0 NOT NULL,
    field_desc        VARCHAR(255) DEFAULT NULL,
--
    PRIMARY KEY       (field_id)
);
