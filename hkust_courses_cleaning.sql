DELETE FROM courses;
DELETE FROM labs;
DELETE FROM tutorials;
DELETE FROM lectures;
DELETE FROM labs_time;
DELETE FROM tutorials_time;
DELETE FROM lectures_time;
DELETE FROM departments;
ALTER TABLE departments auto_increment = 1;