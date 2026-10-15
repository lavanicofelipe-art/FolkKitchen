-- Run this ONLY if you already have an older FolksKitchen database (one
-- created before Username/Birthdate/Gender/City and the Admin/Participant
-- roles existed) and want to upgrade it in place instead of starting over.
-- If you're setting up fresh, skip this file — folkskitchen_schema.sql
-- already includes these columns.

USE folkskitchen;

ALTER TABLE Users ADD COLUMN Username VARCHAR(50) NOT NULL DEFAULT '' AFTER Last_Name;
ALTER TABLE Users ADD COLUMN Birthdate DATE NULL AFTER Password_Hash;
ALTER TABLE Users ADD COLUMN Gender VARCHAR(20) NOT NULL DEFAULT '' AFTER Birthdate;
ALTER TABLE Users ADD COLUMN City VARCHAR(100) NOT NULL DEFAULT '' AFTER Gender;

-- Backfill a unique placeholder username for any existing rows, since the
-- app requires one going forward.
UPDATE Users SET Username = CONCAT('user', User_ID) WHERE Username = '';

-- Rename the old Chef/Relative roles to the new Admin/Participant roles.
UPDATE Users SET Role = 'Admin' WHERE Role = 'Chef';
UPDATE Users SET Role = 'Participant' WHERE Role = 'Relative';

ALTER TABLE Users ADD CONSTRAINT UQ_username UNIQUE (Username);
