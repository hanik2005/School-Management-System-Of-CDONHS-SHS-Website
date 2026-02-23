-- ============================================================
-- Migration: Add 'Pending', 'Withdrawn', and 'Withdrawn with Grades' status
--             and 'requested' column to student_subjects table
-- ============================================================
-- Run this SQL on your existing database to apply the changes
-- ============================================================

-- 1. Add the 'requested' column to track student's subject selection
ALTER TABLE `student_subjects` 
ADD COLUMN `requested` tinyint(1) DEFAULT 1 COMMENT '1=student requested, 0=student did not request' 
AFTER `status`;

-- 2. Modify the 'status' enum to include new values and change default to 'Pending'
ALTER TABLE `student_subjects` 
MODIFY COLUMN `status` enum('Enrolled','Dropped','Pending','Withdrawn','Withdrawn with Grades') 
DEFAULT 'Pending';

-- 3. Set all existing records to have requested = 1 (they were all enrolled previously)
UPDATE `student_subjects` 
SET `requested` = 1 
WHERE `requested` IS NULL;

-- ============================================================
-- After running this migration, the workflow will be:
-- 
-- Student Enlistment:
--   - All subjects are saved with status = 'Pending'
--   - requested = 1 for checked subjects (student wants to enroll)
--   - requested = 0 for unchecked subjects (student doesn't want)
--
-- Admin Validation:
--   - If admin selects "Enlisted":
--     - Subjects with requested = 1 → status = 'Enrolled'
--     - Subjects with requested = 0 → status = 'Dropped'
--   - If admin selects "Rejected":
--     - All pending subjects are deleted
--
-- Strand Change:
--   - Old subjects with grades → status = 'Withdrawn with Grades'
--   - Old subjects without grades → status = 'Withdrawn'
-- ============================================================
