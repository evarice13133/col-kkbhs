# TODO

- [ ] Lire le code autour de la méthode `delete()` pour corriger le champ SQL erroné.
- [ ] Identifier le(s) vrais nom(s) de colonne reliant `academic_year` aux tables `students` et/ou `grades`.
- [ ] Corriger `AcademicYearController::delete()` pour utiliser la bonne colonne (ou implémenter une détection robuste via `SHOW COLUMNS`).
- [ ] Ajouter un garde-fou: ne pas planter si la colonne n’existe pas; fallback sur une autre logique.
- [ ] Tester l’action de suppression d’une année (reproduire le crash puis vérifier que l’erreur PDO a disparu).

