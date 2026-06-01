# Backup and Rollback Procedures for Multi-Year Academic Management System

## Overview
This document outlines the backup and rollback procedures for the multi-year academic management system to ensure data integrity and provide recovery options in case of issues.

## Automated Backup Procedures

### 1. Database Dumps
The system includes automated database dump functionality in `AcademicYearController::doArchive()`:
- Creates SQL dumps using native PHP mysqldump emulation
- Stores dumps in the root directory with timestamp
- Compresses dumps into ZIP archives
- Supports selective data truncation (grades, students, subjects)

### 2. Archive Creation
When archiving an academic year:
1. Full database dump is created
2. ZIP archive is generated with timestamp
3. Archive is stored in the project root directory
4. Archive filename format: `backup_NotesMaster_d_M_Y_His.zip`

### 3. Backup Service
The `BackupService` class (`src/Services/BackupService.php`) provides:
- Archive listing functionality
- Archive creation and management
- Integration with academic year archiving

## Manual Backup Procedures

### Before Major Operations
Before performing year rollover or student promotion:
```bash
# Create a manual database backup
mysqldump -u root -p notemaster_imt > backup_manual_$(date +%Y%m%d_%H%M%S).sql
```

### Before Database Migrations
Before running migration scripts:
```bash
# Backup the entire database
mysqldump -u root -p notemaster_imt > pre_migration_$(date +%Y%m%d_%H%M%S).sql

# Or use the provided script
php scripts/backup_rollback_procedures.php backup
```

## Rollback Procedures

### 1. Database Restoration
If issues occur after migration or year rollover:
```bash
# Restore from SQL dump
mysql -u root -p notemaster_imt < backup_filename.sql

# Or use the provided script
php scripts/backup_rollback_procedures.php restore backup_filename.sql
```

### 2. Migration Rollback
If migration scripts cause issues:
1. Restore from pre-migration backup
2. Manually revert schema changes if needed:
```sql
-- Remove academic_year_id columns if needed
ALTER TABLE students DROP COLUMN academic_year_id;
ALTER TABLE classes DROP COLUMN academic_year_id;
ALTER TABLE teacher_assignments DROP COLUMN academic_year_id;
ALTER TABLE subject_classes DROP COLUMN academic_year_id;
ALTER TABLE sequences DROP COLUMN academic_year_id;
ALTER TABLE activity_logs DROP COLUMN academic_year_id;
ALTER TABLE system_job_runs DROP COLUMN academic_year_id;

-- Remove start_date and end_date from academic_years
ALTER TABLE academic_years DROP COLUMN start_date;
ALTER TABLE academic_years DROP COLUMN end_date;
```

### 3. Year Rollover Rollback
If year rollover causes issues:
1. Restore from pre-rollover backup
2. Deactivate the new year:
```sql
UPDATE academic_years SET is_active = FALSE WHERE id = [new_year_id];
UPDATE academic_years SET is_active = TRUE WHERE id = [old_year_id];
```

## Archive Management

### Archive Location
Archives are stored in the project root directory:
- Format: `backup_NotesMaster_d_M_Y_His.zip`
- SQL dump format: `dump_d_M_Y_His.sql`

### Archive Cleanup
Regular cleanup of old archives is recommended:
- Keep monthly archives for the current year
- Keep quarterly archives for the previous year
- Keep annual archives for older years

### Archive Verification
Regularly verify archive integrity:
```bash
# Test archive extraction
unzip -t backup_filename.zip

# Verify SQL dump
head -n 50 dump_filename.sql
```

## Data Integrity Checks

### Regular Checks
Run these checks regularly:
1. Verify foreign key constraints
2. Check for orphaned records
3. Validate academic_year_id consistency
4. Ensure no data mixing between years

### Verification Script
Use the provided verification script:
```bash
php scripts/verify_data_integrity.php
```

## Emergency Procedures

### System Failure
1. Restore from most recent backup
2. Verify data integrity
3. Check application logs
4. Test critical functionality

### Data Corruption
1. Identify corrupted data
2. Restore from backup before corruption
3. Re-apply safe transactions
4. Verify all data

### Performance Issues
1. Check database indexes
2. Verify query performance
3. Review archive size
4. Clean up old archives

## Best Practices

1. **Always backup before major operations**
2. **Test rollback procedures regularly**
3. **Keep multiple backup versions**
4. **Document all backup locations**
5. **Monitor storage space for archives**
6. **Verify backup integrity regularly**
7. **Keep offsite backups if possible**
8. **Document rollback procedures for team**

## Contact Information
For issues with backup/rollback procedures:
- Check system logs in `src/Services/ActivityTracker.php`
- Review academic year management in `src/Controllers/AcademicYearController.php`
- Consult backup service in `src/Services/BackupService.php`
