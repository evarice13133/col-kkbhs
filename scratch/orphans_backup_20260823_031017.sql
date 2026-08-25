-- SAUVEGARDE DES DONNÉES ORPHELINES AVANT NETTOYAGE - 2026-08-23 03:10:17

INSERT INTO `class_installments` (`id`, `class_id`, `installment_number`, `amount`, `created_at`) VALUES (9, 104, 1, 70000, '2026-07-01 17:54:43');
INSERT INTO `class_installments` (`id`, `class_id`, `installment_number`, `amount`, `created_at`) VALUES (10, 104, 2, 30000, '2026-07-01 17:54:43');
INSERT INTO `class_installments` (`id`, `class_id`, `installment_number`, `amount`, `created_at`) VALUES (11, 104, 3, 20000, '2026-07-01 17:54:43');
INSERT INTO `class_installments` (`id`, `class_id`, `installment_number`, `amount`, `created_at`) VALUES (12, 105, 1, 70000, '2026-07-01 18:06:06');
INSERT INTO `class_installments` (`id`, `class_id`, `installment_number`, `amount`, `created_at`) VALUES (13, 105, 2, 30000, '2026-07-01 18:06:06');
INSERT INTO `class_installments` (`id`, `class_id`, `installment_number`, `amount`, `created_at`) VALUES (14, 105, 3, 20000, '2026-07-01 18:06:06');
INSERT INTO `class_installments` (`id`, `class_id`, `installment_number`, `amount`, `created_at`) VALUES (35, 107, 1, 500000, '2026-07-29 04:21:18');
INSERT INTO `class_installments` (`id`, `class_id`, `installment_number`, `amount`, `created_at`) VALUES (36, 107, 2, 200000, '2026-07-29 04:21:18');
INSERT INTO `class_installments` (`id`, `class_id`, `installment_number`, `amount`, `created_at`) VALUES (40, 106, 1, 0, '2026-08-06 03:31:38');
INSERT INTO `class_installments` (`id`, `class_id`, `installment_number`, `amount`, `created_at`) VALUES (41, 106, 2, 0, '2026-08-06 03:31:38');
INSERT INTO `class_installments` (`id`, `class_id`, `installment_number`, `amount`, `created_at`) VALUES (42, 106, 3, 0, '2026-08-06 03:31:38');
-- Orphan in fee_installments.class_id (ref classes.id): {"id":"9","academic_year_id":"3","name":"Tranche 1","installment_order":"1","amount":"70000.00","deadline_date":"2026-07-08","class_id":"104","cycle_id":null,"teaching_type_id":null,"created_at":"2026-07-01 17:54:43"}
-- Orphan in fee_installments.class_id (ref classes.id): {"id":"10","academic_year_id":"3","name":"Tranche 2","installment_order":"2","amount":"30000.00","deadline_date":"2026-08-14","class_id":"104","cycle_id":null,"teaching_type_id":null,"created_at":"2026-07-01 17:54:43"}
-- Orphan in fee_installments.class_id (ref classes.id): {"id":"11","academic_year_id":"3","name":"Tranche 3","installment_order":"3","amount":"20000.00","deadline_date":"2026-09-16","class_id":"104","cycle_id":null,"teaching_type_id":null,"created_at":"2026-07-01 17:54:43"}
-- Orphan in fee_installments.class_id (ref classes.id): {"id":"12","academic_year_id":"3","name":"Tranche 1","installment_order":"1","amount":"70000.00","deadline_date":"2026-07-31","class_id":"105","cycle_id":null,"teaching_type_id":null,"created_at":"2026-07-01 18:06:06"}
-- Orphan in fee_installments.class_id (ref classes.id): {"id":"13","academic_year_id":"3","name":"Tranche 2","installment_order":"2","amount":"30000.00","deadline_date":"2026-09-02","class_id":"105","cycle_id":null,"teaching_type_id":null,"created_at":"2026-07-01 18:06:06"}
-- Orphan in fee_installments.class_id (ref classes.id): {"id":"14","academic_year_id":"3","name":"Tranche 3","installment_order":"3","amount":"20000.00","deadline_date":"2026-12-02","class_id":"105","cycle_id":null,"teaching_type_id":null,"created_at":"2026-07-01 18:06:06"}
-- Orphan in fee_installments.class_id (ref classes.id): {"id":"34","academic_year_id":"3","name":"Tranche 1","installment_order":"1","amount":"500000.00","deadline_date":"2026-12-12","class_id":"107","cycle_id":null,"teaching_type_id":null,"created_at":"2026-07-29 04:21:18"}
-- Orphan in fee_installments.class_id (ref classes.id): {"id":"35","academic_year_id":"3","name":"Tranche 2","installment_order":"2","amount":"200000.00","deadline_date":"2027-10-13","class_id":"107","cycle_id":null,"teaching_type_id":null,"created_at":"2026-07-29 04:21:18"}
-- Orphan in fee_installments.class_id (ref classes.id): {"id":"39","academic_year_id":"3","name":"Tranche 1","installment_order":"1","amount":"0.00","deadline_date":"2026-12-31","class_id":"106","cycle_id":null,"teaching_type_id":null,"created_at":"2026-08-06 03:31:38"}
-- Orphan in fee_installments.class_id (ref classes.id): {"id":"40","academic_year_id":"3","name":"Tranche 2","installment_order":"2","amount":"0.00","deadline_date":"2026-12-31","class_id":"106","cycle_id":null,"teaching_type_id":null,"created_at":"2026-08-06 03:31:38"}
-- Orphan in fee_installments.class_id (ref classes.id): {"id":"41","academic_year_id":"3","name":"Tranche 3","installment_order":"3","amount":"0.00","deadline_date":"2026-12-31","class_id":"106","cycle_id":null,"teaching_type_id":null,"created_at":"2026-08-06 03:31:38"}
-- Orphan in installment_deadlines.class_id (ref classes.id): {"id":"9","academic_year_id":"3","class_id":"104","installment_number":"1","deadline_date":"2026-07-08","created_at":"2026-07-01 17:54:43"}
-- Orphan in installment_deadlines.class_id (ref classes.id): {"id":"10","academic_year_id":"3","class_id":"104","installment_number":"2","deadline_date":"2026-08-14","created_at":"2026-07-01 17:54:43"}
-- Orphan in installment_deadlines.class_id (ref classes.id): {"id":"11","academic_year_id":"3","class_id":"104","installment_number":"3","deadline_date":"2026-09-16","created_at":"2026-07-01 17:54:43"}
-- Orphan in installment_deadlines.class_id (ref classes.id): {"id":"12","academic_year_id":"3","class_id":"105","installment_number":"1","deadline_date":"2026-07-31","created_at":"2026-07-01 18:06:06"}
-- Orphan in installment_deadlines.class_id (ref classes.id): {"id":"13","academic_year_id":"3","class_id":"105","installment_number":"2","deadline_date":"2026-09-02","created_at":"2026-07-01 18:06:06"}
-- Orphan in installment_deadlines.class_id (ref classes.id): {"id":"14","academic_year_id":"3","class_id":"105","installment_number":"3","deadline_date":"2026-12-02","created_at":"2026-07-01 18:06:06"}
-- Orphan in installment_deadlines.class_id (ref classes.id): {"id":"31","academic_year_id":"3","class_id":"107","installment_number":"1","deadline_date":"2026-12-12","created_at":"2026-07-29 04:21:18"}
-- Orphan in installment_deadlines.class_id (ref classes.id): {"id":"32","academic_year_id":"3","class_id":"107","installment_number":"2","deadline_date":"2027-10-13","created_at":"2026-07-29 04:21:18"}
-- Orphan in school_fees.class_id (ref classes.id): {"id":"6","academic_year_id":"3","class_id":"106","cycle_id":null,"teaching_type_id":null,"amount":"0.00","created_at":"2026-07-25 19:02:25"}
-- Orphan in school_fees.class_id (ref classes.id): {"id":"7","academic_year_id":"3","class_id":"107","cycle_id":null,"teaching_type_id":null,"amount":"700000.00","created_at":"2026-07-25 19:02:25"}
-- Orphan in enrollments.class_id (ref classes.id): {"id":"1","student_id":"1","class_id":"106","academic_year_id":"3","student_status":"nouveau","frais_scolarite_brut":"0.00","total_reductions":"0.00","total_bourses":"0.00","total_paye":"0.00","reste_a_payer":"0.00","created_at":"2026-07-31 01:57:48","updated_at":"2026-07-31 01:57:48"}
-- Orphan in enrollments.student_id (ref students.id): {"id":"2","student_id":"2","class_id":"93","academic_year_id":"3","student_status":"nouveau","frais_scolarite_brut":"100000.00","total_reductions":"0.00","total_bourses":"0.00","total_paye":"0.00","reste_a_payer":"100000.00","created_at":"2026-07-31 01:58:39","updated_at":"2026-07-31 01:58:39"}
-- Orphan in timetables.class_id (ref classes.id): {"id":"12","academic_year_id":"3","teaching_type_id":"9","cycle_id":"14","class_id":"116","week_id":"1","titre":"Emploi du Temps - MSI 1 (Semaine du 06\/08\/2026)","statut":"verrouille","is_locked":"1","created_by":"42","created_at":"2026-08-06 04:15:36","updated_at":"2026-08-21 04:40:15"}
-- Orphan in timetables.class_id (ref classes.id): {"id":"13","academic_year_id":"3","teaching_type_id":"9","cycle_id":"14","class_id":"117","week_id":"1","titre":"Emploi du Temps - RS 1 (Semaine du 06\/08\/2026)","statut":"verrouille","is_locked":"1","created_by":"42","created_at":"2026-08-06 04:15:36","updated_at":"2026-08-21 04:40:15"}
-- Orphan in timetables.class_id (ref classes.id): {"id":"16","academic_year_id":"3","teaching_type_id":"9","cycle_id":"14","class_id":"118","week_id":"1","titre":"Emploi du Temps - TLecom 1 (Semaine du 06\/08\/2026)","statut":"verrouille","is_locked":"1","created_by":"42","created_at":"2026-08-06 07:08:55","updated_at":"2026-08-21 04:42:06"}
-- Orphan in timetables.class_id (ref classes.id): {"id":"28","academic_year_id":"3","teaching_type_id":"9","cycle_id":"14","class_id":"115","week_id":"3","titre":"Emploi du Temps - BAT 2 (Semaine du 24\/08\/2026)","statut":"brouillon","is_locked":"0","created_by":"67","created_at":"2026-08-11 06:28:40","updated_at":"2026-08-11 06:28:40"}
-- Orphan in timetables.class_id (ref classes.id): {"id":"29","academic_year_id":"3","teaching_type_id":"9","cycle_id":"14","class_id":"113","week_id":"3","titre":"Emploi du Temps - IGL 2 (Semaine du 24\/08\/2026)","statut":"brouillon","is_locked":"0","created_by":"67","created_at":"2026-08-11 06:28:41","updated_at":"2026-08-11 06:28:41"}
-- Orphan in timetables.created_by (ref users.id): {"id":"21","academic_year_id":"3","teaching_type_id":"3","cycle_id":"2","class_id":"10","week_id":"2","titre":"Emploi du Temps - 2nd STT (Semaine du 17\/08\/2026)","statut":"brouillon","is_locked":"0","created_by":"1","created_at":"2026-08-07 18:01:49","updated_at":"2026-08-07 18:01:49"}
-- Orphan in timetables.created_by (ref users.id): {"id":"22","academic_year_id":"3","teaching_type_id":"3","cycle_id":"2","class_id":"12","week_id":"2","titre":"Emploi du Temps - 2nd C (Semaine du 17\/08\/2026)","statut":"brouillon","is_locked":"0","created_by":"1","created_at":"2026-08-07 18:01:49","updated_at":"2026-08-07 18:01:49"}
-- Orphan in user_teaching_types.user_id (ref users.id): {"user_id":"41","teaching_type_id":"3"}
-- Orphan in user_teaching_types.user_id (ref users.id): {"user_id":"41","teaching_type_id":"9"}
-- Orphan in user_teaching_types.user_id (ref users.id): {"user_id":"44","teaching_type_id":"3"}
-- Orphan in user_teaching_types.user_id (ref users.id): {"user_id":"57","teaching_type_id":"9"}
-- Orphan in user_teaching_types.user_id (ref users.id): {"user_id":"58","teaching_type_id":"9"}
-- Orphan in user_teaching_types.user_id (ref users.id): {"user_id":"59","teaching_type_id":"9"}
-- Orphan in user_teaching_types.user_id (ref users.id): {"user_id":"60","teaching_type_id":"9"}
-- Orphan in user_teaching_types.user_id (ref users.id): {"user_id":"61","teaching_type_id":"9"}
-- Orphan in user_teaching_types.user_id (ref users.id): {"user_id":"62","teaching_type_id":"9"}
-- Orphan in user_teaching_types.user_id (ref users.id): {"user_id":"63","teaching_type_id":"3"}
-- Orphan in user_teaching_types.user_id (ref users.id): {"user_id":"63","teaching_type_id":"9"}
-- Orphan in user_teaching_types.user_id (ref users.id): {"user_id":"65","teaching_type_id":"9"}
